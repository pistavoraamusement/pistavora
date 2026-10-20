<?php
namespace App\Http\Controllers;

class ResetPasswordController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }
        return view('reset-password.index');
    }
}