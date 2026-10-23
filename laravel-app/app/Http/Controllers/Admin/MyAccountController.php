<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemLogger;
use App\Support\ImageData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MyAccountController extends Controller
{
    private function audit(
        string $eventType,
        string $description,
        string $severity = 'info',
        array $context = [],
        bool $isAnomaly = false
    ): void {
        try {
            $sessionId = session('user_id');
            $uid = is_numeric($sessionId) ? (int) $sessionId : null;

            SystemLogger::record(
                $eventType,
                $description,
                $severity,
                $context,
                $isAnomaly,
                $uid,
                session('email'),
                'accounts'
            );
        } catch (\Throwable $e) {
            Log::error('SystemLogger failed (MyAccount): ' . $e->getMessage());
        }
    }

    private function currentUser()
    {
        $id = session('user_id');
        if (!is_numeric($id)) {
            return null;
        }
        return DB::table('users')->where('user_id', (int) $id)->first();
    }

    public function index()
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('admin.my-account', compact('user'));
    }

    public function updateInfo(Request $request)
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'fullname'     => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username,' . $user->user_id . ',user_id',
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_photo' => 'nullable|boolean',
        ]);

        $fields = ['fullname', 'username'];

        $norm = function ($field, $value) {
            return trim((string) ($value ?? ''));
        };

        $update  = [];
        $newVals = [];

        foreach ($fields as $field) {
            $old = $norm($field, $user->$field ?? null);
            $new = $norm($field, $data[$field] ?? null);

            if ($old !== $new) {
                $newVals[$field] = $new;
                $update[$field]  = $new;
            }
        }

        // ── Profile photo (naka-store na sa database bilang data URI) ──
        $photoChanged = false;

        if ($request->hasFile('photo')) {
            $update['photo'] = ImageData::toDataUri($request->file('photo'));
            $photoChanged = true;
        } elseif ($request->boolean('remove_photo') && !empty($user->photo)) {
            $update['photo'] = null;
            $photoChanged = true;
        }

        if (!$update) {
            return back()->with('info_success', 'No changes were made.');
        }

        DB::table('users')->where('user_id', $user->user_id)->update($update);

        if (array_key_exists('fullname', $update)) {
            session(['fullname' => $update['fullname']]);
        }

        // Bawal i-store ang base64 sa session (masyadong malaki)
        if ($photoChanged) {
            session()->forget('photo');
        }

        $msg = ($photoChanged && !$newVals)
            ? 'Your profile picture has been updated.'
            : 'Your profile has been updated.';

        return back()->with('info_success', $msg);
    }

    public function updatePassword(Request $request)
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed|different:current_password',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            $this->audit(
                'password_change_failed',
                "Incorrect current password while {$user->username} tried to change their password",
                'warning',
                ['target_user_id' => (int) $user->user_id, 'by_self' => true],
                true
            );

            return back()
                ->withErrors(['current_password' => 'The current password you entered is incorrect.'])
                ->withInput();
        }

        DB::table('users')->where('user_id', $user->user_id)->update([
            'password' => Hash::make($request->password),
        ]);

        $request->session()->regenerate();

        $this->audit(
            'password_changed',
            "{$user->username} changed their own password",
            'warning',
            [
                'target_user_id' => (int) $user->user_id,
                'target_role'    => $user->role,
                'by_self'        => true,
            ]
        );

        return back()->with('password_success', 'Your password has been changed.');
    }
}