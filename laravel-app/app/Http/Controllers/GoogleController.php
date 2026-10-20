<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = DB::table('users')
            ->where('email', $googleUser->getEmail())
            ->first();

        if (!$user) {

            $id = DB::table('users')->insertGetId([
                'name' => $googleUser->getName(),
                'fullname' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(str()->random(32)),
                'role' => 'customer',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'user_id');

            $user = DB::table('users')->where('user_id', $id)->first();
        }

        Auth::loginUsingId($user->user_id);
        Session::put('user_id', $user->user_id);
        Session::put('user_name', $user->name);

        return redirect('/app/dashboard');
    }
}