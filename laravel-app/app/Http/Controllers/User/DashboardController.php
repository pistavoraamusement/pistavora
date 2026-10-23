<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Support\ImageData;

class DashboardController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        $upcomingCount = Booking::where('user_id', $user->user_id)
            ->where('status', 'confirmed')
            ->count();

        return view('user.dashboard', compact('user', 'upcomingCount'));
    }

    public function update(\Illuminate\Http\Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect('/login')->with('error', 'Your session has expired. Please log in again.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'avatar_theme' => 'nullable|string|max:60',
            'avatar_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user->name = $validated['name'];

        if (!empty($validated['avatar_theme'])) {
            $user->avatar_theme = $validated['avatar_theme'];
        }

        if ($request->hasFile('avatar_photo')) {
            $user->avatar_path = ImageData::toDataUri($request->file('avatar_photo'));
        }

        $saved = $user->save();
        $user->refresh();

        session(['fullname' => $user->name]);

        if (!$saved) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Could not save your changes. Please try again.');
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Profile updated: name is now "' . $user->name . '", avatar color is "' . $user->avatar_theme . '".');
    }

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
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        $status = $user->two_factor_enabled ? 'enabled' : 'disabled';

        return redirect()->route('user.dashboard')
            ->with('success', "Two-Factor Authentication has been {$status}.");
    }

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
                "Your Pistavora password change code is: {$code}\n\nThis code expires in 10 minutes. If you didn't request this, you can safely ignore this email — your password will not be changed.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Confirm your Pistavora password change');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password change email failed: ' . $e->getMessage());
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'We emailed you a code to confirm your new password.');
    }

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
                "Your Pistavora password change code is: {$code}\n\nThis code expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Confirm your Pistavora password change');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password change resend email failed: ' . $e->getMessage());
        }

        return redirect()->route('user.dashboard')->with('success', 'A new code has been sent to your email.');
    }
}