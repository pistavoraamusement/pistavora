<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLiveVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        SiteVisit::updateOrCreate(
            ['session_id' => $request->session()->getId()],
            [
                'user_id'      => session('user_id'), // adjust kung iba ang session key niyo
                'ip_address'   => $request->ip(),
                'last_seen_at' => now(),
            ]
        );

        return $next($request);
    }
}