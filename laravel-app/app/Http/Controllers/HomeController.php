<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $role = strtolower((string) session('role'));

        if ($role === 'customer') {
            return redirect()->route('user.bookings');
        }

        if ($role === 'cashier') {
            return redirect('/inventory');
        }

        return view('index');
    }
}