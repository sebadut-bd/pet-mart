<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || auth()->user()->type !== 1) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        return $next($request);
    }
}
