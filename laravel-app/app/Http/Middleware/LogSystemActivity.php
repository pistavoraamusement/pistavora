<?php

namespace App\Http\Middleware;

use App\Models\SystemLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records user activity without touching every controller.
 *
 * Registered once as a global web middleware (bootstrap/app.php).
 * It only looks at the request + the outcome; it never reads request bodies,
 * so passwords and codes are never logged.
 */
class LogSystemActivity
{
    /** route name => [action, description] — logged on a successful POST */
    private const USER_ROUTES = [
        'user.booking.store'             => ['booking.created', 'Created a booking'],
        'user.booking.submit'            => ['booking.created', 'Created a booking'],
        'user.bookings.payment'          => ['booking.payment_submitted', 'Submitted payment proof'],
        'user.bookings.cancel'           => ['booking.cancelled', 'Cancelled a booking'],
        'user.bookings.reschedule'       => ['booking.rescheduled', 'Rescheduled a booking'],
        'user.waiver.store'              => ['waiver.accepted', 'Accepted the waiver'],
        'user.bookings.waiver.store'     => ['waiver.accepted', 'Accepted the waiver'],
        'user.account.update'            => ['account.profile_updated', 'Updated profile'],
        'user.account.two-factor.toggle' => ['security.two_factor_changed', 'Changed two-factor setting'],
        'user.account.password.request'  => ['security.password_change_requested', 'Requested a password change'],
        'user.account.password.confirm'  => ['security.password_changed', 'Changed password'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Logout flushes the session, so remember who was signed in first.
        $userBefore = session('user_id');
        $roleBefore = session('role');

        $response = $next($request);

        try {
            $this->log($request, $response, $userBefore, $roleBefore);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('LogSystemActivity failed: ' . $e->getMessage());
        }

        return $response;
    }

    private function log(Request $request, Response $response, $userBefore, $roleBefore): void
    {
        if ($response->getStatusCode() >= 400) {
            return;
        }

        $isPost  = $request->isMethod('post');
        $userNow = session('user_id');
        $failed  = session()->has('errors') || session()->has('error');
        $newSignIn = ! $userBefore && $userNow;

        // ---- logout (GET or POST) ----
        if ($request->is('logout')) {
            if ($userBefore) {
                SystemLog::record('auth.logout', 'Logged out', (int) $userBefore, null, null, $roleBefore);
            }
            return;
        }

        // ---- social login callbacks: /auth/google/callback, /auth/facebook/callback ----
        if ($request->isMethod('get') && $request->is('auth/*/callback')) {
            if ($newSignIn) {
                SystemLog::record('auth.login', 'Logged in with ' . ucfirst((string) $request->segment(2)), (int) $userNow);
            }
            return;
        }

        if (! $isPost) {
            return;
        }

        // ---- password login ----
        if ($request->is('login')) {
            if ($newSignIn) {
                SystemLog::record('auth.login', 'Logged in', (int) $userNow);
            } elseif (session('twofa_pending_user_id')) {
                SystemLog::record('auth.two_factor_sent', 'Password accepted, verification code sent', (int) session('twofa_pending_user_id'));
            } elseif ($failed) {
                $username = mb_substr(trim((string) $request->input('username', '')), 0, 60);
                SystemLog::record('auth.login_failed', "Failed sign-in attempt for username '{$username}'", null);
            }
            return;
        }

        // ---- two-factor verification ----
        if ($request->is('two-factor')) {
            if ($newSignIn) {
                SystemLog::record('auth.login', 'Logged in (two-factor verified)', (int) $userNow);
            } elseif ($failed) {
                SystemLog::record('auth.two_factor_failed', 'Failed two-factor verification', (int) session('twofa_pending_user_id') ?: null);
            }
            return;
        }

        // ---- registration ----
        if ($request->is('register')) {
            if ($newSignIn) {
                SystemLog::record('auth.registered', 'Created an account', (int) $userNow);
            }
            return;
        }

        // ---- forgotten password reset ----
        if ($request->is('reset-password') && ! $failed && $response->isRedirection()) {
            SystemLog::record('security.password_reset', 'Reset password with an email code', null);
            return;
        }

        // ---- signed-in user actions, by route name ----
        $route = $request->route();
        $name  = $route ? $route->getName() : null;

        if ($name && isset(self::USER_ROUTES[$name]) && ! $failed) {
            [$action, $text] = self::USER_ROUTES[$name];

            $subjectId = null;
            $param = $route->parameter('booking');
            if (is_object($param) && isset($param->id)) {
                $param = $param->id;
            }
            if (is_numeric($param)) {
                $subjectId = (int) $param;
            } elseif ($response->isRedirection()
                && preg_match('#/bookings/(\d+)#', (string) $response->headers->get('Location'), $m)) {
                $subjectId = (int) $m[1];
            } elseif ($name === 'user.booking.submit') {
                $data = json_decode((string) $response->getContent(), true);
                $subjectId = isset($data['booking']['id']) ? (int) $data['booking']['id'] : null;
            }

            $isBooking = str_starts_with($action, 'booking.');
            if ($subjectId && $isBooking) {
                $text .= ' #' . $subjectId;
            }

            SystemLog::record($action, $text, null, $isBooking ? 'booking' : null, $isBooking ? $subjectId : null);

            if ($name === 'user.booking.submit') {
                SystemLog::record('booking.payment_submitted', 'Submitted payment proof' . ($subjectId ? ' #' . $subjectId : ''), null, 'booking', $subjectId);
            }
        }
    }
}
