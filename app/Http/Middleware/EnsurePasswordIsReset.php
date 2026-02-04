<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class EnsurePasswordIsReset
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->reset_password_on_next_login) {
            // Prevent redirect loop
            $allowedRoutes = [
                'auth.web.newpass',
                'auth.web.newpass.form', // The GET route name
                'auth.web.logout',
            ];

            if (!in_array(Route::currentRouteName(), $allowedRoutes)) {
                return redirect()->route('auth.web.newpass.form');
            }
        }

        return $next($request);
    }
}
