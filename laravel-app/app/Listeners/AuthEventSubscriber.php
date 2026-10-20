<?php

namespace App\Listeners;

use App\Services\SystemLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

class AuthEventSubscriber
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        $ip = request()->ip();

        // I-check bago mag-log para hindi madamay ang kasalukuyang login sa history
        $newIp = SystemLogger::isNewIpForUser($user->getAuthIdentifier(), $ip);
        $offHours = SystemLogger::isOffHours();

        SystemLogger::record(
            'login_success',
           "Successful login for {$user->email}.",
            'info',
            [],
            false,
            $user->getAuthIdentifier(),
            $user->email
        );

        if ($newIp) {
            SystemLogger::anomaly(
                'login_new_ip',
                "Login by {$user->email} from a new IP ({$ip}).",
                'warning',
                ['ip' => $ip],
                $user->getAuthIdentifier(),
                $user->email
            );
        }

        if ($offHours) {
            SystemLogger::anomaly(
                'login_off_hours',
                "Login by {$user->email} outside normal hours (" . now()->format('h:i A') . ').',
                'warning',
                [],
                $user->getAuthIdentifier(),
                $user->email
            );
        }
    }

    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        SystemLogger::record(
            'login_failed',
           'Failed login attempt' . ($email ? " for {$email}." : '.'),
            'warning',
            [],
            false,
            $event->user?->getAuthIdentifier(),
            $email
        );

        SystemLogger::checkBruteForce(request()->ip(), $email);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            SystemLogger::record(
                'logout',
             "{$event->user->email} logged out.",
                'info',
                [],
                false,
                $event->user->getAuthIdentifier(),
                $event->user->email
            );
        }
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        SystemLogger::anomaly(
            'password_reset',
            "Password reset for {$event->user->email}.",
            'warning',
            [],
            $event->user->getAuthIdentifier(),
            $event->user->email
        );
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
            Logout::class => 'handleLogout',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
