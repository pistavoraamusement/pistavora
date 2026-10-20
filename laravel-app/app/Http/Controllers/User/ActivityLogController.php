<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * GET /app/activity — the signed-in user's own activity log.
     * Query string: ?type=  &q=  &from=YYYY-MM-DD  &to=YYYY-MM-DD
     */
    public function index(Request $request)
    {
        $userId = (int) session('user_id');

        // ── Read + sanitize filters ───────────────────────────
        $group = (string) $request->query('type', '');

        $prefixMap = [
            'auth'     => ['auth', 'login', 'logout', '2fa', 'two_factor'],
            'booking'  => ['booking', 'reservation', 'payment'],
            'waiver'   => ['waiver'],
            'account'  => ['account', 'profile', 'password'],
            'security' => ['security', 'anomaly'],
        ];

        if (!array_key_exists($group, $prefixMap)) {
            $group = '';
        }

        $q = trim((string) $request->query('q', ''));

        $isDate = fn ($v) => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)
            && strtotime($v) !== false;

        $from = (string) $request->query('from', '');
        $to   = (string) $request->query('to', '');
        if (!$isDate($from)) { $from = ''; }
        if (!$isDate($to))   { $to   = ''; }
        if ($from !== '' && $to !== '' && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        // ── Build query ───────────────────────────────────────
        $query = DB::table('system_logs')
            ->where('user_id', $userId);

        // Type tabs: event_type is "booking.created", "booking_created" or just "booking"
        if ($group !== '') {
            $query->where(function ($w) use ($prefixMap, $group) {
                foreach ($prefixMap[$group] as $p) {
                    $w->orWhere('event_type', $p)
                      ->orWhere('event_type', 'like', $p . '.%')
                      ->orWhere('event_type', 'like', $p . '\\_%');
                }
            });
        }

        // Search: description, IP address, event type (LIKE wildcards escaped)
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $query->where(function ($w) use ($like) {
                $w->where('description', 'like', $like)
                  ->orWhere('ip_address', 'like', $like)
                  ->orWhere('event_type', 'like', $like);
            });
        }

        // Date range (inclusive)
        if ($from !== '') {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $user = User::find($userId);

        return view('user.activity', compact('logs', 'group', 'user'));
    }
}