<?php

namespace App\Observers;

use App\Models\User;
use App\Services\SystemLogger;

class UserObserver
{
    public function updated(User $user): void
    {
        $roleColumn = config('system_log.role_column', 'role');

        // Pagbabago ng role / permission
        if ($user->isDirty($roleColumn)) {
            SystemLogger::anomaly(
                'role_changed',
                "Changed role of {$user->email}: '{$user->getOriginal($roleColumn)}' → '{$user->{$roleColumn}}'.",
                'critical',
                ['from' => $user->getOriginal($roleColumn), 'to' => $user->{$roleColumn}],
                $user->id,
                $user->email
            );
        }

        // Pagbabago ng password
        if ($user->isDirty('password')) {
            SystemLogger::anomaly(
                'password_changed',
                "Changed password of {$user->email}.",
                'warning',
                [],
                $user->id,
                $user->email
            );
        }

        // Pagbabago ng email
        if ($user->isDirty('email')) {
            SystemLogger::anomaly(
                'email_changed',
                "Changed email: '{$user->getOriginal('email')}' → '{$user->email}'.",
                'warning',
                [],
                $user->id,
                $user->email
            );
        }
    }

    public function deleted(User $user): void
    {
        SystemLogger::anomaly(
            'user_deleted',
            "Deleted user account of {$user->email}.",
            'critical',
            [],
            $user->id,
            $user->email
        );
    }
}
