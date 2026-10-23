<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share the logged-in user with the sidebar and layout partials
        View::composer(['partials.bottomnav', 'layouts.user'], function ($view) {
            $navUser = session()->has('user_id')
                ? User::find(session('user_id'))
                : null;

            $view->with('navUser', $navUser);
        });
    }
}