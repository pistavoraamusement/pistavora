<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware(['auth.session', 'role:manager,admin'])
 * 'auth.session' must come FIRST (it loads the user from the database).
 * A wrong role returns 403. Logging is handled by DetectUnusualActivity
 * (event: unauthorized_access), so nothing is logged here to avoid duplicates.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->attributes->get('auth_user');

        // Fail closed: if there is no user (auth.session was forgotten), deny access.
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Not authenticated.'], 401)
                : redirect('/login')->with('error', 'Please log in to continue.');
        }

        if (!in_array($user->role, $roles, true)) {
            // Read by DetectUnusualActivity for logging.
            $request->attributes->set('access_denied', [
                'user_id'  => (int) $user->user_id,
                'role'     => $user->role,
                'required' => $roles,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to do that.'], 403);
            }

            abort(403, 'You do not have permission to open this page.');
        }

        return $next($request);
    }
}