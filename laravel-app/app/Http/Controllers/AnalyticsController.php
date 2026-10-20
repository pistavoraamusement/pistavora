<?php
namespace App\Http\Controllers;

class AnalyticsController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }
        return view('analytics.index');
    }
}