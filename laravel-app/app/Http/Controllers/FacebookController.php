<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class FacebookController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function callback()
    {
        $facebookUser = Socialite::driver('facebook')->stateless()->user();

        $user = DB::table('users')
            ->where('email', $facebookUser->getEmail())
            ->first();

        if (!$user) {

            // Gumawa ng makatuwirang username base sa email (may fallback kung taken na)
            $baseUsername = strtolower(explode('@', $facebookUser->getEmail())[0]);
            $username = $baseUsername;
            $suffix = 1;
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $baseUsername . $suffix;
                $suffix++;
            }

            $userId = DB::table('users')->insertGetId([
                'name'       => $facebookUser->getName(),
                'fullname'   => $facebookUser->getName(),
                'username'   => $username,
                'email'      => $facebookUser->getEmail(),
                'age'        => null, // wala talagang age na ibinibigay ang basic Facebook OAuth
                'status'     => 'active',
                'role'       => 'customer',
                'password'   => bcrypt(str()->random(32)),
                'created_at' => now(),
                'updated_at' => now(),
            ], 'user_id');

            $user = DB::table('users')->where('user_id', $userId)->first();
        }

        session()->regenerate();

        session([
            'user_id'  => $user->user_id,
            'fullname' => $user->fullname,
            'email'    => $user->email,
            'role'     => $user->role,
        ]);

        Auth::loginUsingId($user->user_id);

        return redirect('/app/booking');
    }
}