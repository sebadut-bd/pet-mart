<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $isAdmin = $user && (int) $user->type === 1;

        if (! $isAdmin) {
            // JSON callers (admin SPA API calls) get a status code instead of a redirect
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 5001,
                    'message' => $user ? 'You do not have permission to do this.' : 'Please log in to continue.',
                ], $user ? 403 : 401);
            }

            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        return $next($request);
    }
}
