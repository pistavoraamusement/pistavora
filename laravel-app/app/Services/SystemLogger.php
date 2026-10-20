<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Cache;

class SystemLogger
{
    
    public static function record(
        string $eventType,
        string $description,
        string $severity = 'info',
        array $context = [],
        bool $isAnomaly = false,
        ?int $userId = null,
        ?string $email = null,
        ?string $module = null
    ): SystemLog {
        $request = app()->bound('request') ? request() : null;
        $user = $request?->user();

        return SystemLog::create([
            'event_type' => $eventType,
            'severity' => $severity,
            'module' => $module ?? self::moduleFromEvent($eventType) ?? self::detectModule($request),
            'user_id' => $userId ?? $user?->getAuthIdentifier(),
            'email' => $email ?? $user?->email,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 255) : null,
            'method' => $request?->method(),
            'url' => $request ? mb_substr($request->fullUrl(), 0, 500) : null,
            'description' => $description,
            'context' => $context ?: null,
            'is_anomaly' => $isAnomaly,
        ]);
    }

    /** Shortcut para sa event na kakaiba sa baseline. */
    public static function anomaly(
        string $eventType,
        string $description,
        string $severity = 'warning',
        array $context = [],
        ?int $userId = null,
        ?string $email = null,
        ?string $module = null
    ): SystemLog {
        return self::record($eventType, $description, $severity, $context, true, $userId, $email, $module);
    }

    /** Mga event na may sariling module kahit saang URL nangyari (auth at account events). */
    protected static function moduleFromEvent(string $eventType): ?string
    {
        return match (true) {
            str_starts_with($eventType, 'login'),
            str_starts_with($eventType, 'logout'),
            str_starts_with($eventType, 'brute_force'),
            str_starts_with($eventType, 'password') => 'auth',
            in_array($eventType, ['role_changed', 'email_changed', 'user_deleted'], true) => 'accounts',
            default => null,
        };
    }

    /** Hulaan ang module mula sa kasalukuyang request. */
    public static function detectModule($request = null): ?string
    {
        if (! $request) {
            return null;
        }

        $routeName = $request->route()?->getName();
        if ($routeName) {
            $parts = explode('.', $routeName);
            // Laktawan ang generic na prefix gaya ng "admin"
            $skip = (array) config('system_log.module_skip_prefixes', ['admin']);
            while (count($parts) > 1 && in_array($parts[0], $skip, true)) {
                array_shift($parts);
            }
            return $parts[0] ?: null;
        }

        $segments = $request->segments();
        $skip = (array) config('system_log.module_skip_prefixes', ['admin']);
        foreach ($segments as $segment) {
            if (! in_array($segment, $skip, true)) {
                return $segment;
            }
        }

        return null;
    }

    /** Baseline check: sobra-sobrang failed login mula sa isang IP. */
    public static function checkBruteForce(?string $ip, ?string $email = null): void
    {
        if (! $ip) {
            return;
        }

        $window = (int) config('system_log.failed_login_window_minutes', 10);
        $threshold = (int) config('system_log.failed_login_threshold', 5);

        $count = SystemLog::where('event_type', 'login_failed')
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subMinutes($window))
            ->count();

        // Isang alert lang kada window para hindi mag-spam
        if ($count >= $threshold && Cache::add("syslog:bruteforce:{$ip}", 1, now()->addMinutes($window))) {
            self::anomaly(
                'brute_force_suspected',
                "{$count} failed login attempts from IP {$ip} within {$window} minutes.",
                'critical',
                ['attempts' => $count, 'window_minutes' => $window],
                null,
                $email,
                'auth'
            );
        }
    }

    /** Baseline check: sunod-sunod na delete ng iisang user (anumang module). */
    public static function checkBulkDelete(?int $userId, string $module): void
    {
        if (! $userId) {
            return;
        }

        $window = (int) config('system_log.bulk_delete_window_minutes', 5);
        $threshold = (int) config('system_log.bulk_delete_threshold', 10);

        $count = SystemLog::where('user_id', $userId)
            ->where('event_type', 'like', '%_deleted')
            ->where('created_at', '>=', now()->subMinutes($window))
            ->count();

        if ($count >= $threshold && Cache::add("syslog:bulkdelete:{$userId}", 1, now()->addMinutes($window))) {
            self::anomaly(
                'bulk_delete',
                "{$count} records deleted by the same user within {$window} minutes (last module: {$module}).",
                'critical',
                ['deleted' => $count, 'window_minutes' => $window],
                $userId,
                null,
                $module
            );
        }
    }

    /** Baseline check: unang beses na IP para sa user na ito. */
    public static function isNewIpForUser(int|string $userId, ?string $ip): bool
    {
        if (! $ip) {
            return false;
        }

        $hasHistory = SystemLog::where('event_type', 'login_success')
            ->where('user_id', $userId)
            ->exists();

        if (! $hasHistory) {
            return false; // unang login ever, walang mapaghahambing
        }

        return ! SystemLog::where('event_type', 'login_success')
            ->where('user_id', $userId)
            ->where('ip_address', $ip)
            ->exists();
    }

    /** Baseline check: nasa labas ng normal na oras. */
    public static function isOffHours(): bool
    {
        $hour = (int) now()->format('G');
        $start = (int) config('system_log.off_hours_start', 0);
        $end = (int) config('system_log.off_hours_end', 5);

        return $hour >= $start && $hour < $end;
    }
}
