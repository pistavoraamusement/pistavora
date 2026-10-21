<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\SystemLogger;

class AccountController extends Controller
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
                $eventType, $description, $severity, $context, $isAnomaly,
                $uid, session('email'), 'accounts'
            );

            if (str_ends_with($eventType, '_deleted')) {
                SystemLogger::checkBulkDelete($uid, 'accounts');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SystemLogger failed (Accounts): ' . $e->getMessage());
        }
    }

    public function index()
    {
        $users = DB::table('users')
            ->where('role', '!=', 'customer')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('accounts.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'password' => 'required|min:6',
            'role'     => 'required|in:cashier,tl,manager,admin',
        ]);

        $newId = DB::table('users')->insertGetId([
            'fullname'   => $request->fullname,
            'username'   => $request->username,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'status'     => 'active',
            'created_at' => now(),
        ], 'user_id');

        $this->audit(
            'account_created',
            "Created new account: {$request->username} ({$request->role})",
            $request->role === 'admin' ? 'warning' : 'info',
            [
                'new' => [
                    'user_id'  => $newId,
                    'fullname' => mb_substr($request->fullname, 0, 150),
                    'username' => mb_substr($request->username, 0, 100),
                    'role'     => $request->role,
                    'status'   => 'active',
                ],
            ],
            $request->role === 'admin'
        );

        return redirect()->route('accounts.index')->with('success', 'Account created successfully!');
    }

    public function update(Request $request, int $user_id)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user_id . ',user_id|max:255',
            'role'     => 'required|in:cashier,tl,manager,admin',
        ]);

        $old = DB::table('users')->where('user_id', $user_id)->first();

        $data = [
            'fullname' => $request->fullname,
            'username' => $request->username,
            'role'     => $request->role,
        ];

        $passwordChanged = $request->filled('password');
        if ($passwordChanged) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('user_id', $user_id)->update($data);

        if ($old) {
            $actorId = is_numeric(session('user_id')) ? (int) session('user_id') : null;
            $isSelf  = $actorId !== null && $actorId === (int) $user_id;

            if ($old->role !== $request->role) {
                $this->audit(
                    'role_changed',
                    "Changed role of {$old->username}: {$old->role} → {$request->role}"
                        . ($isSelf ? ' (self)' : ''),
                    'warning',
                    [
                        'target_user_id' => (int) $user_id,
                        'old'            => ['role' => $old->role],
                        'new'            => ['role' => $request->role],
                        'self_change'    => $isSelf,
                    ],
                    $request->role === 'admin' || $isSelf
                );
            }

            if ($passwordChanged) {
                $this->audit(
                    'password_changed',
                    "Changed password of {$old->username}" . ($isSelf ? ' (self)' : ' by admin'),
                    'warning',
                    [
                        'target_user_id' => (int) $user_id,
                        'target_role'    => $request->role,
                        'by_self'        => $isSelf,
                    ]
                );
            }

            $oldVals = [];
            $newVals = [];
            if ($old->fullname !== $request->fullname) {
                $oldVals['fullname'] = $old->fullname;
                $newVals['fullname'] = $request->fullname;
            }
            if ($old->username !== $request->username) {
                $oldVals['username'] = $old->username;
                $newVals['username'] = $request->username;
            }
            if ($newVals) {
                $this->audit(
                    'account_updated',
                    "Edited account of {$old->username}: " . implode(', ', array_keys($newVals)),
                    'info',
                    ['target_user_id' => (int) $user_id, 'old' => $oldVals, 'new' => $newVals]
                );
            }
        }

        return redirect()->route('accounts.index')->with('success', 'Account updated successfully!');
    }

    public function destroy(int $user_id)
    {
        $user = DB::table('users')->where('user_id', $user_id)->first();

        $deleted = DB::table('users')->where('user_id', $user_id)->delete();

        if ($user && $deleted > 0) {
            $actorId = is_numeric(session('user_id')) ? (int) session('user_id') : null;
            $isSelf  = $actorId !== null && $actorId === (int) $user_id;

            $this->audit(
                'user_deleted',
                "Deleted account: {$user->username} ({$user->role})" . ($isSelf ? ' (self)' : ''),
                'critical',
                [
                    'old' => [
                        'user_id'  => (int) $user->user_id,
                        'fullname' => mb_substr((string) $user->fullname, 0, 150),
                        'username' => mb_substr((string) $user->username, 0, 100),
                        'role'     => $user->role,
                        'status'   => $user->status ?? null,
                    ],
                    'self_delete' => $isSelf,
                ],
                $isSelf || $user->role === 'admin'
            );
        }

        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully!');
    }

    public function create()
    {
        $users = DB::table('users')
            ->where('role', '!=', 'customer')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('accounts.create', compact('users'));
    }
}
