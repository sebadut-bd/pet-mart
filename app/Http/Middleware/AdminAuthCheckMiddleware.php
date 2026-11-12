<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthCheckMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->status) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->deny($request, 'Your account has been deactivated.', 401);
        }

        if (! $user) {
            return $this->deny($request, 'Please log in to continue.', 401);
        }

        if ((int) $user->type !== 1) {
            return $this->deny($request, 'You do not have permission to do this.', 403);
        }

        return $next($request);
    }

    private function deny(Request $request, string $message, int $status): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['status' => 5001, 'message' => $message], $status);
        }

        return redirect()->route('login')->with('error', $message);
    }
}
