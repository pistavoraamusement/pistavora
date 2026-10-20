<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryRate;
use App\Models\PayrollSetting;
use App\Services\SystemLogger;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendanceController extends Controller
{
    // Minimum minutes between Time In and Time Out. Prevents accidental /
    // test taps (e.g. In 4:52 PM, Out 4:52 PM) from being counted as a
    // full paid day. Change the number to whatever HR prefers.
    private const MIN_MINUTES_BEFORE_TIMEOUT = 30;

    // Overtime is counted in steps of this many minutes, rounded DOWN:
    //   under 30 min -> 0     30 min -> 0.5h     1h20m -> 1h
    //   1h45m -> 1.5h         2h35m -> 2.5h      3h -> 3h
    // So OT is always a whole or half hour (1, 1.5, 2, 2.5, ...).
    private const OT_STEP_MINUTES = 30;

    // Mga area na pwedeng i-assign sa Team Leader at Staff.
    // Ang Manager ay walang area. Ito rin ang pagkakasunod-sunod sa sheet.
    private const AREAS = ['Field of Rides', 'Dino Adventure', 'Roller Skates'];

    // ─── SYSTEM LOG HELPER ─────────────────────────────────
    // A single place that calls SystemLogger::record().
    // - The app uses sessions (not Laravel Auth), so the user_id and email
    //   are passed manually from the session.
    // - $public = true is for endpoints with no login (QR check-in):
    //   no user_id/email is recorded, so the log is never tied to someone
    //   else who happens to be logged in on the same browser. The IP is
    //   still recorded.
    // - When the event ends in "_deleted", checkBulkDelete() is also called.
    // - Wrapped in try/catch: the main action never breaks if logging fails.
    private function audit(
        string $eventType,
        string $description,
        string $severity = 'info',
        array $context = [],
        bool $isAnomaly = false,
        string $module = 'attendance',
        bool $public = false
    ): void {
        try {
            $uid   = null;
            $email = null;

            if (!$public) {
                $sessionId = session('user_id');
                $uid       = is_numeric($sessionId) ? (int) $sessionId : null;
                $email     = session('email');
            }

            SystemLogger::record(
                $eventType,
                $description,
                $severity,
                $context,
                $isAnomaly,
                $uid,
                $email,
                $module
            );

            if (str_ends_with($eventType, '_deleted')) {
                SystemLogger::checkBulkDelete($uid, $module);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SystemLogger failed (Attendance): ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // The sheet always shows the CURRENT half-month (based on today's
        // date), so it is always up to date: Days 1–15 until the 15th,
        // Days 16–end of month after that.
        //
        // ?period=YYYY-MM-DD is still honored (the redirect after an import
        // and the live-update polling use it), but there is no longer a
        // dropdown on the page for picking an older period.
        $requestedPeriod = $request->query('period');
        $range = null;
        if ($requestedPeriod && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedPeriod)) {
            try {
                $range = $this->cutoffRangeForDate(Carbon::parse($requestedPeriod));
            } catch (\Throwable $e) {
                $range = null;
            }
        }
        [$periodStart, $periodEnd, $cutoffType] = $range ?? $this->currentCutoffRange();

        // How many actual days are in THIS period — always 15 for the 1st
        // half, but 13/14/15/16 for the 2nd half depending on the month.
        $periodDays = $periodStart->diffInDays($periodEnd) + 1;

        $attendance = Attendance::whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        // Pivot into day_1..day_N shape, since that's what the view expects
        $pendingNames = [];
        $areaMap = $this->employeeAreaMap();
        $records = $attendance->groupBy('employee_name')->map(function ($recs, $name) use ($periodStart, $periodDays, &$pendingNames, $areaMap) {
            $first = $recs->first();
            $obj = new \stdClass();
            $obj->name = $name;
            // Normalized so "Manager", "MANAGER", "manager" all collapse
            // into one group.
            $obj->category = $first->category ? ucwords(strtolower(trim($first->category))) : 'Staff';
            // Area ay galing sa Employees table. Ang Manager ay walang area.
            $obj->area = $obj->category === 'Manager'
                ? ''
                : ($areaMap[strtolower(trim($name))] ?? '');

            for ($i = 1; $i <= $periodDays; $i++) {
                $obj->{"day_{$i}"} = null;
                $obj->{"source_{$i}"} = null;
                // Actual Time In / Time Out shown in the cell tooltip.
                $obj->{"tin_{$i}"} = null;
                $obj->{"tout_{$i}"} = null;
            }

            foreach ($recs as $rec) {
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                if ($dayNum >= 1 && $dayNum <= $periodDays) {
                    // Prefer the exact value ("+1", "-15", etc.) saved in
                    // `remarks`. Only fall back to a plain P/- when remarks is
                    // empty. QR Time In with no Time Out yet -> shown as "IN"
                    // (not counted as a worked day until they Time Out).
                    $isPending = ($rec->source ?? null) === 'qr' && empty($rec->time_out);
                    if ($isPending) {
                        $pendingNames[] = $name;
                    }

                    $obj->{"day_{$dayNum}"} = $isPending
                        ? 'IN'
                        : ($rec->remarks
                            ?: (strtolower($rec->status) === 'present' ? 'P' : '-'));
                    // Source: bio scanner / QR self check-in (old rows may be 'manual').
                    $obj->{"source_{$dayNum}"} = $rec->source ?? 'bio';

                    // Actual recorded times, so HR can see exactly when the
                    // employee came in / went out (e.g. to verify a late).
                    $obj->{"tin_{$dayNum}"} = $rec->time_in ? Carbon::parse($rec->time_in)->format('g:i A') : null;
                    $obj->{"tout_{$dayNum}"} = $rec->time_out ? Carbon::parse($rec->time_out)->format('g:i A') : null;
                }
            }

            return $obj;
        })->values();

        // Current QR token (if one has ever been generated) — so the QR
        // modal opens with the CORRECT, currently-valid link from the start.
        $currentToken = DB::table('app_settings')->where('key', 'checkin_qr_token')->value('value');
        $qrDate = DB::table('app_settings')->where('key', 'checkin_qr_date')->value('value') ?? now()->format('Y-m-d');

        // Employees who timed in via QR but haven't timed out yet (shown as "IN").
        $pendingNames = array_values(array_unique($pendingNames));

        // Shift setup (times, grace/break, default shift per employee).
        $shiftSettings = $this->shiftSettings();
        $shiftReady = $this->columnExists('employees', 'shift');
        $employeeShifts = $this->employeeShiftMap();
        $shiftEmployees = Employee::active()->orderBy('employee_name')->pluck('employee_name')->unique()->values()->all();

        // Area setup (Field of Rides / Dino Adventure / Roller Skates).
        $areaOptions = self::AREAS;
        $areaReady = $this->columnExists('employees', 'area');
        $areaEmployees = $this->areaEmployeeList();

        return view('attendance.attendance', compact(
            'records', 'cutoffType', 'periodStart', 'periodEnd', 'periodDays',
            'currentToken', 'qrDate', 'pendingNames',
            'shiftSettings', 'employeeShifts', 'shiftEmployees', 'shiftReady',
            'areaOptions', 'areaReady', 'areaEmployees'
        ));
    }

    // ═══════════════ AREAS (Field of Rides / Dino Adventure / Roller Skates) ═══════════════

    // [lowercase trimmed name => area]. Walang laman kung wala pang `area` column.
    private function employeeAreaMap(): array
    {
        if (!$this->columnExists('employees', 'area')) {
            return [];
        }

        $map = [];
        foreach (DB::table('employees')->whereNotNull('area')->where('area', '!=', '')->get(['employee_name', 'area']) as $r) {
            $map[strtolower(trim($r->employee_name))] = $r->area;
        }

        return $map;
    }

    // Mga Team Leader / Staff na pwedeng bigyan ng area (para sa modal).
    private function areaEmployeeList(): array
    {
        if (!$this->columnExists('employees', 'area')) {
            return [];
        }

        return DB::table('employees')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->whereRaw("LOWER(TRIM(COALESCE(category, ''))) != 'manager'")
            ->orderBy('category')
            ->orderBy('employee_name')
            ->get(['employee_name', 'category', 'area'])
            ->map(fn($r) => [
                'name' => $r->employee_name,
                'category' => ucwords(strtolower(trim((string) $r->category))) ?: 'Staff',
                'area' => $r->area ?: '',
            ])
            ->all();
    }

    // ─── SAVE AREA ASSIGNMENTS ───
    // POST /attendance/areas
    public function saveAreas(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        if (!$this->columnExists('employees', 'area')) {
            return back()->with('error', 'Hindi pa nai-save ang area. Patakbuhin muna ang "php artisan migrate".');
        }

        $validated = $request->validate([
            'employees' => 'required|array',
            'employees.*.name' => 'required|string',
            'employees.*.area' => ['nullable', Rule::in(self::AREAS)],
        ]);

        $before = $this->employeeAreaMap();
        $changed = 0;

        foreach ($validated['employees'] as $row) {
            $area = $row['area'] ?? null;
            $area = $area === '' ? null : $area;
            $key = strtolower(trim($row['name']));

            if (($before[$key] ?? null) === $area) {
                continue;
            }

            DB::table('employees')
                ->where('employee_name', $row['name'])
                ->update(['area' => $area]);
            $changed++;
        }

        if ($changed > 0) {
            $this->audit(
                'employee_areas_updated',
                "Area of {$changed} employee(s) updated",
                'info',
                ['employees_changed' => $changed],
                false,
                'attendance'
            );
        }

        return back()->with('success', $changed > 0
            ? "Na-save ang area ng {$changed} empleyado."
            : 'Walang binago sa mga area.');
    }

    // ─── Live check for the Attendance sheet ───
    // Called by the page every few seconds. Returns a "signature" of all
    // attendance in the visible period. When the signature changes (someone
    // timed in / timed out / an import happened), the page refreshes the
    // table by itself without reloading the whole page.
    //
    // GET /attendance/live?period=YYYY-MM-DD
    public function liveSignature(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $requestedPeriod = $request->query('period');
        $range = null;
        if ($requestedPeriod && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedPeriod)) {
            try {
                $range = $this->cutoffRangeForDate(Carbon::parse($requestedPeriod));
            } catch (\Throwable $e) {
                $range = null;
            }
        }
        [$periodStart, $periodEnd] = $range ?? $this->currentCutoffRange();

        $rows = Attendance::whereBetween('attendance_date', [
                $periodStart->format('Y-m-d'),
                $periodEnd->format('Y-m-d'),
            ])
            ->orderBy('employee_name')
            ->orderBy('attendance_date')
            ->get(['employee_name', 'attendance_date', 'time_in', 'time_out', 'remarks', 'status', 'source']);

        return response()->json([
            'sig' => md5($rows->toJson()),
            'count' => $rows->count(),
        ]);
    }

    // ─── Which half-month period has the most recent attendance data ───
    // Uses MAX(attendance_date) (import and QR check-in both write to this
    // column) rather than updated_at/created_at, since the `attendance`
    // table doesn't have timestamp columns.
    //
    // NOTE: the Attendance sheet itself no longer uses this — it always
    // shows today's period (see currentCutoffRange()). This is kept for
    // anything else that wants "the period of the latest data".
    private function latestCutoffRange()
    {
        $latestDate = Attendance::max('attendance_date');

        if (!$latestDate) {
            return $this->currentCutoffRange();
        }

        return $this->cutoffRangeForDate(Carbon::parse($latestDate));
    }

    // ─── Same as latestCutoffRange(), but can force a half ───
    // Used by the Salary page and Generate Salary so Salary and Payroll
    // always look at the same period.
    private function latestCutoffRangeFor($cutoffType = null)
    {
        $latestDate = $this->completedOnly(Attendance::query())->max('attendance_date');
        $base = $latestDate ? Carbon::parse($latestDate) : now();

        if ($cutoffType) {
            $base = $base->copy()->day($cutoffType === '1st' ? 1 : 16);
        }

        return $this->cutoffRangeForDate($base);
    }

    // ─── Compute the 1st/2nd-half period boundaries for a given date ───
    private function cutoffRangeForDate(Carbon $date)
    {
        $isFirst = $date->day <= 15;

        $start = $isFirst
            ? $date->copy()->startOfMonth()
            : $date->copy()->startOfMonth()->addDays(15);

        $end = $isFirst
            ? $date->copy()->startOfMonth()->addDays(14)
            : $date->copy()->endOfMonth();

        return [$start, $end, $isFirst ? '1st' : '2nd'];
    }

    // ─── Determine cutoff period based on TODAY's real date ───
    private function currentCutoffRange($cutoffType = null)
    {
        $today = now();

        if ($cutoffType) {
            $isFirst = $cutoffType === '1st';

            $start = $isFirst
                ? $today->copy()->startOfMonth()
                : $today->copy()->startOfMonth()->addDays(15);

            $end = $isFirst
                ? $today->copy()->startOfMonth()->addDays(14)
                : $today->copy()->endOfMonth();

            return [$start, $end, $isFirst ? '1st' : '2nd'];
        }

        return $this->cutoffRangeForDate($today);
    }

    // ─── QR check-ins that haven't been timed out yet ───
    // A QR self check-in is only "complete" once the employee taps Time Out.
    // Until then the Attendance sheet shows it as "IN", but it must NOT be
    // counted in Salary / Payroll. Bio rows are never affected.
    private function completedOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('source')
              ->orWhere('source', '!=', 'qr')
              ->orWhereNotNull('time_out');
        });
    }

    // ─── Extract late minutes / undertime minutes / OT hours from remarks ──
    //
    // Formats understood:
    //   "+1"      overtime hours
    //   "-15"     LATE arrival past the shift's grace period, by this many
    //             minutes — the employee still worked a full day otherwise.
    //   "-20/+1"  late AND overtime on the same day
    //   "Nh"      UNDERTIME — hours actually worked when the full required
    //             shift length wasn't completed (e.g. "7h", "3.18h").
    //
    // late_minutes and undertime_minutes are returned separately on
    // purpose — storePayroll() prices them differently (late carries the
    // late penalty multiplier; undertime is a plain pro-rated reduction).
    private function parseRemarks(?string $remarks, int $minutesPerDay = 480): array
    {
        $remarks = trim((string) $remarks);

        if ($remarks === '' || $remarks === 'P' || $remarks === '-') {
            return ['late_minutes' => 0, 'undertime_minutes' => 0, 'ot_hours' => 0.0];
        }

        if (preg_match('/^(\d+(?:\.\d+)?)h$/i', $remarks, $m)) {
            // Hours actually worked, short of a full day — this is
            // UNDERTIME, never counted as "late".
            $shortage = (int) round($minutesPerDay - ((float) $m[1]) * 60);
            return ['late_minutes' => 0, 'undertime_minutes' => max(0, $shortage), 'ot_hours' => 0.0];
        }

        $late = 0;
        $ot = 0.0;
        foreach (explode('/', $remarks) as $part) {
            $part = trim($part);
            if (str_starts_with($part, '+') && is_numeric(substr($part, 1))) {
                $ot += (float) substr($part, 1);
            } elseif (str_starts_with($part, '-') && is_numeric(substr($part, 1))) {
                $late += (int) abs((float) $part);
            }
        }

        return ['late_minutes' => $late, 'undertime_minutes' => 0, 'ot_hours' => $ot];
    }

    // ═══════════════ SHIFTS (Opening / Mid / Closing) ═══════════════
    // Remarks formats written by QR check-in:
    //   P        full day (8 hours after break), nothing to report
    //   +1       1 whole hour of overtime (minutes are not paid)
    //   7h       hours actually worked when the day was short (undertime)
    // (Late formats "-20" and "-20/+1" still come from the bio file and
    //  from computeShiftRemarks().)

    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $k = $table . '.' . $column;

        if (!isset($cache[$k])) {
            try {
                $cache[$k] = Schema::hasColumn($table, $column);
            } catch (\Throwable $e) {
                $cache[$k] = false;
            }
        }

        return $cache[$k];
    }

    private function shiftSettings(): array
    {
        $defaults = [
            'opening' => ['label' => 'Opening', 'start' => '07:00', 'end' => '16:00'],
            'mid'     => ['label' => 'Mid',     'start' => '09:00', 'end' => '18:00'],
            'closing' => ['label' => 'Closing', 'start' => '12:00', 'end' => '21:00'],
        ];

        $keys = ['shift_grace_minutes', 'shift_break_minutes'];
        foreach (array_keys($defaults) as $k) {
            $keys[] = "shift_{$k}_start";
            $keys[] = "shift_{$k}_end";
        }

        try {
            $saved = DB::table('app_settings')->whereIn('key', $keys)->pluck('value', 'key');
        } catch (\Throwable $e) {
            $saved = collect();
        }

        $isTime = fn($v) => is_string($v) && preg_match('/^\d{2}:\d{2}$/', $v) === 1;

        $shifts = [];
        foreach ($defaults as $k => $sh) {
            $start = $saved->get("shift_{$k}_start");
            $end = $saved->get("shift_{$k}_end");
            $shifts[$k] = [
                'label' => $sh['label'],
                'start' => $isTime($start) ? $start : $sh['start'],
                'end' => $isTime($end) ? $end : $sh['end'],
            ];
        }

        $grace = $saved->get('shift_grace_minutes');
        $break = $saved->get('shift_break_minutes');

        return [
            'grace' => is_numeric($grace) ? (int) $grace : 15,
            'break' => is_numeric($break) ? (int) $break : 60,
            'shifts' => $shifts,
        ];
    }

    private function employeeShiftMap(): array
    {
        if (!$this->columnExists('employees', 'shift')) {
            return [];
        }

        return DB::table('employees')->whereNotNull('shift')->pluck('shift', 'employee_name')->all();
    }

    private function employeeShiftKey(string $name): ?string
    {
        if (!$this->columnExists('employees', 'shift')) {
            return null;
        }

        return DB::table('employees')->where('employee_name', $name)->value('shift') ?: null;
    }

    private function requiredMinutes(): int
    {
        $m = (int) (PayrollSetting::current()->minutes_per_day ?? 0);

        return $m > 0 ? $m : 480;
    }

    private function toMinutes(?string $t): ?int
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})/', trim((string) $t), $m)) {
            return null;
        }

        return ((int) $m[1]) * 60 + (int) $m[2];
    }

    // Minutes of overtime -> hours, in 30-minute steps (rounded down).
    private function otFromMinutes(int $minutes): float
    {
        $step = self::OT_STEP_MINUTES;

        return floor(max(0, $minutes) / $step) * ($step / 60);
    }

    // 1.0 -> "1", 1.5 -> "1.5", 2.5 -> "2.5"
    private function formatOt(float $hours): string
    {
        return rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.');
    }

    private function formatDutyHours(int $minutes): string
    {
        $h = round(max(0, $minutes) / 60, 2);
        $txt = rtrim(rtrim(number_format($h, 2, '.', ''), '0'), '.');

        return ($txt === '' ? '0' : $txt) . 'h';
    }

    /**
     * Returns the remarks value ("P", "-20", "+1", "-20/+1", "7h", ...) or
     * null when it can't be worked out (missing time in/out or times).
     *
     * NOTE: a "Nh" result here always means UNDERTIME (worked fewer hours
     * than required), never "late" — see parseRemarks().
     */
    private function computeShiftRemarks(?string $timeIn, ?string $timeOut, string $shiftStart, string $shiftEnd, int $grace, int $break, int $requiredMinutes): ?string
    {
        $in = $this->toMinutes($timeIn);
        $out = $this->toMinutes($timeOut);
        $start = $this->toMinutes($shiftStart);
        $end = $this->toMinutes($shiftEnd);

        if ($in === null || $out === null || $start === null || $end === null) {
            return null;
        }

        if ($end <= $start) {
            $end += 1440; // shift runs past midnight
        }
        if ($out < $in) {
            $out += 1440; // timed out after midnight
        }

        $lateRaw = max(0, $in - $start);
        $late = $lateRaw > $grace ? $lateRaw : 0;     // within grace = on time
        $underRaw = max(0, $end - $out);
        $under = $underRaw > $grace ? $underRaw : 0;  // leaving within grace = no undertime
        $otHours = $this->otFromMinutes($out - $end); // 30-minute steps (1, 1.5, 2, ...)

        // Paid length of the (possibly adjusted) schedule: the break is
        // taken out of any shift long enough to include one.
        $schedSpan = $end - $start;
        $schedDuty = $schedSpan - ($schedSpan >= 300 ? $break : 0);

        // Normal day: full-length schedule, nobody left early.
        if ($under === 0 && $schedDuty >= $requiredMinutes) {
            $parts = [];
            if ($late > 0) {
                $parts[] = '-' . $late;
            }
            if ($otHours > 0) {
                $parts[] = '+' . $this->formatOt($otHours);
            }

            return $parts ? implode('/', $parts) : 'P';
        }

        // Short day (early time-out, ...): report the hours actually
        // worked (UNDERTIME, not late).
        $effIn = ($in <= $start + $grace) ? $start : $in;
        $span = max(0, $out - $effIn);
        $duty = $span - ($span >= 300 ? $break : 0);

        // Full day = required hours, or short by no more than the grace period.
        if ($duty >= $requiredMinutes - $grace) {
            $extra = $this->otFromMinutes($duty - $requiredMinutes);

            return $extra > 0 ? '+' . $this->formatOt($extra) : 'P';
        }

        return $this->formatDutyHours($duty);
    }

    private function shiftRemarksFor(string $name, ?string $shiftKey, ?string $adjStart, ?string $adjEnd, ?string $timeIn, ?string $timeOut): ?string
    {
        if (!$timeIn || !$timeOut) {
            return null;
        }

        $cfg = $this->shiftSettings();
        $key = $shiftKey ?: $this->employeeShiftKey($name);

        if (!$key || !isset($cfg['shifts'][$key])) {
            // No shift known: judge by the hours actually worked.
            return $this->hoursOnlyRemarks($timeIn, $timeOut, $cfg['break']);
        }

        $sh = $cfg['shifts'][$key];

        return $this->computeShiftRemarks(
            $timeIn,
            $timeOut,
            $adjStart ?: $sh['start'],
            $adjEnd ?: $sh['end'],
            $cfg['grace'],
            $cfg['break'],
            $this->requiredMinutes()
        );
    }

    // Remarks based purely on hours worked (break deducted):
    //   P    = 8 hours (full day)
    //   +N   = overtime beyond 8h, in 30-minute steps (+1, +1.5, +2, +2.5 ...)
    //   Short by 15 minutes or less (the grace period) still counts as "P".
    //   Nh   = short of 8 hours — the hours actually worked (undertime)
    //
    // Examples (1 hour break, 8 hours required):
    //   7:00 AM -> 4:00 PM  = 9h - 1h = 8h   -> "P"
    //   7:00 AM -> 5:00 PM  = 10h - 1h = 9h  -> "+1"
    //   7:00 AM -> 3:00 PM  = 8h - 1h = 7h   -> "7h"
    private function hoursOnlyRemarks(?string $timeIn, ?string $timeOut, int $break): ?string
    {
        $in = $this->toMinutes($timeIn);
        $out = $this->toMinutes($timeOut);

        if ($in === null || $out === null) {
            return null;
        }

        if ($out < $in) {
            $out += 1440; // timed out after midnight
        }

        $span = $out - $in;
        $duty = $span - ($span >= 300 ? $break : 0);   // deduct the break
        $required = $this->requiredMinutes();          // 480 = 8 hours
        $grace = $this->shiftSettings()['grace'];      // 15 minutes by default

        // Full day: 8 hours, or short by no more than the grace period.
        if ($duty >= $required - $grace) {
            $otHours = $this->otFromMinutes($duty - $required);

            return $otHours > 0 ? '+' . $this->formatOt($otHours) : 'P';
        }

        return $this->formatDutyHours($duty);
    }

    // ─── Late check at Time In (shown right away to the employee) ───
    // Returns null when the employee has no shift assigned, otherwise:
    //   raw   = actual minutes after shift start
    //   late  = minutes counted as late (only if beyond the grace period)
    //   grace = grace period in minutes
    //   start = shift start, formatted (e.g. "9:00 AM")
    private function lateAtTimeIn(string $name, string $timeIn, ?string $adjStart = null): ?array
    {
        $key = $this->employeeShiftKey($name);
        $cfg = $this->shiftSettings();

        if (!$key || !isset($cfg['shifts'][$key])) {
            return null;
        }

        $startStr = $adjStart ?: $cfg['shifts'][$key]['start'];
        $in = $this->toMinutes($timeIn);
        $start = $this->toMinutes($startStr);

        if ($in === null || $start === null) {
            return null;
        }

        $raw = max(0, $in - $start);

        return [
            'raw'   => $raw,
            'late'  => $raw > $cfg['grace'] ? $raw : 0,
            'grace' => $cfg['grace'],
            'start' => Carbon::createFromFormat('H:i', substr($startStr, 0, 5))->format('g:i A'),
        ];
    }

    // ─── Which date a QR scan is recorded under ───
    // Default = the real date today. The day the admin picked when
    // generating the QR (checkin_qr_date) is honored — earlier OR later
    // than today — but only if the QR was generated within the last
    // 2 hours. After that it goes back to today's real date, so a
    // forgotten QR can't make later check-ins land on the wrong day.
    private function resolveCheckinDate(): string
    {
        $today = now()->toDateString();
        $row = DB::table('app_settings')->where('key', 'checkin_qr_date')->first();

        if (!$row || !$row->value || $row->value === $today) {
            return $today;
        }

        $generatedAt = !empty($row->updated_at) ? Carbon::parse($row->updated_at) : null;

        return ($generatedAt && $generatedAt->diffInMinutes(now()) <= 120)
            ? $row->value
            : $today;
    }

    private function checkinFail(Request $request, string $message, int $status = 422)
    {
        return $request->wantsJson()
            ? response()->json(['message' => $message], $status)
            : back()->with('error', $message);
    }

    // ─── SAVE SHIFT TIMES, GRACE/BREAK AND EMPLOYEE DEFAULT SHIFTS ───
    public function saveShifts(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'shifts' => 'required|array',
            'shifts.*.start' => 'required|date_format:H:i',
            'shifts.*.end' => 'required|date_format:H:i',
            'grace_minutes' => 'required|integer|min:0|max:120',
            'break_minutes' => 'required|integer|min:0|max:240',
            'employees' => 'nullable|array',
            'employees.*.name' => 'required|string',
            'employees.*.shift' => 'nullable|in:opening,mid,closing',
        ]);

        // For the log: capture the previous settings BEFORE saving
        $shiftsBefore    = $this->shiftSettings();
        $empShiftsBefore = $this->employeeShiftMap();

        $set = function (string $key, $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => (string) $value, 'updated_at' => now()]
            );
        };

        foreach (['opening', 'mid', 'closing'] as $k) {
            if (isset($validated['shifts'][$k])) {
                $set("shift_{$k}_start", $validated['shifts'][$k]['start']);
                $set("shift_{$k}_end", $validated['shifts'][$k]['end']);
            }
        }
        $set('shift_grace_minutes', $validated['grace_minutes']);
        $set('shift_break_minutes', $validated['break_minutes']);

        $note = '';
        if (!empty($validated['employees'])) {
            if ($this->columnExists('employees', 'shift')) {
                foreach ($validated['employees'] as $row) {
                    DB::table('employees')
                        ->where('employee_name', $row['name'])
                        ->update(['shift' => $row['shift'] ?: null]);
                }
            } else {
                $note = ' Employee default shifts were NOT saved — run "php artisan migrate" first.';
            }
        }

        // LOG: changes to shift times / grace / break / employee default shift.
        // These affect who counts as "late" and therefore pay, so they matter.
        $shiftsAfter    = $this->shiftSettings();
        $empShiftsAfter = $this->employeeShiftMap();

        $oldVals = [];
        $newVals = [];
        if ($shiftsBefore['grace'] !== $shiftsAfter['grace']) {
            $oldVals['grace_minutes'] = $shiftsBefore['grace'];
            $newVals['grace_minutes'] = $shiftsAfter['grace'];
        }
        if ($shiftsBefore['break'] !== $shiftsAfter['break']) {
            $oldVals['break_minutes'] = $shiftsBefore['break'];
            $newVals['break_minutes'] = $shiftsAfter['break'];
        }
        foreach ($shiftsAfter['shifts'] as $k => $sh) {
            $b = $shiftsBefore['shifts'][$k] ?? null;
            if ($b && ($b['start'] !== $sh['start'] || $b['end'] !== $sh['end'])) {
                $oldVals["shift_{$k}"] = $b['start'] . '-' . $b['end'];
                $newVals["shift_{$k}"] = $sh['start'] . '-' . $sh['end'];
            }
        }

        $empChanged = 0;
        foreach (array_unique(array_merge(array_keys($empShiftsBefore), array_keys($empShiftsAfter))) as $empName) {
            if (($empShiftsBefore[$empName] ?? null) !== ($empShiftsAfter[$empName] ?? null)) {
                $empChanged++;
            }
        }

        if ($oldVals || $newVals || $empChanged > 0) {
            $this->audit(
                'shift_settings_updated',
                'Shift settings updated'
                    . ($oldVals ? ' (times/grace/break)' : '')
                    . ($empChanged > 0 ? " and the default shift of {$empChanged} employee(s)" : ''),
                'warning',
                [
                    'old'                => $oldVals,
                    'new'                => $newVals,
                    'employees_changed'  => $empChanged,
                ],
                false,
                'attendance'
            );
        }

        return back()->with('success', 'Shift settings saved.' . $note);
    }

    // ─── SALARY PAGE ───────────────────────────────
    // GET /attendance/salary?area=Dino Adventure
    // Kapag may ?area=, ang Team Leader at Staff lang ng area na iyon ang
    // lalabas. Ang Manager ay walang area, kaya sa "Lahat ng area" lang siya.
    public function salary(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $area = $this->requestedArea($request->query('area'));

        $rates = SalaryRate::all()->keyBy('position');
        $settings = PayrollSetting::current();

        // Period of the latest attendance data.
        [$periodStart, $periodEnd, $cutoffType] = $this->latestCutoffRangeFor();

        // Total days in THIS half (a 31-day month's 2nd cutoff has 16 days).
        $periodDays = $periodStart->diffInDays($periodEnd) + 1;

        // The 2nd-cutoff day count for the CURRENT month, regardless of
        // which half happens to be showing right now (for the holiday modal).
        $daysInMonth = $periodStart->copy()->startOfMonth()->daysInMonth;
        $secondCutoffDays = $daysInMonth - 15;

        $records = $this->completedOnly(Attendance::query())->whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        $areaMap = $this->employeeAreaMap();

        // Pivot attendance rows into day_1..day_N shape
        $attendance = $records->groupBy('employee_name')->map(function ($recs, $name) use ($periodStart, $periodDays, $areaMap) {
            $first = $recs->first();
            $obj = new \stdClass();
            $obj->name = $name;
            $obj->category = $first->category ? ucwords(strtolower(trim($first->category))) : 'Staff';
            // Area ay galing sa Employees table. Ang Manager ay walang area.
            $obj->area = $obj->category === 'Manager'
                ? ''
                : ($areaMap[strtolower(trim($name))] ?? '');
            $obj->days_worked = 0;

            for ($i = 1; $i <= $periodDays; $i++) {
                $obj->{"day_{$i}"} = null;
            }

            foreach ($recs as $rec) {
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                if ($dayNum >= 1 && $dayNum <= $periodDays) {
                    $obj->{"day_{$dayNum}"} = $rec->remarks
                        ?: (strtolower($rec->status) === 'present' ? 'P' : 'A');

                    // Days Worked = count of days where status = present.
                    // Same value used by storePayroll(), so the two always match.
                    if (strtolower($rec->status) === 'present') {
                        $obj->days_worked++;
                    }
                }
            }

            return $obj;
        })->values();

        // Piliin lang ang napiling area (kung may napili).
        if ($area !== null) {
            $attendance = $attendance->filter(fn($o) => $o->area === $area)->values();
        }

        // Payroll generated FOR THIS PERIOD, and generated AFTER the last
        // import of attendance for this period (older ones are stale but
        // stay in Payroll History).
        $importedAt = DB::table('attendance_imports')
            ->whereDate('period_start', $periodStart->format('Y-m-d'))
            ->whereDate('period_end', $periodEnd->format('Y-m-d'))
            ->value('imported_at');

        $salaryQuery = Payroll::whereDate('payroll_period_start', $periodStart->format('Y-m-d'))
            ->whereDate('payroll_period_end', $periodEnd->format('Y-m-d'));

        if ($importedAt) {
            $salaryQuery->where('generated_at', '>=', $importedAt);
        }

        // Per empleyado ang pagkuha ng pinakabagong payroll, kaya ang
        // pag-generate ng isang area ay hindi nakakaapekto sa iba.
        $salaryMap = $salaryQuery
            ->orderByDesc('generated_at')
            ->get()
            ->unique('name')
            ->keyBy('name');

        $areaOptions = self::AREAS;

        return view('attendance.salary', compact(
            'rates', 'attendance', 'salaryMap', 'periodStart', 'periodEnd', 'cutoffType', 'settings',
            'periodDays', 'secondCutoffDays', 'area', 'areaOptions'
        ));
    }

    // Ibabalik ang area kung valid (Field of Rides / Dino Adventure / Roller Skates),
    // kung hindi ay null (= lahat ng area).
    private function requestedArea($value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return in_array($value, self::AREAS, true) ? $value : null;
    }

    public function updateRates(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'rates' => 'required|array',
            'rates.*' => 'required|numeric|min:0',
        ]);

        // For the log: previous daily rate of each position BEFORE updating
        $oldRates = SalaryRate::all()->pluck('daily_rate', 'position')
            ->map(fn($v) => (float) $v)->all();

        foreach ($request->rates as $position => $dailyRate) {
            SalaryRate::updateOrCreate(
                ['position' => $position],
                ['daily_rate' => $dailyRate]
            );
        }

        // LOG: daily rate changes. Only the positions that changed are recorded.
        $oldChanged = [];
        $newChanged = [];
        foreach ($request->rates as $position => $dailyRate) {
            $new = (float) $dailyRate;
            $old = $oldRates[$position] ?? null;

            if ($old === null || abs($old - $new) > 0.001) {
                $oldChanged[$position] = $old;
                $newChanged[$position] = $new;
            }
        }

        if ($newChanged) {
            $this->audit(
                'salary_rates_updated',
                'Daily rate updated for: ' . implode(', ', array_keys($newChanged)),
                'warning',
                ['old' => $oldChanged, 'new' => $newChanged],
                false,
                'payroll'
            );
        }

        return redirect()->route('salary', array_filter(['area' => $request->input('area')]))
            ->with('success', 'Daily rates updated successfully.');
    }

    // ─── UPDATE OVERTIME / LATE / HOLIDAY-OT SETTINGS ───────────
    public function updatePayrollSettings(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'ot_multiplier' => 'required|numeric|min:1',
            'holiday_ot_multiplier' => 'required|numeric|min:1',
            'minutes_per_day' => 'required|integer|min:1',
            'late_rate_multiplier' => 'required|numeric|min:0',
        ]);

        $keys = ['ot_multiplier', 'holiday_ot_multiplier', 'minutes_per_day', 'late_rate_multiplier'];

        $settings = PayrollSetting::current();

        // For the log: previous values BEFORE updating
        $before = [];
        foreach ($keys as $k) {
            $before[$k] = (float) $settings->{$k};
        }

        $settings->update($request->only($keys));

        // LOG: changes to OT / late / holiday-OT settings (affects all pay)
        $oldChanged = [];
        $newChanged = [];
        foreach ($keys as $k) {
            $after = (float) $request->input($k);
            if (abs($before[$k] - $after) > 0.0001) {
                $oldChanged[$k] = $before[$k];
                $newChanged[$k] = $after;
            }
        }

        if ($newChanged) {
            $this->audit(
                'payroll_settings_updated',
                'Payroll settings updated: ' . implode(', ', array_keys($newChanged)),
                'warning',
                ['old' => $oldChanged, 'new' => $newChanged],
                false,
                'payroll'
            );
        }

        return redirect()->route('salary')->with('success', 'Overtime & deduction settings updated successfully.');
    }

    // ─── GENERATE PAYROLL ───────────────────────────
    // Kung may `area` sa request (Field of Rides / Dino Adventure /
    // Roller Skates), ang Team Leader at Staff lang ng area na iyon ang
    // kakalkulahin. Walang `area` = lahat (kasama ang Manager), gaya ng dati.
    public function storePayroll(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $cutoffType = $request->input('cutoff_type', '1st');
        $applyGovt = $request->input('apply_govt_deductions') === '1';
        $holidays = $request->input('holidays', []); // [day_number => 'regular'|'special']
        $area = $this->requestedArea($request->input('area'));

        [$periodStart, $periodEnd] = $this->latestCutoffRangeFor($cutoffType);

        $rates = SalaryRate::all()->keyBy('position');
        $settings = PayrollSetting::current();
        $hoursPerDay = $settings->minutes_per_day > 0 ? $settings->minutes_per_day / 60 : 8;

        $categoryToPosition = [
            'manager' => 'manager',
            'team leader' => 'team_leader',
            'staff' => 'staff',
        ];

        $records = $this->completedOnly(Attendance::query())->whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        // Isang area lang: itira ang mga empleyadong nasa area na iyon.
        // (Ang Manager ay walang area, kaya hindi siya kasama dito.)
        if ($area !== null) {
            $areaMap = $this->employeeAreaMap();
            $records = $records->filter(
                fn($r) => ($areaMap[strtolower(trim($r->employee_name))] ?? null) === $area
                    && strtolower(trim((string) $r->category)) !== 'manager'
            )->values();
        }

        if ($records->isEmpty()) {
            return redirect()->route('salary', array_filter(['area' => $area]))->with('error', $area !== null
                ? "Walang attendance records para sa {$area} sa cutoff na ito. Siguraduhing may area ang mga empleyado (Attendance > Areas)."
                : 'No attendance records found for this cutoff period.');
        }

        // Does the payrolls table have the undertime_deduction column yet?
        // If not (migration not run), fold undertime into the same
        // `deduction` column as before.
        $hasUndertimeColumn = $this->columnExists('payrolls', 'undertime_deduction');

        $generatedAt = now();
        $count = 0;
        $sumGross = 0.0; // for the log
        $sumNet = 0.0;   // for the log

        foreach ($records->groupBy('employee_name') as $name => $recs) {
            $first = $recs->first();
            $category = $first->category ?: 'Staff';
            $positionKey = $categoryToPosition[strtolower($category)] ?? 'staff';
            $dailyRate = floatval($rates->get($positionKey)?->daily_rate ?? 0);
            $hourlyRate = $hoursPerDay > 0 ? $dailyRate / $hoursPerDay : 0;

            $basicPay = 0;
            $holidayPay = 0;
            $overtimePay = 0;
            $holidayOtPay = 0;
            $lateDeduction = 0;
            $undertimeDeduction = 0;
            $totalDays = 0;

            foreach ($recs as $rec) {
                if (strtolower($rec->status) !== 'present') continue;

                $totalDays++;

                // "Day 1–15" in the holiday modal is relative to the start
                // of the cutoff (so Day 1 = the 16th on the 2nd cutoff).
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                $holidayType = $holidays[$dayNum] ?? null;
                $isHoliday = $holidayType !== null;
                $multiplier = $holidayType === 'regular' ? 2.0 : ($holidayType === 'special' ? 1.3 : 1.0);

                // Straight pay first (kept separate from the holiday premium
                // so the payslip can show "Daily Rate" and "Holiday" apart).
                $basicPay += $dailyRate;

                if ($isHoliday) {
                    $holidayPay += $dailyRate * ($multiplier - 1);
                }

                [
                    'late_minutes' => $lateMinutes,
                    'undertime_minutes' => $undertimeMinutes,
                    'ot_hours' => $otHours,
                ] = $this->parseRemarks($rec->remarks, (int) $settings->minutes_per_day);

                // LATE: arrived past the shift's grace period. Carries the
                // late penalty multiplier HR configured in Payroll Settings.
                if ($lateMinutes > 0 && $settings->minutes_per_day > 0) {
                    $lateDeduction += ($dailyRate / $settings->minutes_per_day) * $lateMinutes * $settings->late_rate_multiplier;
                }

                // UNDERTIME: worked a short/incomplete day. Priced as a plain
                // pro-rated reduction — multiplier of 1, never the late
                // penalty.
                if ($undertimeMinutes > 0 && $settings->minutes_per_day > 0) {
                    $undertimeDeduction += ($dailyRate / $settings->minutes_per_day) * $undertimeMinutes;
                }

                if ($otHours > 0) {
                    if ($isHoliday) {
                        $holidayOtPay += $otHours * $hourlyRate * $settings->holiday_ot_multiplier;
                    } else {
                        $overtimePay += $otHours * $hourlyRate * $settings->ot_multiplier;
                    }
                }
            }

            $grossPay = $basicPay + $holidayPay + $overtimePay + $holidayOtPay;

            // NOTE: placeholder computation only — replace with the correct
            // SSS / PhilHealth / Pag-IBIG / withholding tax brackets.
            $sss = 0; $philhealth = 0; $pagibig = 0; $withholding = 0;
            if ($applyGovt) {
                $sss = round($grossPay * 0.045, 2);
                $philhealth = round($grossPay * 0.02, 2);
                $pagibig = 100.00;
                $withholding = round(max(0, $grossPay - 20833) * 0.15, 2);
            }

            $totalDeductions = $sss + $philhealth + $pagibig + $withholding + $lateDeduction + $undertimeDeduction;
            $netSalary = $grossPay - $totalDeductions;

            $payrollData = [
                'employee_id' => $first->employee_id,
                'name' => $name,
                'category' => $category,
                'payroll_period_start' => $periodStart->format('Y-m-d'),
                'payroll_period_end' => $periodEnd->format('Y-m-d'),
                'cutoff_type' => $cutoffType,
                'total_days' => $totalDays,
                'present_days' => $totalDays,
                'daily_rate' => $dailyRate,
                'total_hours' => $recs->sum('total_hours'),
                'basic_salary' => $basicPay,
                'holiday_pay' => round($holidayPay, 2),
                'overtime_pay' => round($overtimePay, 2),
                'holiday_ot_pay' => round($holidayOtPay, 2),
                'sss_deduction' => $sss,
                'philhealth_deduction' => $philhealth,
                'pagibig_deduction' => $pagibig,
                'withholding_tax' => $withholding,
                // deduction = LATE only when the column split is available;
                // otherwise it carries both, same as before the split.
                'deduction' => round($hasUndertimeColumn ? $lateDeduction : ($lateDeduction + $undertimeDeduction), 2),
                'gross_pay' => round($grossPay, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_pay' => round($netSalary, 2),
                'net_salary' => round($netSalary, 2),
                'payroll_status' => 'Pending',
                'generated_at' => $generatedAt,
            ];

            if ($hasUndertimeColumn) {
                $payrollData['undertime_deduction'] = round($undertimeDeduction, 2);
            }

            Payroll::create($payrollData);

            $count++;
            $sumGross += round($grossPay, 2);
            $sumNet   += round($netSalary, 2);
        }

        // LOG: payroll generated. No per-employee names or pay, totals only;
        // the details are already in Payroll History. batch_id = generated_at.
        $this->audit(
            'payroll_generated',
            "Payroll generated ({$cutoffType} cutoff, {$periodStart->format('M j')}–{$periodEnd->format('M j, Y')}"
                . ($area !== null ? ", {$area}" : '') . ") "
                . "for {$count} employee(s)",
            'info',
            [
                'batch_id'          => $generatedAt->format('Y-m-d H:i:s'),
                'cutoff_type'       => $cutoffType,
                'area'              => $area ?? 'all',
                'period_start'      => $periodStart->format('Y-m-d'),
                'period_end'        => $periodEnd->format('Y-m-d'),
                'employees'         => $count,
                'total_gross'       => round($sumGross, 2),
                'total_net'         => round($sumNet, 2),
                'govt_deductions'   => $applyGovt,
                'holiday_days'      => is_array($holidays) ? count($holidays) : 0,
            ],
            false,
            'payroll'
        );

        $note = $hasUndertimeColumn
            ? ''
            : ' (Note: "php artisan migrate" has not been run yet — Late and Undertime are temporarily combined in one column.)';

        $scope = $area !== null ? " ({$area})" : '';

        return redirect()->route('salary', array_filter(['area' => $area]))
            ->with('success', "Payroll generated for {$count} employee(s){$scope}." . $note);
    }

    // ─── PAYROLL HISTORY ────────────────────────────
    public function payrollHistory(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $batches = Payroll::selectRaw('generated_at as batch_id, payroll_period_end as pay_date, cutoff_type, generated_at')
            ->whereNotNull('generated_at')
            ->distinct()
            ->orderByDesc('generated_at')
            ->get();

        $selectedBatch = $request->query('batch') ?? optional($batches->first())->batch_id;

        $isLatestBatch = $selectedBatch
            && $batches->isNotEmpty()
            && (string) $selectedBatch === (string) $batches->first()->batch_id;

        $salaries = $selectedBatch
            ? Payroll::where('generated_at', $selectedBatch)->get()
            : collect();

        $totalEmployees = $salaries->count();
        $totalBasic = $salaries->sum('basic_salary');
        $totalDeductions = $salaries->sum(function ($sal) {
            return $sal->deduction + ($sal->undertime_deduction ?? 0) + $sal->sss_deduction + $sal->philhealth_deduction
                + $sal->pagibig_deduction + $sal->withholding_tax;
        });
        $totalNet = $salaries->sum('net_salary');

        return view('attendance.payroll-history', compact(
            'batches', 'selectedBatch', 'isLatestBatch', 'salaries',
            'totalEmployees', 'totalBasic', 'totalDeductions', 'totalNet'
        ));
    }

    public function deleteBatch($batch)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // For the log: capture a summary of the batch BEFORE deleting it
        $summary = Payroll::where('generated_at', $batch)
            ->selectRaw('COUNT(*) as c, SUM(net_salary) as net, MIN(payroll_period_start) as ps, MAX(payroll_period_end) as pe, MAX(cutoff_type) as ct')
            ->first();

        $deleted = Payroll::where('generated_at', $batch)->delete();

        // LOG: payroll batch deleted (key evidence if there is ever a pay dispute)
        if ($deleted > 0) {
            $this->audit(
                'payroll_batch_deleted',
                "Payroll batch deleted ({$deleted} payslip(s))",
                'critical',
                [
                    'old' => [
                        'batch_id'     => (string) $batch,
                        'payslips'     => (int) $deleted,
                        'total_net'    => (float) ($summary->net ?? 0),
                        'period_start' => $summary->ps ?? null,
                        'period_end'   => $summary->pe ?? null,
                        'cutoff_type'  => $summary->ct ?? null,
                    ],
                ],
                false,
                'payroll'
            );
        }

        return redirect()->route('payroll-history')->with('success', 'Payroll batch deleted successfully.');
    }

    // ─── BULK DELETE PAYROLL BATCHES ────────────────
    // Accepts a list of generated_at values (one per batch) and deletes
    // all payroll rows in those batches.
    //
    // DELETE /payroll-history/bulk-delete
    public function bulkDeleteBatches(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'batches'   => 'required|array|min:1',
            'batches.*' => 'required|string',
        ]);

        // For the log: summary BEFORE deleting
        $summary = Payroll::whereIn('generated_at', $request->batches)
            ->selectRaw('COUNT(*) as c, SUM(net_salary) as net')
            ->first();

        $deleted = DB::transaction(function () use ($request) {
            return Payroll::whereIn('generated_at', $request->batches)->delete();
        });

        // LOG: one line for the whole bulk delete
        if ($deleted > 0) {
            $this->audit(
                'payroll_batches_deleted',
                'Bulk deleted ' . count($request->batches) . " payroll batch(es) ({$deleted} payslip(s))",
                'critical',
                [
                    'old' => [
                        'batches'   => array_slice($request->batches, 0, 50),
                        'payslips'  => (int) $deleted,
                        'total_net' => (float) ($summary->net ?? 0),
                    ],
                ],
                false,
                'payroll'
            );
        }

        return redirect()->route('payroll-history')
            ->with('success', count($request->batches) . " payroll batch(es) deleted ({$deleted} payslip record(s)).");
    }

    public function payslip($id)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $sal = Payroll::findOrFail($id);

        // Hours summary for the payslip: how many OT hours, and how many
        // late and undertime minutes in this cutoff (taken from the
        // attendance remarks, same as when the payroll was generated).
        $minutesPerDay = (int) (PayrollSetting::current()->minutes_per_day ?: 480);
        $summary = ['ot_hours' => 0.0, 'late_minutes' => 0, 'undertime_minutes' => 0];

        $recs = $this->completedOnly(Attendance::query())
            ->where('employee_name', $sal->name)
            ->whereBetween('attendance_date', [
                Carbon::parse($sal->payroll_period_start)->format('Y-m-d'),
                Carbon::parse($sal->payroll_period_end)->format('Y-m-d'),
            ])
            ->get();

        foreach ($recs as $rec) {
            if (strtolower($rec->status) !== 'present') {
                continue;
            }

            $p = $this->parseRemarks($rec->remarks, $minutesPerDay);
            $summary['ot_hours'] += $p['ot_hours'];
            $summary['late_minutes'] += $p['late_minutes'];
            $summary['undertime_minutes'] += $p['undertime_minutes'];
        }

        return view('attendance.payslip', compact('sal', 'summary'));
    }

    // ─── QR SELF CHECK-IN (employee scans a QR code with their phone) ──

    /**
     * Public check-in page — no login required. Employees are listed
     * straight from the Employees table, so a deactivated employee simply
     * disappears from the dropdown.
     *
     * The URL carries a ?token= that must match the current value stored
     * in app_settings (key = 'checkin_qr_token'). Generating a new QR
     * "revokes" every old printed one. If no token has ever been
     * generated, nothing is enforced (backward compatible).
     *
     * GET /attendance/checkin
     */
    public function showCheckin(Request $request)
    {
        $currentToken = DB::table('app_settings')->where('key', 'checkin_qr_token')->value('value');
        $qrDate = DB::table('app_settings')->where('key', 'checkin_qr_date')->value('value') ?? now()->format('Y-m-d');

        if ($currentToken && $request->query('token') !== $currentToken) {
            // LOG: expired/wrong/missing token when opening the page. The token
            // itself is never recorded. Severity 'info' because this is
            // usually just an old printed QR.
            $this->audit(
                'qr_token_invalid',
                'Opened the QR check-in page with ' . ($request->query('token') ? 'a wrong/expired' : 'no') . ' token',
                'info',
                ['stage' => 'page_open', 'token_given' => (bool) $request->query('token')],
                false,
                'attendance',
                true
            );

            return view('attendance.expired');
        }

        $employees = Employee::active()
            ->orderBy('employee_name')
            ->get(['employee_name', 'category']);

        return view('attendance.checkin', compact('employees', 'currentToken', 'qrDate'));
    }

    /**
     * Handle the "Time In" / "Time Out" tap from the QR check-in page.
     *
     * - PIN-protected (the PIN proves the person tapping is the employee
     *   selected from the dropdown).
     * - Re-checks the QR token on submit, so a page opened before the QR
     *   was revoked can't still submit.
     * - Uses SERVER time (now()), never the phone's clock.
     * - Blocks double Time In, double Time Out, and Time Out without Time In.
     * - Tells the employee right away at Time In if they're late.
     * - On Time Out, remarks are computed from the hours worked:
     *   P (8h), +N (overtime hours), or Nh (undertime).
     *
     * POST /attendance/checkin
     */
    public function storeCheckin(Request $request)
    {
        $currentToken = DB::table('app_settings')->where('key', 'checkin_qr_token')->value('value');

        if ($currentToken && $request->input('token') !== $currentToken) {
            // LOG: expired QR on submit (public endpoint, so no user)
            $this->audit(
                'qr_token_invalid',
                'Check-in submitted with an expired/wrong QR token',
                'warning',
                [
                    'stage'         => 'submit',
                    'employee_name' => mb_substr((string) $request->input('employee_name'), 0, 100),
                    'type'          => $request->input('type'),
                ],
                false,
                'attendance',
                true
            );

            return $this->checkinFail(
                $request,
                'This QR code has expired. Please scan the current QR code posted for check-in.',
                410
            );
        }

        $validated = $request->validate([
            'employee_name' => [
                'required', 'string',
                Rule::exists('employees', 'employee_name')->where('status', 'active'),
            ],
            'category' => 'required|string',
            'type' => 'required|in:in,out',
            // Exactly 4 digits — kept as a string so a leading zero isn't dropped.
            'pin' => ['required', 'digits:4'],
        ], [
            'employee_name.exists' => 'You could not be recognized as an active employee. Please contact HR.',
            'pin.required' => 'Please enter your 4-digit PIN.',
            'pin.digits' => 'PIN must be exactly 4 digits.',
        ]);

        $employee = Employee::where('employee_name', $validated['employee_name'])
            ->where('status', 'active')
            ->first();

        if (!$employee || !$employee->pin_code) {
            // LOG: the employee has no PIN (HR must set one)
            $this->audit(
                'checkin_no_pin',
                "{$validated['employee_name']} tried to check in but has no PIN yet",
                'info',
                ['employee_name' => $validated['employee_name'], 'type' => $validated['type']],
                false,
                'attendance',
                true
            );

            return $this->checkinFail($request, 'No PIN has been set for your account yet. Please ask HR to set one.');
        }

        if (!Hash::check($validated['pin'], $employee->pin_code)) {
            // LOG: wrong PIN. event_type = 'login_failed' so that
            // checkBruteForce() counts it (the PIN is only 4 digits, 10,000
            // combinations, and the endpoint is public). The PIN that was
            // typed is never recorded.
            $this->audit(
                'login_failed',
                "Wrong PIN on QR check-in for {$validated['employee_name']}",
                'warning',
                [
                    'reason'        => 'qr_checkin_wrong_pin',
                    'employee_name' => $validated['employee_name'],
                    'type'          => $validated['type'],
                ],
                false,
                'attendance',
                true
            );
            SystemLogger::checkBruteForce($request->ip(), null);

            return $this->checkinFail($request, 'Incorrect PIN.');
        }

        // Real date today by default; the admin's catch-up date only applies
        // for 2 hours after the QR was generated (see resolveCheckinDate()).
        $today = $this->resolveCheckinDate();
        $now = now()->format('H:i:s');

        $existing = Attendance::where('employee_name', $validated['employee_name'])
            ->where('attendance_date', $today)
            ->first();

        $timeIn = $existing->time_in ?? null;
        $timeOut = $existing->time_out ?? null;

        // No double Time In / Time Out, and no Time Out without Time In.
        if ($validated['type'] === 'in') {
            if ($timeIn) {
                return $this->checkinFail(
                    $request,
                    'You already timed in earlier at ' . Carbon::parse($timeIn)->format('g:i A') . '.'
                );
            }
            $timeIn = $now;
        } else {
            if (!$timeIn) {
                return $this->checkinFail($request, 'You have not timed in today. Please time in first.');
            }
            if ($timeOut) {
                return $this->checkinFail(
                    $request,
                    'You already timed out earlier at ' . Carbon::parse($timeOut)->format('g:i A') . '.'
                );
            }

            $worked = ($this->toMinutes($now) ?? 0) - ($this->toMinutes($timeIn) ?? 0);
            if ($worked < 0) {
                $worked += 1440;
            }
            if ($worked < self::MIN_MINUTES_BEFORE_TIMEOUT) {
                return $this->checkinFail(
                    $request,
                    'Time Out is too early. At least ' . self::MIN_MINUTES_BEFORE_TIMEOUT
                    . ' minutes are required after your Time In (' . Carbon::parse($timeIn)->format('g:i A') . ').'
                );
            }

            $timeOut = $now;
        }

        // On Time Out, work out the remarks:
        //  - Employee WITH a shift: late is checked against the shift start
        //    (15-minute grace), plus overtime in 30-minute steps.
        //      e.g. 7:10 in (within grace) / 4:00 PM out -> "P"
        //           7:20 in / 4:00 PM out                -> "-20"
        //           7:00 in / 5:45 PM out                -> "+1.5"
        //  - Employee WITHOUT a shift: judged by hours worked only
        //    (break deducted, 15-minute grace):
        //           7:00 AM -> 4:00 PM = 8h  -> "P"
        //           7:00 AM -> 5:30 PM = 9.5h -> "+1.5"
        //           7:00 AM -> 3:00 PM = 7h  -> "7h"
        $qrRemarks = 'P';
        if ($validated['type'] === 'out') {
            $qrRemarks = $this->shiftRemarksFor(
                $validated['employee_name'],
                null,
                $existing->adjusted_start ?? null,
                $existing->adjusted_end ?? null,
                $timeIn,
                $timeOut
            ) ?? 'P';
        }

        $record = Attendance::updateOrCreate(
            [
                'employee_name' => $validated['employee_name'],
                'attendance_date' => $today,
            ],
            [
                'category' => $validated['category'],
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'status' => 'present',
                'remarks' => 'P',
                'source' => 'qr',
            ]
        );

        // Make sure `source` is really saved as 'qr' even if the
        // model's $fillable doesn't include it.
        $record->forceFill(['source' => 'qr', 'remarks' => $qrRemarks])->save();

        // LOG: normal check-ins are not logged (they are already in the
        // attendance table). But when it is recorded on a different date
        // because of the admin's catch-up date, it is logged, since it
        // changes the pay of a past day.
        if ($today !== now()->toDateString()) {
            $this->audit(
                'checkin_backdated',
                "QR check-in for {$validated['employee_name']} recorded on {$today} (not today)",
                'warning',
                [
                    'employee_name'   => $validated['employee_name'],
                    'type'            => $validated['type'],
                    'recorded_date'   => $today,
                    'real_date'       => now()->toDateString(),
                    'remarks'         => $validated['type'] === 'out' ? $qrRemarks : null,
                ],
                true,
                'attendance',
                true
            );
        }

        // ── Message shown to the employee ──
        $label = $validated['type'] === 'in' ? 'Timed in' : 'Timed out';
        $message = "{$label} successfully at " . now()->format('g:i A') . ". Thank you, {$validated['employee_name']}!";

        if ($validated['type'] === 'in') {
            $info = $this->lateAtTimeIn(
                $validated['employee_name'],
                $timeIn,
                $existing->adjusted_start ?? null
            );

            if ($info === null) {
                $message .= ' (You have no assigned shift, so lateness cannot be computed. Please tell HR.)';
            } elseif ($info['late'] > 0) {
                $message .= " You are {$info['raw']} minutes late (shift start: {$info['start']}).";
            } elseif ($info['raw'] > 0) {
                $message .= " {$info['raw']} min after start, but you are still within the {$info['grace']}-minute grace period.";
            } else {
                $message .= ' You are on time!';
            }

            $message .= ' Your attendance will count once you Time Out.';
        } elseif ($qrRemarks === 'P') {
            $message .= ' You completed your 8 hours today.';
        } elseif (str_starts_with($qrRemarks, '+')) {
            $message .= ' Overtime today: ' . substr($qrRemarks, 1) . ' hour(s).';
        } elseif (preg_match('/^\d+(\.\d+)?h$/i', $qrRemarks)) {
            $message .= " Today's record: {$qrRemarks} worked (short of 8 hours, shown as Undertime).";
        } else {
            $message .= " Today's record: {$qrRemarks}.";
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Issue a brand new QR token, instantly invalidating every previously
     * printed/shared QR link. Admin/HR only.
     *
     * Also accepts an optional `for_date` — a "catch-up" date for scans
     * (e.g. during a bio outage). It is set by the admin only, never by
     * the scanning device, and it only stays in effect for 2 hours
     * (see resolveCheckinDate()). Defaults to today.
     *
     * POST /attendance/qr/regenerate
     */
    public function regenerateQr(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'for_date' => 'nullable|date',
        ]);

        $token = Str::random(32);
        $forDate = $validated['for_date'] ?? now()->format('Y-m-d');

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'checkin_qr_token'],
            ['value' => $token, 'updated_at' => now()]
        );

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'checkin_qr_date'],
            ['value' => $forDate, 'updated_at' => now()]
        );

        $url = route('attendance.checkin', ['token' => $token]);

        // LOG: new QR. The token and URL are never recorded. Flagged as an
        // anomaly when set to a different date (backdated/future), since
        // that is where scans will be recorded.
        $isCatchUp = Carbon::parse($forDate)->toDateString() !== now()->toDateString();
        $this->audit(
            'qr_regenerated',
            'Generated a new check-in QR'
                . ($isCatchUp ? " set to {$forDate} (not today)" : ''),
            $isCatchUp ? 'warning' : 'info',
            ['for_date' => $forDate, 'catch_up_date' => $isCatchUp],
            $isCatchUp,
            'attendance'
        );

        return response()->json([
            'token' => $token,
            'url' => $url,
            'for_date' => $forDate,
        ]);
    }

    // ─── BIO FILE IMPORT (CSV or Excel) ─────────────
    //
    // Expected columns (header row, case-insensitive):
    //   name | category | 1, 2, 3 ... N   (or day1, day2 ... dayN)
    // Cell values:
    //   P       present
    //   +N      present + overtime hours
    //   -N      present, late by N minutes
    //   Nh      present, short day — N hours actually worked (undertime),
    //           e.g. 7h, 5h, 6.5h
    //   -       absent
    public function importAttendance(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        if (!$request->hasFile('import_file')) {
            return redirect()->route('attendance')->with('error', 'No file uploaded.');
        }

        $file = $request->file('import_file');
        $ext = strtolower($file->getClientOriginalExtension());

        $request->validate([
            'period_start' => 'nullable|date',
        ]);

        // Which half of the month this file's day-1..day-N columns map
        // onto: the period currently on screen. If none was sent, default
        // to the current half-month (based on today's date).
        if ($request->filled('period_start')) {
            [$periodStart, $periodEnd, $cutoffType] = $this->cutoffRangeForDate(Carbon::parse($request->input('period_start')));
        } else {
            [$periodStart, $periodEnd, $cutoffType] = $this->currentCutoffRange();
        }
        $periodDays = $periodStart->diffInDays($periodEnd) + 1;

        // Active employee names, normalized (lowercase + trimmed), mapped
        // to the canonical name as stored in the Employees table. Keeps
        // the bio file from creating "ghost" rows for unknown names, and
        // makes every bio row use the SAME casing as QR entries so the
        // sheet groups them into a single row per person.
        $knownNames = Employee::active()
            ->pluck('employee_name')
            ->mapWithKeys(fn($n) => [strtolower(preg_replace('/\s+/', ' ', trim($n))) => $n]);

        $allRows = [];
        $unmatchedNames = []; // rows skipped because the name isn't a known active employee

        try {
            if ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                if (!$handle) {
                    return redirect()->route('attendance')->with('error', 'Unable to read the uploaded file.');
                }

                $header = fgetcsv($handle);
                if (!$header) {
                    fclose($handle);
                    return redirect()->route('attendance')->with('error', 'The uploaded CSV file is empty or invalid.');
                }
                $header = array_map(
                    fn($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))),
                    $header
                );

                while (($row = fgetcsv($handle)) !== false) {
                    // Guard against column count mismatch (avoids array_combine() crash)
                    if (count($row) !== count($header)) {
                        $row = array_pad(array_slice($row, 0, count($header)), count($header), '');
                    }
                    $rowData = array_combine($header, $row);
                    $allRows = array_merge($allRows, $this->buildWideAttendanceRows($rowData, $periodStart, $periodDays, $knownNames, $unmatchedNames));
                }
                fclose($handle);
            } else {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, false);

                if (empty($rows)) {
                    return redirect()->route('attendance')->with('error', 'The uploaded file is empty or invalid.');
                }

                $header = array_map(
                    fn($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) ($h ?? '')))),
                    $rows[0]
                );

                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    if (count($row) !== count($header)) {
                        $row = array_pad(array_slice($row, 0, count($header)), count($header), '');
                    }
                    $rowData = array_combine($header, $row);
                    $allRows = array_merge($allRows, $this->buildWideAttendanceRows($rowData, $periodStart, $periodDays, $knownNames, $unmatchedNames));
                }
            }

            if (empty($allRows) && empty($unmatchedNames)) {
                return redirect()->route('attendance')->with('error', 'No valid attendance rows found in the file.');
            }

            if (empty($allRows) && !empty($unmatchedNames)) {
                // Every single row was unmatched — most likely the wrong
                // file, or the Employees list hasn't been set up yet.
                return redirect()->route('attendance')->with('error',
                    'Nothing was imported — none of the names in the file were found in the Employees list: '
                    . implode(', ', array_unique($unmatchedNames)) . '.');
            }

            // ── PROTECT existing QR (and old Manual) entries for this period ──
            // A bio export marks a day as "-" whenever the employee did NOT
            // scan the bio device — which is exactly what happens when they
            // checked in via QR. Any employee+date that already has a
            // non-bio record is dropped from the incoming bio rows, so QR
            // entries always win over a same-day bio import.
            $protectedKeys = Attendance::whereBetween('attendance_date', [
                    $periodStart->format('Y-m-d'),
                    $periodEnd->format('Y-m-d'),
                ])
                ->where('source', '!=', 'bio')
                ->get(['employee_name', 'attendance_date'])
                ->map(fn($r) => strtolower(trim($r->employee_name)) . '|' . Carbon::parse($r->attendance_date)->format('Y-m-d'))
                ->flip();

            $protectedCount = 0;
            $allRows = array_values(array_filter($allRows, function ($row) use ($protectedKeys, &$protectedCount) {
                $key = strtolower(trim($row['employee_name'])) . '|' . $row['attendance_date'];
                if (isset($protectedKeys[$key])) {
                    $protectedCount++;
                    return false;
                }
                return true;
            }));

            // Full replace, scoped to bio data only: clear out whatever was
            // previously imported from the bio scanner for this exact
            // period before inserting the new file. QR records in this
            // period are left untouched.
            $replaced = Attendance::whereBetween('attendance_date', [
                $periodStart->format('Y-m-d'),
                $periodEnd->format('Y-m-d'),
            ])->where('source', 'bio')->delete();

            $imported = 0;
            foreach (array_chunk($allRows, 200) as $chunk) {
                Attendance::upsert(
                    $chunk,
                    ['employee_name', 'attendance_date'],           // unique key to match on
                    ['category', 'status', 'remarks', 'source']       // columns to update if match found
                );
                $imported += count($chunk);
            }

            // Record when this period was last imported. Used by the
            // Salary page to know if a generated payroll is stale.
            DB::table('attendance_imports')->updateOrInsert(
                [
                    'period_start' => $periodStart->format('Y-m-d'),
                    'period_end' => $periodEnd->format('Y-m-d'),
                ],
                ['imported_at' => now()]
            );

            // LOG: bio file imported. This replaces all bio entries in the
            // period, so the number replaced is recorded. One line for the
            // whole file.
            $uniqueUnmatched = array_values(array_unique($unmatchedNames));

            // Anomaly: may payroll na para sa period na ito, kaya ang import ay
            // nagpapalit ng datos na pinagbatayan ng sweldo. Ang pagsuri ay hindi
            // dapat makasira sa import, kaya naka-try/catch.
            $afterPayroll = false;
            try {
                $afterPayroll = Payroll::whereDate('payroll_period_start', $periodStart->format('Y-m-d'))
                    ->whereDate('payroll_period_end', $periodEnd->format('Y-m-d'))
                    ->exists();
            } catch (\Throwable $e) {
                $afterPayroll = false;
            }

            $this->audit(
                'attendance_imported',
                "Bio file imported ({$periodStart->format('M j')}–{$periodEnd->format('M j, Y')}): "
                    . "{$imported} new record(s), {$replaced} bio record(s) replaced"
                    . ($afterPayroll ? ' — payroll already generated for this period' : ''),
                $afterPayroll ? 'warning' : 'info',
                [
                    'period_start'    => $periodStart->format('Y-m-d'),
                    'period_end'      => $periodEnd->format('Y-m-d'),
                    'file_name'       => mb_substr((string) $file->getClientOriginalName(), 0, 120),
                    'imported'        => $imported,
                    'replaced_bio'    => (int) $replaced,
                    'qr_preserved'    => $protectedCount,
                    'unmatched_names' => count($uniqueUnmatched),
                    'payroll_already_generated' => $afterPayroll,
                ],
                $afterPayroll,
                'attendance'
            );

            $successMessage = "Imported {$imported} attendance record(s) into Days {$periodStart->day}-{$periodEnd->day} ({$periodStart->format('M j')}–{$periodEnd->format('M j, Y')}) successfully!";

            if ($protectedCount > 0) {
                $successMessage .= " {$protectedCount} QR entr" . ($protectedCount === 1 ? 'y was' : 'ies were') . " not overwritten (preserved because QR takes priority over the bio file for that day).";
            }

            if (!empty($unmatchedNames)) {
                $uniqueUnmatched = array_unique($unmatchedNames);
                $successMessage .= ' ' . count($uniqueUnmatched)
                    . ' name(s) not in the Employees list were not imported: ' . implode(', ', $uniqueUnmatched)
                    . '. Add them to Employees first, then import the file again.';
            }

            return redirect()->route('attendance', ['period' => $periodStart->format('Y-m-d')])->with('success', $successMessage);

        } catch (\Throwable $e) {
            // LOG: the import failed
            $this->audit(
                'attendance_import_failed',
                'Bio file import failed',
                'warning',
                ['error' => mb_substr($e->getMessage(), 0, 200)],
                false,
                'attendance'
            );

            return redirect()->route('attendance')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ─── CLEAR ALL ATTENDANCE (for testing / fresh start) ──────
    // Deletes ALL attendance records — every period, every source — plus
    // the import history (attendance_imports).
    // Payroll records are deliberately NOT included; previously generated
    // payslips remain in Payroll History.
    public function clearAll(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // For the log: count BEFORE deleting
        $total  = Attendance::count();
        $qrRows = Attendance::where('source', 'qr')->count();

        Attendance::query()->delete();
        DB::table('attendance_imports')->delete();

        // LOG: ALL attendance deleted. Critical anomaly, because pay is
        // computed from this data.
        $this->audit(
            'attendance_cleared',
            "Deleted ALL attendance records ({$total} record(s))",
            'critical',
            [
                'old' => ['records' => $total, 'qr_records' => $qrRows],
            ],
            true,
            'attendance'
        );

        return redirect()->route('attendance')->with('success', 'All attendance records have been cleared.');
    }

    private function buildWideAttendanceRows(
        array $rowData,
        Carbon $periodStart,
        int $periodDays = 15,
        ?\Illuminate\Support\Collection $knownNames = null,
        array &$unmatchedNames = []
    ): array {
        // Accept the common header names for the employee name column.
        $name = null;
        foreach (['name', 'name of employee', 'employee name', 'employee', 'full name'] as $key) {
            if (isset($rowData[$key]) && trim((string) $rowData[$key]) !== '') {
                $name = trim((string) $rowData[$key]);
                break;
            }
        }
        if ($name === null) return [];

        // Skip title/footer/legend rows ("Hours per full day", "Legend", "P", "-", ...).
        // Real employee rows have a numeric NO. Files without a NO column are still accepted.
        if (array_key_exists('no', $rowData)) {
            $no = trim((string) $rowData['no']);
            if ($no === '' || !is_numeric($no)) return [];
        }

        // Use the canonical, Employees-table casing of this name for
        // everything we save, so bio and QR rows group together.
        $canonicalName = $name;

        if ($knownNames !== null) {
            // Collapse repeated spaces so "Juan  Dela Cruz" still matches.
            $key = strtolower(preg_replace('/\s+/', ' ', $name));

            if (!$knownNames->has($key)) {
                $unmatchedNames[] = $name;
                return [];
            }

            $canonicalName = $knownNames->get($key);
        }

        $category = trim((string) ($rowData['category'] ?? '')) ?: 'Staff';
        $rows = [];

        for ($day = 1; $day <= $periodDays; $day++) {
            // Supports "day1", "day 1", "d1" or plain "1" headers.
            $val = $rowData["day{$day}"]
                ?? $rowData["day {$day}"]
                ?? $rowData["d{$day}"]
                ?? $rowData[(string) $day]
                ?? '';
            $val = trim((string) $val);

            // Normalize look-alike dash characters that Excel/Word may produce
            // (en dash, em dash, unicode minus) into a plain "-".
            $val = str_replace(["\u{2212}", "\u{2013}", "\u{2014}"], '-', $val);

            // "p" / "P " -> "P"
            if (strtoupper($val) === 'P') $val = 'P';

            if ($val === '') continue;

            if ($val === 'P' || (str_starts_with($val, '+') && is_numeric(substr($val, 1)))) {
                // "P" = present, "+1", "+2" = present with overtime.
                $status = 'present';
            } elseif ($val === '-') {
                // A bare dash with no number = genuinely absent that day.
                $status = 'absent';
            } elseif (str_starts_with($val, '-') && is_numeric(substr($val, 1))) {
                // "-5", "-30" = late / undertime in minutes; still present.
                $status = 'present';
            } elseif (preg_match('/^\d+(\.\d+)?h$/i', $val)) {
                // "6h", "7h" = short day (hours actually worked); still present.
                $status = 'present';
            } else {
                continue;
            }

            $rows[] = [
                'employee_name'   => $canonicalName,
                'attendance_date' => $periodStart->copy()->addDays($day - 1)->format('Y-m-d'),
                'category'        => $category,
                'status'          => $status,
                'remarks'         => $val,
                'source'          => 'bio',
            ];
        }

        return $rows;
    }
}