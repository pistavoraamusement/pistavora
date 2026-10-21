<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SystemLogger;

class AuthController extends Controller
{
    // ─── SYSTEM LOG HELPER ─────────────────────────────────
    // A single place that calls SystemLogger::record().
    // The app uses sessions (not Laravel Auth), so $userId and $email are
    // passed manually. When they are not passed, they are taken from the session.
    // Wrapped in try/catch: login/logout never break if logging fails.
    private function audit(
        string $eventType,
        string $description,
        string $severity = 'info',
        array $context = [],
        bool $isAnomaly = false,
        int|string|null $userId = null,
        ?string $email = null,
        string $module = 'auth'
    ): void {
        try {
            $sessionId = session('user_id');
            $uid = $userId ?? $sessionId;
            $uid = is_numeric($uid) ? (int) $uid : null;

            // Customer (website user) activity is NOT recorded: this log is for
            // staff / POS activity only. A failed login passes no user id, so
            // fall back to target_user_id from the context (the account that
            // was targeted, if one was found).
            $roleCheckId = $uid ?? ($context['target_user_id'] ?? null);
            if (is_numeric($roleCheckId)
                && DB::table('users')->where('user_id', (int) $roleCheckId)->value('role') === 'customer') {
                return;
            }

            SystemLogger::record(
                $eventType,
                $description,
                $severity,
                $context,
                $isAnomaly,
                $uid,
                $email ?? session('email'),
                $module
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SystemLogger failed (Auth): ' . $e->getMessage());
        }
    }

    // LOG: successful login (called after the session is set / after 2FA)
    // $user is a stdClass row from DB::table('users')->first()
    private function logLoginSuccess(object $user, bool $via2fa = false): void
    {
        try {
            $ip      = request()->ip();
            $isStaff = $user->role !== 'customer';

            // Important: check for a new IP BEFORE recording the current login
            $newIp    = SystemLogger::isNewIpForUser($user->user_id, $ip);
            $offHours = SystemLogger::isOffHours();
            $anomaly  = $isStaff && ($newIp || $offHours);

            $flags = [];
            if ($isStaff && $newIp)    { $flags[] = 'new IP'; }
            if ($isStaff && $offHours) { $flags[] = 'off-hours'; }

            $this->audit(
                'login_success',
                "{$user->fullname} ({$user->role}) logged in"
                    . ($via2fa ? ' with 2FA' : '')
                    . ($flags ? ' — ' . implode(', ', $flags) : ''),
                $anomaly ? 'warning' : 'info',
                [
                    'role'      => $user->role,
                    'via_2fa'   => $via2fa,
                    'new_ip'    => $newIp,
                    'off_hours' => $offHours,
                ],
                $anomaly,
                $user->user_id,
                $user->email
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SystemLogger failed (Auth login_success): ' . $e->getMessage());
        }
    }

    public function showLoginForm()
    {
        if (session()->has('user_id')) {
            return redirect('/home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $username = trim($request->input('username', ''));
        $password = $request->input('password', '');

        if ($username === '' || $password === '') {
            return back()->with('error', 'Please enter username and password.');
        }

        $user = DB::table('users')
                  ->where('username', $username)
                  ->first();

        $passwordOk = $user && password_verify($password, $user->password);

        if ($passwordOk && $user->status === 'active') {

            if (!empty($user->two_factor_enabled)) {
                $code = (string) random_int(100000, 999999);

                DB::table('users')->where('user_id', $user->user_id)->update([
                    'two_factor_code'       => $code,
                    'two_factor_expires_at' => now()->addMinutes(10),
                ]);

                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Your Pistavora verification code is: {$code}\n\nThis code expires in 10 minutes. If you didn't try to log in, you can ignore this email.",
                        function ($message) use ($user) {
                            $message->to($user->email)
                                    ->subject('Your Pistavora verification code');
                        }
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('2FA email failed: ' . $e->getMessage());
                }

                session([
                    'twofa_pending_user_id' => $user->user_id,
                    'twofa_redirect' => $user->role === 'customer'
                        ? route('user.waiver')
                        : ($user->role === 'cashier' ? '/home' : route('ml.forecast')),
                ]);

                // LOG: password is correct, waiting for the 2FA code
                $this->audit(
                    'login_2fa_pending',
                    "Password correct for {$user->fullname} ({$user->role}), waiting for the 2FA code",
                    'info',
                    ['role' => $user->role],
                    false,
                    $user->user_id,
                    $user->email
                );

                return redirect('/two-factor');
            }

            session()->regenerate();
            session([
                'user_id' => $user->user_id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            // LOG: successful login
            $this->logLoginSuccess($user);

            if ($user->role === 'customer') {
                return redirect()->route('user.waiver');
            }

            if ($user->role === 'cashier') {
                return redirect('/home');
            }

            return redirect()->route('ml.forecast');
        }

        if ($passwordOk) {
            // LOG: password is correct but the account is deactivated
            $this->audit(
                'login_blocked',
                "Deactivated account tried to log in: {$user->username} ({$user->role})",
                'warning',
                ['role' => $user->role, 'status' => $user->status],
                false,
                $user->user_id,
                $user->email
            );
        } else {
            // LOG: failed login (this is a guest, so there is no user_id; the
            // email comes from the targeted account if one was found). No
            // password is ever recorded.
            $this->audit(
                'login_failed',
                'Failed login for username: ' . mb_substr($username, 0, 100),
                'warning',
                [
                    'username'       => mb_substr($username, 0, 100),
                    'reason'         => $user ? 'wrong_password' : 'unknown_username',
                    'target_user_id' => $user->user_id ?? null,
                ],
                false,
                null,
                $user->email ?? null
            );

            SystemLogger::checkBruteForce($request->ip(), $user->email ?? null);
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
            // LOG: wrong 2FA code. event_type is 'login_failed' so that
            // checkBruteForce() counts it (the code is only 6 digits, so it
            // could be brute-forced).
            $this->audit(
                'login_failed',
                "Wrong 2FA code for {$user->fullname} ({$user->role})",
                'warning',
                ['reason' => '2fa_wrong_code', 'role' => $user->role],
                false,
                $user->user_id,
                $user->email
            );
            SystemLogger::checkBruteForce($request->ip(), $user->email);

            return back()->with('error', 'Incorrect code. Please try again.');
        }

        if (!$user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
            // LOG: correct code but already expired
            $this->audit(
                'login_failed',
                "{$user->fullname} ({$user->role}) used an expired 2FA code",
                'info',
                ['reason' => '2fa_expired', 'role' => $user->role],
                false,
                $user->user_id,
                $user->email
            );

            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        DB::table('users')->where('user_id', $user->user_id)->update([
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ]);

        $redirect = session('twofa_redirect', route('user.waiver'));
        session()->forget(['twofa_pending_user_id', 'twofa_redirect']);

        session()->regenerate();
        session([
            'user_id' => $user->user_id,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        // LOG: successful login after 2FA
        $this->logLoginSuccess($user, true);

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
                "Your Pistavora verification code is: {$code}\n\nThis code expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Your Pistavora verification code');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('2FA resend email failed: ' . $e->getMessage());
        }

        return back()->with('status', 'A new code has been sent to your email.');
    }

    public function logout()
    {
        // LOG: grab the user first, BEFORE flushing the session.
        // When nobody is logged in (e.g. someone just opened GET /logout),
        // nothing is logged.
        if (session()->has('user_id')) {
            $this->audit(
                'logout',
                (session('fullname') ?? 'user') . ' (' . (session('role') ?? 'unknown') . ') logged out',
                'info',
                ['role' => session('role')]
            );
        }

        session()->flush();
        return redirect('/login');
    }

    public function showRegisterForm()
    {
        if (session()->has('user_id')) {
            return redirect('/home');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $fullname = trim($request->input('fullname', ''));
        $username = trim($request->input('username', ''));
        $email    = trim($request->input('email', ''));
        $age      = $request->input('age', '');
        $password = $request->input('password', '');
        $confirm  = $request->input('password_confirmation', '');

        if ($fullname === '' || $username === '' || $email === '' || $age === '' || $password === '') {
            return back()->withInput()->with('error', 'Please fill in all fields.');
        }

        if (!is_numeric($age) || $age < 1 || $age > 120) {
            return back()->withInput()->with('error', 'Please enter a valid age.');
        }

        if ($password !== $confirm) {
            return back()->withInput()->with('error', 'Passwords do not match.');
        }

        if (strlen($password) < 8) {
            return back()->withInput()->with('error', 'Password must be at least 8 characters.');
        }

        // Terms & Privacy consent
        if (!$request->boolean('terms')) {
            return back()->withInput()->with('error', 'You must agree to the Terms & Conditions and Privacy Notice to create an account.');
        }

        if (DB::table('users')->where('username', $username)->exists()) {
            return back()->withInput()->with('error', 'Username is already taken.');
        }

        if (DB::table('users')->where('email', $email)->exists()) {
            return back()->withInput()->with('error', 'Email is already registered.');
        }

        $userId = DB::table('users')->insertGetId([
            'name'              => $fullname,
            'username'          => $username,
            'fullname'          => $fullname,
            'email'             => $email,
            'age'               => (int) $age,
            'status'            => 'active',
            'role'              => 'customer',
            'password'          => password_hash($password, PASSWORD_DEFAULT),
            'terms_accepted_at' => now(),
            'terms_version'     => '2026-10-04',
            'created_at'        => now(),
            'updated_at'        => now(),
        ], 'user_id');

        session()->regenerate();

        session([
            'user_id'  => $userId,
            'fullname' => $fullname,
            'email'    => $email,
            'role'     => 'customer',
        ]);

        // LOG: new customer account (self-registered)
        $this->audit(
            'account_created',
            "New customer account (self-registered): {$username}",
            'info',
            ['new' => ['user_id' => $userId, 'username' => $username, 'role' => 'customer']],
            false,
            $userId,
            $email,
            'accounts'
        );

        return redirect()->route('user.waiver');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = DB::table('users')->where('email', $request->email)->first();

        // We never reveal whether the email exists (for security) — always
        // the same generic message, but a code is only actually sent if it does.
        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('users')->where('user_id', $user->user_id)->update([
                'password_change_code'       => $code,
                'password_change_expires_at' => now()->addMinutes(10),
            ]);

            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Your Pistavora password reset code is: {$code}\n\nThis code expires in 10 minutes. If you didn't request this, you can safely ignore this email.",
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Reset your Pistavora password');
                    }
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Password reset email failed: ' . $e->getMessage());
            }

            session(['reset_pending_user_id' => $user->user_id]);
        }

        // LOG: reset code request. Only the log shows whether an account
        // exists; the user is still not told (the message stays generic).
        $this->audit(
            'password_reset_requested',
            $user
                ? "{$user->fullname} ({$user->role}) requested a password reset code"
                : 'Password reset code requested for an email with no account',
            $user ? 'info' : 'warning',
            ['account_found' => (bool) $user],
            false,
            $user->user_id ?? null,
            $user->email ?? mb_substr((string) $request->email, 0, 150)
        );

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
            // LOG: wrong reset code
            $this->audit(
                'password_reset_failed',
                "Wrong reset code for {$user->fullname} ({$user->role})",
                'warning',
                ['reason' => 'wrong_code'],
                false,
                $user->user_id,
                $user->email
            );

            return back()->with('error', 'Incorrect code. Please try again.');
        }

        if (!$user->password_change_expires_at || now()->greaterThan($user->password_change_expires_at)) {
            // LOG: expired reset code
            $this->audit(
                'password_reset_failed',
                "{$user->fullname} ({$user->role}) used an expired reset code",
                'info',
                ['reason' => 'expired'],
                false,
                $user->user_id,
                $user->email
            );

            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        DB::table('users')->where('user_id', $user->user_id)->update([
            'password'                   => password_hash($request->new_password, PASSWORD_DEFAULT),
            'password_change_code'       => null,
            'password_change_expires_at' => null,
        ]);

        session()->forget('reset_pending_user_id');

        // LOG: password changed (neither the password nor the code is recorded)
        $this->audit(
            'password_reset_completed',
            "Password changed for {$user->fullname} ({$user->role}) via reset code",
            'warning',
            ['role' => $user->role],
            false,
            $user->user_id,
            $user->email
        );

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
                "Your Pistavora password reset code is: {$code}\n\nThis code expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Reset your Pistavora password');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password reset resend email failed: ' . $e->getMessage());
        }

        return back()->with('status', 'A new code has been sent to your email.');
    }
}