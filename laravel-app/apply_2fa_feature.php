<?php
/**
 * apply_2fa_feature.php
 * Run from your laravel-app root: php apply_2fa_feature.php
 *
 * Adds email-based Two-Factor Authentication:
 *  - Migration: two_factor_enabled / two_factor_code / two_factor_expires_at on users
 *  - AuthController: login() branches into a 2FA code flow when enabled
 *  - New routes: /two-factor (show/verify), /two-factor/resend
 *  - New view: resources/views/auth/two-factor.blade.php
 *  - DashboardController: toggleTwoFactor() + route to flip it on/off
 *  - Account page: functional toggle instead of "Coming soon"
 *  - User model: boolean/datetime casts for the new columns
 *
 * NOTE: OAuth logins (Google/Facebook) currently bypass this 2FA check —
 * they set the session directly in their own callback controllers.
 */

$root = __DIR__;

function writeNewFile($path, $content, $label) {
    if (file_exists($path)) {
        echo "[SKIP] $label — file already exists, hindi ko na papalitan: $path\n";
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

// ---------- 1. Migration (new file) ----------
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
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('status');
            }
            if (!Schema::hasColumn('users', 'two_factor_code')) {
                $table->string('two_factor_code', 10)->nullable()->after('two_factor_enabled');
            }
            if (!Schema::hasColumn('users', 'two_factor_expires_at')) {
                $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_enabled', 'two_factor_code', 'two_factor_expires_at']);
        });
    }
};
PHP;

writeNewFile(
    $root . '/database/migrations/2026_09_19_090000_add_two_factor_fields_to_users_table.php',
    $migration,
    'Migration (two_factor fields)'
);

// ---------- 2. New view: auth/two-factor.blade.php ----------
$twoFactorView = <<<'BLADE'
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your identity | WonderPark Amusement Com Inc.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body>

    <div class="auth-split">

        <div class="auth-visual">
            <div class="bg-slide" data-bg="{{ asset('images/vikings.jpg') }}"></div>
            <div class="bg-slide" data-bg="{{ asset('images/roller-fever.jpg') }}"></div>
            <div class="auth-visual-content">
                <a href="{{ route('home') }}" class="logo">WonderPark<span class="dot">•</span>Amusement</a>
                <div class="auth-visual-copy">
                    <span class="ticket-label on-dark">Security check</span>
                    <h2>One quick step before you're in.</h2>
                    <p>We've emailed a 6-digit code to keep your account safe. Enter it here to finish signing in.</p>
                </div>
            </div>
        </div>

        <div class="auth-form-side" style="position:relative;">
            <a href="{{ url('/login') }}" class="auth-back-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back to login
            </a>
            <div class="auth-card">
                <span class="ticket-label coral">Two-Factor Authentication</span>
                <h1>Enter your code</h1>
                <p class="auth-sub">We sent a 6-digit verification code to your registered email. It expires in 10 minutes.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif

                @if (session('status'))
                    <div class="auth-error" style="background:#e6f7f1;color:#0c8b80;border-color:#0c8b8033;">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ url('/two-factor') }}">
                    @csrf

                    <div class="field">
                        <label for="code">Verification code</label>
                        <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                            autocomplete="one-time-code" required autofocus
                            style="letter-spacing:6px;font-family:'Space Mono',monospace;font-size:20px;text-align:center;">
                    </div>

                    <button type="submit" class="btn btn-primary">Verify &amp; Continue</button>
                </form>

                <form method="POST" action="{{ url('/two-factor/resend') }}" style="margin-top:14px;">
                    @csrf
                    <button type="submit" class="btn-social" style="width:100%;justify-content:center;background:none;border:1px solid var(--line,#eee);cursor:pointer;">
                        Resend code
                    </button>
                </form>

                <p class="auth-signup">Wrong account? <a href="{{ url('/logout') }}">Log out</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.bg-slide[data-bg]').forEach(function (el) {
                el.style.backgroundImage = "url('" + el.dataset.bg + "')";
            });
        });
    </script>
</body>

</html>
BLADE;

writeNewFile(
    $root . '/resources/views/auth/two-factor.blade.php',
    $twoFactorView,
    'View (auth/two-factor.blade.php)'
);

// ---------- 3. Patch AuthController.php ----------
$authControllerPath = $root . '/app/Http/Controllers/AuthController.php';

$oldLoginBlock = <<<'PHP'
    if ($user && $user->status === 'active' && password_verify($password, $user->password)) {
    session()->regenerate();
    session([
        'user_id' => $user->user_id,
        'fullname' => $user->fullname,
        'email' => $user->email,
        'role' => $user->role,
    ]);

      if ($user->role === 'customer') {
        return redirect('/app/waiver');
    }

    if ($user->role === 'cashier') {
        return redirect('/home');
    }

    return redirect('/ml-forecast');
}
    return back()->with('error', 'Invalid username or password.');
}
PHP;

$newLoginBlock = <<<'PHP'
    if ($user && $user->status === 'active' && password_verify($password, $user->password)) {

    if (!empty($user->two_factor_enabled)) {
        $code = (string) random_int(100000, 999999);

        DB::table('users')->where('user_id', $user->user_id)->update([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your WonderPark verification code is: {$code}\n\nThis code expires in 10 minutes. If you didn't try to log in, you can ignore this email.",
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Your WonderPark verification code');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('2FA email failed: ' . $e->getMessage());
        }

        session([
            'twofa_pending_user_id' => $user->user_id,
            'twofa_redirect' => $user->role === 'customer' ? '/app/waiver' : ($user->role === 'cashier' ? '/home' : '/ml-forecast'),
        ]);

        return redirect('/two-factor');
    }

    session()->regenerate();
    session([
        'user_id' => $user->user_id,
        'fullname' => $user->fullname,
        'email' => $user->email,
        'role' => $user->role,
    ]);

      if ($user->role === 'customer') {
        return redirect('/app/waiver');
    }

    if ($user->role === 'cashier') {
        return redirect('/home');
    }

    return redirect('/ml-forecast');
}
    return back()->with('error', 'Invalid username or password.');
}

    public function showTwoFactorForm()
    {
        if (!session()->has('twofa_pending_user_id')) {
            return redirect('/login');
        }
        return view('auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $pendingId = session('twofa_pending_user_id');
        if (!$pendingId) {
            return redirect('/login');
        }

        $code = trim($request->input('code', ''));
        $user = DB::table('users')->where('user_id', $pendingId)->first();

        if (!$user) {
            session()->forget(['twofa_pending_user_id', 'twofa_redirect']);
            return redirect('/login')->with('error', 'Session expired. Please log in again.');
        }

        if (empty($user->two_factor_code) || $user->two_factor_code !== $code) {
            return back()->with('error', 'Incorrect code. Please try again.');
        }

        if (!$user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        DB::table('users')->where('user_id', $user->user_id)->update([
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ]);

        $redirect = session('twofa_redirect', '/app/waiver');
        session()->forget(['twofa_pending_user_id', 'twofa_redirect']);

        session()->regenerate();
        session([
            'user_id' => $user->user_id,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect($redirect);
    }

    public function resendTwoFactor()
    {
        $pendingId = session('twofa_pending_user_id');
        if (!$pendingId) {
            return redirect('/login');
        }

        $user = DB::table('users')->where('user_id', $pendingId)->first();
        if (!$user) {
            return redirect('/login');
        }

        $code = (string) random_int(100000, 999999);
        DB::table('users')->where('user_id', $user->user_id)->update([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your WonderPark verification code is: {$code}\n\nThis code expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Your WonderPark verification code');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('2FA resend email failed: ' . $e->getMessage());
        }

        return back()->with('status', 'A new code has been sent to your email.');
    }
PHP;

patchFile($authControllerPath, $oldLoginBlock, $newLoginBlock, 'AuthController.php (2FA login branch)');

// ---------- 4. Patch routes/web.php ----------
$routesPath = $root . '/routes/web.php';

$oldRoutes1 = "Route::get('/logout', [AuthController::class, 'logout'])->name('logout');\nRoute::post('/logout', [AuthController::class, 'logout']);\n";
$newRoutes1 = "Route::get('/logout', [AuthController::class, 'logout'])->name('logout');\nRoute::post('/logout', [AuthController::class, 'logout']);\n\n// Two-Factor Authentication (email code)\nRoute::get('/two-factor', [AuthController::class, 'showTwoFactorForm'])->name('two-factor.show');\nRoute::post('/two-factor', [AuthController::class, 'verifyTwoFactor'])->name('two-factor.verify');\nRoute::post('/two-factor/resend', [AuthController::class, 'resendTwoFactor'])->name('two-factor.resend');\n";

patchFile($routesPath, $oldRoutes1, $newRoutes1, 'routes/web.php (two-factor routes)');

$oldRoutes2 = "Route::post('/account', [UserDashboardController::class, 'update'])->name('account.update');\n";
$newRoutes2 = "Route::post('/account', [UserDashboardController::class, 'update'])->name('account.update');\n        Route::post('/account/two-factor', [UserDashboardController::class, 'toggleTwoFactor'])->name('account.two-factor.toggle');\n";

patchFile($routesPath, $oldRoutes2, $newRoutes2, 'routes/web.php (account two-factor toggle route)');

// ---------- 5. Patch DashboardController.php ----------
$dashboardControllerPath = $root . '/app/Http/Controllers/User/DashboardController.php';

$oldDashTail = <<<'PHP'
        return redirect()->route('user.dashboard')
            ->with('success', 'Profile updated: name is now "' . $user->name . '", avatar color is "' . $user->avatar_theme . '".');
    }
}
PHP;

$newDashTail = <<<'PHP'
        return redirect()->route('user.dashboard')
            ->with('success', 'Profile updated: name is now "' . $user->name . '", avatar color is "' . $user->avatar_theme . '".');
    }

    /**
     * Toggle email-based two-factor authentication on/off for the account.
     *
     * POST /app/account/two-factor
     */
    public function toggleTwoFactor(\Illuminate\Http\Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect('/login')->with('error', 'Your session has expired. Please log in again.');
        }

        $user->two_factor_enabled = !$user->two_factor_enabled;

        // Clear any stale pending code when toggling.
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        $status = $user->two_factor_enabled ? 'enabled' : 'disabled';

        return redirect()->route('user.dashboard')
            ->with('success', "Two-Factor Authentication has been {$status}.");
    }
}
PHP;

patchFile($dashboardControllerPath, $oldDashTail, $newDashTail, 'DashboardController.php (toggleTwoFactor)');

// ---------- 6. Patch User.php model ----------
$userModelPath = $root . '/app/Models/User.php';

$oldCasts = <<<'PHP'
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
PHP;

$newCasts = <<<'PHP'
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
        ];
    }
PHP;

patchFile($userModelPath, $oldCasts, $newCasts, 'User.php (two_factor casts)');

// ---------- 7. Patch dashboard.blade.php ----------
$dashboardBladePath = $root . '/resources/views/user/dashboard.blade.php';

$oldBladeBlock = <<<'BLADE'
                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:16px;">
                    <div>
                        <div style="font-size:12.5px;font-weight:700;color:var(--ink);">Two-Factor Authentication</div>
                        <div style="font-size:10.5px;color:var(--muted);">Extra layer of security for booking waivers</div>
                    </div>
                    <span class="rb-badge rb-badge-neutral">Coming soon</span>
                </div>
BLADE;

$newBladeBlock = <<<'BLADE'
                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:16px;">
                    <div>
                        <div style="font-size:12.5px;font-weight:700;color:var(--ink);">Two-Factor Authentication</div>
                        <div style="font-size:10.5px;color:var(--muted);">
                            @if ($user->two_factor_enabled ?? false)
                                Enabled — a code is emailed to you at every login.
                            @else
                                Get a one-time code by email each time you log in.
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('user.account.two-factor.toggle') }}">
                        @csrf
                        <button type="submit" class="rb-badge {{ ($user->two_factor_enabled ?? false) ? 'rb-badge-teal' : 'rb-badge-neutral' }}" style="border:none;cursor:pointer;">
                            {{ ($user->two_factor_enabled ?? false) ? 'Enabled — Turn off' : 'Turn on' }}
                        </button>
                    </form>
                </div>
BLADE;

patchFile($dashboardBladePath, $oldBladeBlock, $newBladeBlock, 'dashboard.blade.php (2FA toggle UI)');

// ---------- 8. Run the migration ----------
echo "\nRunning php artisan migrate...\n";
passthru('php artisan migrate --force');

echo "\nTapos na. Buod:\n";
echo "- Kapag na-on yung 2FA sa Account page, tuwing mag-lo-login (username/password), papadalhan ng 6-digit code sa email bago pumasok.\n";
echo "- Sa dev, kung MAIL_MAILER=log ang laman ng .env mo, makikita mo yung code sa storage/logs/laravel.log imbes na aktwal na email.\n";
echo "- PAALALA: hindi pa kasama dito yung Google/Facebook OAuth login — dumadaan pa rin sila nang diretso, hindi tinatawagan yung 2FA check. Sabihin mo lang kung gusto mo ring saklawin 'yon.\n";
