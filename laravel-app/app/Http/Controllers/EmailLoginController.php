<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailLoginController extends Controller
{
    private const TRUST_DAYS   = 14;
    private const COOKIE       = 'wp_trusted_device';
    private const MAX_ATTEMPTS = 5;
    private const CODE_MINUTES = 10;

    // Step 1: user enters email
    public function sendCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($request->input('email')));

        $user = DB::table('users')->where('email', $email)->first();

        if ($user && $user->status !== 'active') {
            return back()->withInput()->with('error', 'This account is not active.');
        }

        // Existing user on a trusted device that logged in within the last 14 days -> no code needed
        if ($user && $this->deviceIsTrusted($request, $user)) {
            $this->touchDevice($request);
            return $this->loginUser($user);
        }

        // Otherwise (new email, new device, or inactive for 14+ days) -> send a code
        if (!$this->issueCode($email)) {
            return back()->withInput()->with('error', 'We could not send the email right now. Please try again.');
        }

        return redirect('/login/email/verify')
            ->with('status', 'We sent a 6-digit code to your email.');
    }

    public function showVerifyForm()
    {
        if (!session()->has('emaillogin')) {
            return redirect('/login');
        }
        return view('auth.email-code');
    }

    // Step 2: verify code -> log in (creating the account first if the email is new)
    public function verify(Request $request)
    {
        $pending = session('emaillogin');
        if (!$pending) {
            return redirect('/login')->with('error', 'Please enter your email again.');
        }

        if (($pending['attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
            session()->forget(['emaillogin', 'emaillogin_email']);
            return redirect('/login')->with('error', 'Too many incorrect attempts. Please request a new code.');
        }

        if (now()->timestamp > $pending['expires']) {
            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        $code = trim($request->input('code', ''));
        $hash = hash_hmac('sha256', $code, config('app.key'));

        if (!hash_equals($pending['hash'], $hash)) {
            session()->put('emaillogin.attempts', ($pending['attempts'] ?? 0) + 1);
            return back()->with('error', 'Incorrect code. Please try again.');
        }

        $email = $pending['email'];
        $user  = DB::table('users')->where('email', $email)->first();

        if ($user && $user->status !== 'active') {
            return redirect('/login')->with('error', 'This account is not active.');
        }

        if (!$user) {
            $user = $this->createAccount($email);
        }

        // Remember this browser for 14 days (sliding: renewed on every login)
        $this->trustDevice($request, $user);

        return $this->loginUser($user);
    }

    public function resend()
    {
        $pending = session('emaillogin');
        if (!$pending) {
            return redirect('/login');
        }

        if (!$this->issueCode($pending['email'])) {
            return back()->with('error', 'We could not send the email right now. Please try again.');
        }

        return back()->with('status', 'A new code has been sent to your email.');
    }

    // ───────────────────────── helpers ─────────────────────────

    private function issueCode(string $email): bool
    {
        $code = (string) random_int(100000, 999999);

        session([
            'emaillogin' => [
                'email'    => $email,
                'hash'     => hash_hmac('sha256', $code, config('app.key')),
                'expires'  => now()->addMinutes(self::CODE_MINUTES)->timestamp,
                'attempts' => 0,
            ],
            'emaillogin_email' => $email,
        ]);

        try {
            Mail::raw(
                "Your Pistavora login code is: {$code}\n\nThis code expires in " . self::CODE_MINUTES . " minutes. If you didn't request this, you can ignore this email.",
                function ($message) use ($email) {
                    $message->to($email)->subject('Your Pistavora login code');
                }
            );
            return true;
        } catch (\Throwable $e) {
            Log::error('Email login code failed: ' . $e->getMessage());
            return false;
        }
    }

    private function createAccount(string $email)
    {
        $local = Str::before($email, '@');

        $base     = substr(preg_replace('/[^a-z0-9_]/i', '', $local) ?: 'guest', 0, 20);
        $username = $base;
        while (DB::table('users')->where('username', $username)->exists()) {
            $username = $base . random_int(100, 99999);
        }

        $fullname = trim(Str::title(preg_replace('/[._\-\d]+/', ' ', $local))) ?: $username;

        $userId = DB::table('users')->insertGetId([
            'name'              => $fullname,
            'username'          => $username,
            'fullname'          => $fullname,
            'email'             => $email,
            'age'               => 0, // not collected here; ask for it in the profile/waiver step
            'status'            => 'active',
            'role'              => 'customer',
            'password'          => password_hash(Str::random(40), PASSWORD_DEFAULT), // unusable random password
            'terms_accepted_at' => now(),
            'terms_version'     => '2026-10-04',
            'created_at'        => now(),
            'updated_at'        => now(),
        ], 'user_id');

        return DB::table('users')->where('user_id', $userId)->first();
    }

    private function loginUser($user)
    {
        session()->forget(['emaillogin', 'emaillogin_email']);
        session()->regenerate();
        session([
            'user_id'  => $user->user_id,
            'fullname' => $user->fullname,
            'email'    => $user->email,
            'role'     => $user->role,
        ]);

        if ($user->role === 'customer') {
            return redirect('/app/waiver');
        }
        if ($user->role === 'cashier') {
            return redirect('/home');
        }
        return redirect('/ml-forecast');
    }

    // ── trusted device (14-day, sliding) ──

    private function deviceIsTrusted(Request $request, $user): bool
    {
        $raw = $request->cookie(self::COOKIE);
        if (!$raw) {
            return false;
        }

        $row = DB::table('email_login_devices')
            ->where('token_hash', hash('sha256', $raw))
            ->where('user_id', $user->user_id)
            ->first();

        if (!$row) {
            return false;
        }

        if (Carbon::parse($row->last_used_at)->lt(now()->subDays(self::TRUST_DAYS))) {
            DB::table('email_login_devices')->where('id', $row->id)->delete();
            return false;
        }

        return true;
    }

    private function touchDevice(Request $request): void
    {
        $raw = $request->cookie(self::COOKIE);
        if ($raw) {
            DB::table('email_login_devices')
                ->where('token_hash', hash('sha256', $raw))
                ->update(['last_used_at' => now(), 'updated_at' => now()]);
        }
    }

    private function trustDevice(Request $request, $user): void
    {
        // drop the old token of this browser, if any
        $old = $request->cookie(self::COOKIE);
        if ($old) {
            DB::table('email_login_devices')->where('token_hash', hash('sha256', $old))->delete();
        }

        $token = Str::random(64);

        DB::table('email_login_devices')->insert([
            'user_id'      => $user->user_id,
            'token_hash'   => hash('sha256', $token),
            'last_used_at' => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // cookie lives 60 days; the real 14-day rule is enforced on the server via last_used_at
        Cookie::queue(Cookie::make(self::COOKIE, $token, 60 * 24 * 60));
    }
}