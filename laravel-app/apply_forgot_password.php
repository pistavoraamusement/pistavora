<?php
/**
 * apply_forgot_password.php
 * Run from your laravel-app root: php apply_forgot_password.php
 *
 * Adds a public "Forgot Password" recovery flow (no login needed):
 *  1. /forgot-password  - enter registered email, get a 6-digit code emailed
 *  2. /reset-password   - enter that code + a new password, done
 *
 * Reuses the password_change_code / password_change_expires_at columns
 * already added by apply_change_password.php — no new migration needed.
 * (Requires that script to have been run already.)
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

// ---------- 0. Sanity check: password_change_code column must already exist ----------
echo "Checking for password_change_code column (from the earlier Change Password feature)...\n";
passthru('php artisan tinker --execute="echo Illuminate\\Support\\Facades\\Schema::hasColumn(\'users\', \'password_change_code\') ? \'FOUND\' : \'MISSING\';"', $tinkerExit);
echo "\n";

// ---------- 1. Patch login.blade.php: add Forgot password? link + status message ----------
$loginPath = $root . '/resources/views/auth/login.blade.php';

$oldLink = <<<'BLADE'
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required
                            autocomplete="current-password">
                    </div>
BLADE;

$newLink = <<<'BLADE'
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required
                            autocomplete="current-password">
                        <a href="{{ url('/forgot-password') }}" style="display:inline-block;margin-top:6px;font-size:11.5px;color:var(--pink-deep,#B82850);font-weight:600;">Forgot password?</a>
                    </div>
BLADE;

patchFile($loginPath, $oldLink, $newLink, 'login.blade.php (Forgot password? link)');

$oldStatus = <<<'BLADE'
                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
BLADE;

$newStatus = <<<'BLADE'
                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
                @if (session('status'))
                    <div class="auth-error" style="background:#e6f7f1;color:#0c8b80;border-color:#0c8b8033;">{{ session('status') }}</div>
                @endif
BLADE;

patchFile($loginPath, $oldStatus, $newStatus, 'login.blade.php (status message display)');

// ---------- 2. New view: forgot-password.blade.php ----------
$forgotView = <<<'BLADE'
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot password | WonderPark Amusement Com Inc.</title>
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
                    <span class="ticket-label on-dark">Account recovery</span>
                    <h2>Forgot your password?</h2>
                    <p>No worries — enter your registered email and we'll send you a code to reset it.</p>
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
                <span class="ticket-label coral">Forgot Password</span>
                <h1>Reset your password</h1>
                <p class="auth-sub">Enter the email you registered with. We'll email you a 6-digit code.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="auth-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ url('/forgot-password') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Registered email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            autocomplete="email">
                    </div>

                    <button type="submit" class="btn btn-primary">Send reset code</button>
                </form>

                <p class="auth-signup">Remembered it? <a href="{{ url('/login') }}">Back to login</a></p>
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

writeNewFile($root . '/resources/views/auth/forgot-password.blade.php', $forgotView, 'View (auth/forgot-password.blade.php)');

// ---------- 3. New view: reset-password.blade.php ----------
$resetView = <<<'BLADE'
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password | WonderPark Amusement Com Inc.</title>
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
                    <span class="ticket-label on-dark">Account recovery</span>
                    <h2>Almost there.</h2>
                    <p>Enter the code we emailed you, then choose a new password.</p>
                </div>
            </div>
        </div>

        <div class="auth-form-side" style="position:relative;">
            <a href="{{ url('/forgot-password') }}" class="auth-back-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <div class="auth-card">
                <span class="ticket-label coral">Reset Password</span>
                <h1>Enter your code</h1>
                <p class="auth-sub">Check your email for the 6-digit code. It expires in 10 minutes.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
                @if (session('status'))
                    <div class="auth-error" style="background:#e6f7f1;color:#0c8b80;border-color:#0c8b8033;">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="auth-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ url('/reset-password') }}">
                    @csrf

                    <div class="field">
                        <label for="code">Verification code</label>
                        <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                            autocomplete="one-time-code" required autofocus
                            style="letter-spacing:6px;font-family:'Space Mono',monospace;font-size:20px;text-align:center;">
                    </div>

                    <div class="field">
                        <label for="new_password">New password</label>
                        <input id="new_password" type="password" name="new_password" minlength="8" required
                            autocomplete="new-password">
                    </div>

                    <div class="field">
                        <label for="new_password_confirmation">Confirm new password</label>
                        <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                            minlength="8" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">Reset password</button>
                </form>

                <form method="POST" action="{{ url('/reset-password/resend') }}" style="margin-top:14px;">
                    @csrf
                    <button type="submit" class="btn-social" style="width:100%;justify-content:center;background:none;border:1px solid var(--line,#eee);cursor:pointer;">
                        Resend code
                    </button>
                </form>

                <p class="auth-signup"><a href="{{ url('/login') }}">Cancel and go back to login</a></p>
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

writeNewFile($root . '/resources/views/auth/reset-password.blade.php', $resetView, 'View (auth/reset-password.blade.php)');

// ---------- 4. Patch AuthController.php ----------
$authControllerPath = $root . '/app/Http/Controllers/AuthController.php';

$oldTail = "return redirect('/app/waiver');\n}\n}";

$newTail = <<<'PHP'
return redirect('/app/waiver');
}

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = DB::table('users')->where('email', $request->email)->first();

        // Hindi natin sasabihin kung existing ba yung email o hindi (para sa security) —
        // palaging same generic message, pero padadalhan lang talaga kung meron.
        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('users')->where('user_id', $user->user_id)->update([
                'password_change_code'       => $code,
                'password_change_expires_at' => now()->addMinutes(10),
            ]);

            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Your WonderPark password reset code is: {$code}\n\nThis code expires in 10 minutes. If you didn't request this, you can safely ignore this email.",
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Reset your WonderPark password');
                    }
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Password reset email failed: ' . $e->getMessage());
            }

            session(['reset_pending_user_id' => $user->user_id]);
        }

        return redirect('/reset-password')
            ->with('status', 'If that email is registered, a reset code has been sent to it.');
    }

    public function showResetForm()
    {
        if (!session()->has('reset_pending_user_id')) {
            return redirect('/forgot-password');
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $pendingId = session('reset_pending_user_id');
        if (!$pendingId) {
            return redirect('/forgot-password');
        }

        $request->validate([
            'code' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::table('users')->where('user_id', $pendingId)->first();

        if (!$user) {
            session()->forget('reset_pending_user_id');
            return redirect('/forgot-password')->with('error', 'Something went wrong. Please try again.');
        }

        if (empty($user->password_change_code) || $user->password_change_code !== $request->code) {
            return back()->with('error', 'Incorrect code. Please try again.');
        }

        if (!$user->password_change_expires_at || now()->greaterThan($user->password_change_expires_at)) {
            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        DB::table('users')->where('user_id', $user->user_id)->update([
            'password'                   => password_hash($request->new_password, PASSWORD_DEFAULT),
            'password_change_code'       => null,
            'password_change_expires_at' => null,
        ]);

        session()->forget('reset_pending_user_id');

        return redirect('/login')->with('status', 'Your password has been reset. You can now log in.');
    }

    public function resendResetCode()
    {
        $pendingId = session('reset_pending_user_id');
        if (!$pendingId) {
            return redirect('/forgot-password');
        }

        $user = DB::table('users')->where('user_id', $pendingId)->first();
        if (!$user) {
            return redirect('/forgot-password');
        }

        $code = (string) random_int(100000, 999999);
        DB::table('users')->where('user_id', $user->user_id)->update([
            'password_change_code'       => $code,
            'password_change_expires_at' => now()->addMinutes(10),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your WonderPark password reset code is: {$code}\n\nThis code expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Reset your WonderPark password');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password reset resend email failed: ' . $e->getMessage());
        }

        return back()->with('status', 'A new code has been sent to your email.');
    }
}
PHP;

patchFile($authControllerPath, $oldTail, $newTail, 'AuthController.php (forgot/reset password methods)');

// ---------- 5. Patch routes/web.php ----------
$routesPath = $root . '/routes/web.php';

$oldRoutes = "Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');\n";
$newRoutes = "Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');\n\n// Forgot / Reset Password (email code)\nRoute::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password.show');\nRoute::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('forgot-password.send');\nRoute::get('/reset-password', [AuthController::class, 'showResetForm'])->name('reset-password.show');\nRoute::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.update');\nRoute::post('/reset-password/resend', [AuthController::class, 'resendResetCode'])->name('reset-password.resend');\n";

patchFile($routesPath, $oldRoutes, $newRoutes, 'routes/web.php (forgot/reset password routes)');

echo "\nTapos na. Buod:\n";
echo "1. Sa /login, may 'Forgot password?' link na sa ilalim ng Password field.\n";
echo "2. Pinipindot -> /forgot-password -> ilagay email -> code sa storage/logs/laravel.log.\n";
echo "3. /reset-password -> ilagay code + bagong password -> babalik sa /login na may success message.\n";
echo "\nPAALALA: kung hindi mo pa pinapatakbo yung apply_change_password.php dati, patakbuhin mo muna 'yun bago ito, kasi ginagamit dito yung mga column na dinagdag doon.\n";
