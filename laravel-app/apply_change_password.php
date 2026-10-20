<?php
/**
 * apply_change_password.php
 * Run from your laravel-app root: php apply_change_password.php
 *
 * Adds a two-step "Change Password" flow to the Account page:
 *  1. User enters current password + new password -> we verify current
 *     password, store the new one (hashed) as PENDING, and email a 6-digit
 *     confirmation code.
 *  2. User enters that code -> only then does the pending hash actually
 *     become their real password.
 *
 * Files touched:
 *  - New migration: pending_password_hash / password_change_code / password_change_expires_at
 *  - DashboardController: 4 new methods (request/confirm/cancel/resend)
 *  - routes/web.php: 4 new POST routes
 *  - User.php: cast for password_change_expires_at
 *  - dashboard.blade.php: Password row becomes a functional two-step form
 */

$root = __DIR__;

function writeNewFile($path, $content, $label) {
    if (file_exists($path)) {
        echo "[SKIP] $label — file already exists: $path\n";
        return;
    }
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    file_put_contents($path, $content);
    echo "[DONE] $label — nagawa.\n";
}

function patchFile($path, $old, $new, $label) {
    if (!file_exists($path)) {
        echo "[SKIP] $label — file not found: $path\n";
        return;
    }
    $content = file_get_contents($path);
    if (strpos($content, $new) !== false) {
        echo "[OK]   $label — na-patch na dati, walang ginalaw.\n";
        return;
    }
    if (strpos($content, $old) === false) {
        echo "[WARN] $label — hindi na-match yung expected old content. I-check manually.\n";
        return;
    }
    $content = str_replace($old, $new, $content);
    file_put_contents($path, $content);
    echo "[DONE] $label — na-patch.\n";
}

// ---------- 1. Migration ----------
$migration = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pending_password_hash')) {
                $table->string('pending_password_hash')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'password_change_code')) {
                $table->string('password_change_code', 10)->nullable()->after('pending_password_hash');
            }
            if (!Schema::hasColumn('users', 'password_change_expires_at')) {
                $table->timestamp('password_change_expires_at')->nullable()->after('password_change_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pending_password_hash', 'password_change_code', 'password_change_expires_at']);
        });
    }
};
PHP;

writeNewFile(
    $root . '/database/migrations/2026_09_19_110000_add_password_change_fields_to_users_table.php',
    $migration,
    'Migration (password change fields)'
);

// ---------- 2. Patch DashboardController.php ----------
$dashboardControllerPath = $root . '/app/Http/Controllers/User/DashboardController.php';

$oldTail = <<<'PHP'
        return redirect()->route('user.dashboard')
            ->with('success', "Two-Factor Authentication has been {$status}.");
    }
}
PHP;

$newTail = <<<'PHP'
        return redirect()->route('user.dashboard')
            ->with('success', "Two-Factor Authentication has been {$status}.");
    }

    /**
     * Step 1: verify current password + validate new one, then email a code.
     *
     * POST /app/account/password
     */
    public function requestPasswordChange(\Illuminate\Http\Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect('/login')->with('error', 'Your session has expired. Please log in again.');
        }

        if (!password_verify($request->current_password, $user->password)) {
            return redirect()->route('user.dashboard')->with('error', 'Current password is incorrect.');
        }

        $code = (string) random_int(100000, 999999);

        $user->pending_password_hash = password_hash($request->new_password, PASSWORD_DEFAULT);
        $user->password_change_code = $code;
        $user->password_change_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your WonderPark password change code is: {$code}\n\nThis code expires in 10 minutes. If you didn't request this, you can safely ignore this email — your password will not be changed.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Confirm your WonderPark password change');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password change email failed: ' . $e->getMessage());
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'We emailed you a code to confirm your new password.');
    }

    /**
     * Step 2: verify the emailed code, then actually apply the new password.
     *
     * POST /app/account/password/confirm
     */
    public function confirmPasswordChange(\Illuminate\Http\Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect('/login')->with('error', 'Your session has expired. Please log in again.');
        }

        $code = trim($request->input('code', ''));

        if (empty($user->pending_password_hash) || empty($user->password_change_code)) {
            return redirect()->route('user.dashboard')->with('error', 'No pending password change found.');
        }

        if ($user->password_change_code !== $code) {
            return redirect()->route('user.dashboard')->with('error', 'Incorrect code. Please try again.');
        }

        if (!$user->password_change_expires_at || now()->greaterThan($user->password_change_expires_at)) {
            return redirect()->route('user.dashboard')->with('error', 'This code has expired. Please request a new one.');
        }

        $user->password = $user->pending_password_hash;
        $user->pending_password_hash = null;
        $user->password_change_code = null;
        $user->password_change_expires_at = null;
        $user->save();

        return redirect()->route('user.dashboard')->with('success', 'Your password has been updated.');
    }

    /**
     * Cancel a pending password change.
     *
     * POST /app/account/password/cancel
     */
    public function cancelPasswordChange()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));
        if ($user) {
            $user->pending_password_hash = null;
            $user->password_change_code = null;
            $user->password_change_expires_at = null;
            $user->save();
        }

        return redirect()->route('user.dashboard')->with('success', 'Password change cancelled.');
    }

    /**
     * Resend the password-change confirmation code.
     *
     * POST /app/account/password/resend
     */
    public function resendPasswordChangeCode()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        if (!$user || empty($user->pending_password_hash)) {
            return redirect()->route('user.dashboard')->with('error', 'No pending password change found.');
        }

        $code = (string) random_int(100000, 999999);
        $user->password_change_code = $code;
        $user->password_change_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your WonderPark password change code is: {$code}\n\nThis code expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Confirm your WonderPark password change');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password change resend email failed: ' . $e->getMessage());
        }

        return redirect()->route('user.dashboard')->with('success', 'A new code has been sent to your email.');
    }
}
PHP;

patchFile($dashboardControllerPath, $oldTail, $newTail, 'DashboardController.php (change-password methods)');

// ---------- 3. Patch routes/web.php ----------
$routesPath = $root . '/routes/web.php';

$oldRoutes = "Route::post('/account/two-factor', [UserDashboardController::class, 'toggleTwoFactor'])->name('account.two-factor.toggle');\n";
$newRoutes = "Route::post('/account/two-factor', [UserDashboardController::class, 'toggleTwoFactor'])->name('account.two-factor.toggle');\n        Route::post('/account/password', [UserDashboardController::class, 'requestPasswordChange'])->name('account.password.request');\n        Route::post('/account/password/confirm', [UserDashboardController::class, 'confirmPasswordChange'])->name('account.password.confirm');\n        Route::post('/account/password/cancel', [UserDashboardController::class, 'cancelPasswordChange'])->name('account.password.cancel');\n        Route::post('/account/password/resend', [UserDashboardController::class, 'resendPasswordChangeCode'])->name('account.password.resend');\n";

patchFile($routesPath, $oldRoutes, $newRoutes, 'routes/web.php (change-password routes)');

// ---------- 4. Patch User.php model ----------
$userModelPath = $root . '/app/Models/User.php';

$oldCasts = <<<'PHP'
            'google_linked' => 'boolean',
            'facebook_linked' => 'boolean',
        ];
    }
PHP;

$newCasts = <<<'PHP'
            'google_linked' => 'boolean',
            'facebook_linked' => 'boolean',
            'password_change_expires_at' => 'datetime',
        ];
    }
PHP;

patchFile($userModelPath, $oldCasts, $newCasts, 'User.php (password_change_expires_at cast)');

// ---------- 5. Patch dashboard.blade.php ----------
$dashboardBladePath = $root . '/resources/views/user/dashboard.blade.php';

$oldBlade = <<<'BLADE'
                <p class="u-field-label" style="margin-top:16px;">Password</p>
                <div class="ap-static-field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--muted);flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span style="flex:1;letter-spacing:2px;color:var(--muted);">••••••••••••</span>
                    <span class="rb-badge rb-badge-neutral">Coming soon</span>
                </div>
BLADE;

$newBlade = <<<'BLADE'
                <p class="u-field-label" style="margin-top:16px;">Password</p>
                <div class="ap-static-field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--muted);flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span style="flex:1;letter-spacing:2px;color:var(--muted);">••••••••••••</span>
                    @if (!empty($user->pending_password_hash))
                        <span class="rb-badge" style="background:var(--amber-pale);color:var(--amber-deep);">Code sent</span>
                    @endif
                </div>

                @if (!empty($user->pending_password_hash))
                    {{-- Step 2: enter the code emailed to confirm the change --}}
                    <div style="margin-top:10px;padding:12px;border:1px solid var(--line);border-radius:12px;background:var(--bg);">
                        <p style="margin:0 0 8px;font-size:11px;color:var(--muted);">
                            We emailed a 6-digit code to confirm your new password. It expires 10 minutes after it was sent.
                        </p>
                        <form method="POST" action="{{ route('user.account.password.confirm') }}" style="display:flex;gap:8px;flex-wrap:wrap;">
                            @csrf
                            <input type="text" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]*" placeholder="6-digit code" required
                                style="flex:1;min-width:120px;padding:8px 10px;border:1px solid var(--line);border-radius:8px;letter-spacing:3px;font-family:'Space Mono',monospace;">
                            <button type="submit" class="rb-badge rb-badge-teal" style="border:none;cursor:pointer;">Confirm change</button>
                        </form>
                        <div style="display:flex;gap:14px;margin-top:8px;">
                            <form method="POST" action="{{ route('user.account.password.resend') }}">
                                @csrf
                                <button type="submit" style="background:none;border:none;padding:0;font-size:10.5px;color:var(--pink-deep);font-weight:700;cursor:pointer;">Resend code</button>
                            </form>
                            <form method="POST" action="{{ route('user.account.password.cancel') }}">
                                @csrf
                                <button type="submit" style="background:none;border:none;padding:0;font-size:10.5px;color:var(--muted);font-weight:700;cursor:pointer;">Cancel</button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Step 1: request the change --}}
                    <details style="margin-top:10px;">
                        <summary style="cursor:pointer;font-size:11.5px;font-weight:700;color:var(--pink-deep);">Change password</summary>
                        <form method="POST" action="{{ route('user.account.password.request') }}" style="margin-top:10px;display:flex;flex-direction:column;gap:8px;">
                            @csrf
                            <input type="password" name="current_password" placeholder="Current password" required
                                style="padding:9px 10px;border:1px solid var(--line);border-radius:8px;">
                            <input type="password" name="new_password" placeholder="New password (min. 8 characters)" required minlength="8"
                                style="padding:9px 10px;border:1px solid var(--line);border-radius:8px;">
                            <input type="password" name="new_password_confirmation" placeholder="Confirm new password" required minlength="8"
                                style="padding:9px 10px;border:1px solid var(--line);border-radius:8px;">
                            <button type="submit" class="rb-badge rb-badge-neutral" style="border:none;cursor:pointer;align-self:flex-start;">Send verification code</button>
                        </form>
                    </details>
                @endif
BLADE;

patchFile($dashboardBladePath, $oldBlade, $newBlade, 'dashboard.blade.php (Change Password UI)');

// ---------- 6. Run migration ----------
echo "\nRunning php artisan migrate...\n";
passthru('php artisan migrate --force');

echo "\nTapos na. Buod:\n";
echo "1. Sa Account page, i-click 'Change password', punan yung 3 fields, 'Send verification code'.\n";
echo "2. Hanapin yung code sa storage/logs/laravel.log (kung MAIL_MAILER=log pa).\n";
echo "3. I-type sa 'Confirm change' — saka lang aktwal na mapapalitan yung password.\n";
