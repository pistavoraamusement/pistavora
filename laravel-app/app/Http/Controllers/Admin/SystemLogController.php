<?php
// Ilagay sa: app/Http/Controllers/Admin/SystemLogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Services\SystemLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemLogController extends Controller
{
    // Mga event na pwedeng i-review ng admin ("Yes, it's me" / "No, it's not me")
    private array $reviewable = ['login_failed', 'password_change_failed'];

    private function ensureAdmin(): void
    {
        abort_unless(
            in_array(session('role'), (array) config('system_log.admin_roles', ['admin']), true),
            403
        );
    }

    // Base query: itinatago ang "updated their own profile" (pangalan/photo lang ang binago)
    private function visible(): Builder
    {
        return SystemLog::query()->where(function ($q) {
            $q->where('event_type', '!=', 'account_updated')
              ->orWhere('description', 'not like', '% updated their own profile%');
        });
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $logs = $this->visible()
            ->with('user:user_id,username,fullname,email,role')
            ->when($request->filled('severity'), fn ($q) => $q->where('severity', $request->severity))
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->module))
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->event_type))
            ->when($request->boolean('anomaly_only'), fn ($q) => $q->where('is_anomaly', true))
            ->when($request->filled('search'), function ($q) use ($request) {
                // I-escape ang % at _ para literal ang hahanapin
                $term = addcslashes(trim($request->search), '%_\\');
                $s = '%' . $term . '%';

                $q->where(fn ($w) => $w->where('email', 'like', $s)
                    ->orWhere('ip_address', 'like', $s)
                    ->orWhere('description', 'like', $s));
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $since = now()->subDay();
        $summary = [
            'total'         => $this->visible()->where('created_at', '>=', $since)->count(),
            'anomalies'     => $this->visible()->where('created_at', '>=', $since)->where('is_anomaly', true)->count(),
            'critical'      => $this->visible()->where('created_at', '>=', $since)->where('severity', 'critical')->count(),
            'failed_logins' => $this->visible()->where('created_at', '>=', $since)->where('event_type', 'login_failed')->count(),
        ];

        $eventTypes = SystemLog::select('event_type')->distinct()->orderBy('event_type')->pluck('event_type');
        $modules = SystemLog::whereNotNull('module')->select('module')->distinct()->orderBy('module')->pluck('module');
        $reviewableEvents = $this->reviewable;

        // Username ng naka-login na admin (para malaman kung sariling account ang pinag-try)
        $currentUsername = is_numeric(session('user_id'))
            ? DB::table('users')->where('user_id', (int) session('user_id'))->value('username')
            : null;

        return view('admin.system-logs.index', compact('logs', 'summary', 'eventTypes', 'modules', 'reviewableEvents', 'currentUsername'));
    }

    // "Yes, it's me" / "No, it's not me" para sa failed login
    public function review(Request $request, int|string $id): JsonResponse
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'decision' => 'required|in:confirmed,suspicious',
        ]);

        $log = SystemLog::findOrFail($id);
        abort_unless(in_array($log->event_type, $this->reviewable, true), 422);

        $uid = is_numeric(session('user_id')) ? (int) session('user_id') : null;

        $log->review_status = $data['decision'];
        $log->reviewed_by   = $uid;
        $log->reviewed_at   = now();
        $log->save();

        // Kapag "not me", mag-log ng warning para makita ng buong team
        if ($data['decision'] === 'suspicious') {
            try {
                SystemLogger::record(
                    'suspicious_activity_reported',
                    "Admin reported log #{$log->id} ({$log->event_type}) as not them",
                    'warning',
                    ['log_id' => (int) $log->id, 'ip_address' => $log->ip_address],
                    true,
                    $uid,
                    session('email'),
                    'auth'
                );
            } catch (\Throwable $e) {
                Log::error('SystemLogger failed (review): ' . $e->getMessage());
            }
        }

        return response()->json(['ok' => true, 'status' => $log->review_status]);
    }
}