<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Only treat the route as admin-only when 'admin' is the sole requested guard.
        // This avoids denying access on routes that accept either 'web' or 'admin' (e.g. auth:web,admin).
        if (in_array('admin', $guards) && !in_array('web', $guards) && Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if ($user && $user->type !== 'admin') {
                return redirect()->route('login', [
                    'guard' => 'admin',
                    'error' => 'access_denied'
                ]);
            }
        }
        
        // Authenticate the user - this will throw AuthenticationException if not authenticated
        $this->authenticate($request, $guards);
        
        return $next($request);
    }
    
    /**
     * Determine if the user is logged in to any of the given guards.
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        $this->unauthenticated($request, $guards);
    }
    
    /**
     * Handle an unauthenticated user.
     */
    protected function unauthenticated($request, array $guards)
    {
        $guard = !empty($guards) && $guards[0] !== 'web' && $guards[0] !== null ? $guards[0] : null;
        
        if ($guard) {
            throw new \Illuminate\Auth\AuthenticationException(
                'Unauthenticated.', $guards, route('login', ['guard' => $guard])
            );
        }
        
        throw new \Illuminate\Auth\AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }
    
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
        
        return null;
    }
}