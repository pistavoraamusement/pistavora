<?php

namespace App\Http\Middleware;

use App\Services\SystemLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsLoggedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');

        if (!$userId) {
            return $this->unauthenticated($request);
        }

        // Checked against the database on every request so that deactivation,
        // deletion and role changes take effect immediately (no waiting for logout).
        $user = DB::table('users')
            ->where('user_id', $userId)
            ->first(['user_id', 'username', 'fullname', 'email', 'role', 'status']);

        if (!$user || $user->status !== 'active') {
            try {
                SystemLogger::record(
                    'session_revoked',
                    $user
                        ? "Session revoked for {$user->username}: account is no longer active"
                        : 'Session revoked: account no longer exists in the database',
                    'warning',
                    ['stale_user_id' => (int) $userId, 'status' => $user->status ?? 'deleted'],
                    false,
                    $user ? (int) $user->user_id : null,
                    $user->email ?? null,
                    'auth'
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('SystemLogger failed (session_revoked): ' . $e->getMessage());
            }

            session()->flush();

            return $this->unauthenticated($request, 'Your account is no longer active. Please contact an administrator.');
        }

        // Sync the role into the session if an admin changed it.
        if (session('role') !== $user->role) {
            session(['role' => $user->role]);
        }

        // Used by EnsureUserHasRole so it does not need to query again.
        $request->attributes->set('auth_user', $user);

        return $next($request);
    }

    private function unauthenticated(Request $request, string $message = 'Please log in to continue.'): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Not authenticated.'], 401);
        }

        return redirect('/login')->with('error', $message);
    }
}