<?php

namespace App\Http\Middleware;

use App\Models\SystemLog;
use App\Services\SystemLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Logs an 'unauthorized_access' event when a request is denied
 * (EnsureUserHasRole sets 'access_denied' on the request attributes).
 *
 * Severity:
 *  - warning  : normal denied request
 *  - critical : repeated denied requests from the same IP within a short window
 *               (see CRITICAL_THRESHOLD / WINDOW_MINUTES). Also flagged as anomaly.
 *
 * This middleware must sit OUTSIDE 'auth.session' and 'role:...'
 * (i.e. run earlier in the pipeline) so it can read the attributes afterwards.
 * Example: append it to the 'web' group in bootstrap/app.php.
 */
class DetectUnusualActivity
{
    /** Ilang denied requests (kasama ang kasalukuyan) bago maging critical. */
    private const CRITICAL_THRESHOLD = 5;

    /** Sa loob ng ilang minuto binibilang ang mga denied requests. */
    private const WINDOW_MINUTES = 10;

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (HttpExceptionInterface $e) {
            // abort(403) in EnsureUserHasRole throws, so it is caught here.
            $this->logIfDenied($request);

            throw $e;
        }

        // JSON 403 responses do not throw.
        $this->logIfDenied($request);

        return $response;
    }

    private function logIfDenied(Request $request): void
    {
        $denied = $request->attributes->get('access_denied');

        if (!$denied) {
            return;
        }

        try {
            $user = $request->attributes->get('auth_user');
            $role = $denied['role'] ?? null;

            // Ilang beses nang na-deny ang IP na ito kamakailan (hindi pa kasama ang ngayon)
            $recent = SystemLog::query()
                ->where('event_type', 'unauthorized_access')
                ->where('ip_address', $request->ip())
                ->where('created_at', '>=', now()->subMinutes(self::WINDOW_MINUTES))
                ->count();

            $isRepeated = ($recent + 1) >= self::CRITICAL_THRESHOLD;
            $severity = $isRepeated ? 'critical' : 'warning';

            // May role sa description para lumabas sa User column ng System Logs
            $who = $role ? " ({$role})" : '';
            $description = "Unauthorized access{$who}: {$request->method()} /{$request->path()}";

            if ($isRepeated) {
                $description .= ' - repeated attempts (' . ($recent + 1) . ' in ' . self::WINDOW_MINUTES . ' min)';
            }

            SystemLogger::record(
                'unauthorized_access',
                $description,
                $severity,
                [
                    'user_id'  => $denied['user_id'] ?? null,
                    'role'     => $role,
                    'required' => $denied['required'] ?? [],
                    'method'   => $request->method(),
                    'path'     => '/' . ltrim($request->path(), '/'),
                    'ip'       => $request->ip(),
                    'attempts' => $recent + 1,
                ],
                $isRepeated,
                $denied['user_id'] ?? null,
                $user->email ?? null,
                'security'
            );
        } catch (\Throwable $e) {
            // A logging failure must never block the request.
            Log::error('SystemLogger failed (unauthorized_access): ' . $e->getMessage());
        }

        // Avoid double logging if called twice.
        $request->attributes->remove('access_denied');
    }
}