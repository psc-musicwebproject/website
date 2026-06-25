<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Manually load channel definitions to avoid Laravel auto-registering
            // the default Broadcast::routes() which overrides our custom auth route.
            require __DIR__ . '/../routes/channels.php';

            // Custom broadcasting auth route that supports both 'admin' and 'web' guards.
            // Laravel's default Broadcast::routes() only checks the default guard,
            // so we register our own route that tries both guards before delegating
            // to Laravel's built-in channel authorization (channels.php).
            Route::middleware(['web'])->group(function () {
                Route::match(['get', 'post'], '/broadcasting/auth', function (Illuminate\Http\Request $request) {
                    // Try admin guard first, then fall back to web guard
                    $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

                    if (!$user) {
                        abort(403, 'Unauthenticated.');
                    }

                    // Set the resolved user on the request so that
                    // Laravel's Broadcast::auth() can find them via $request->user()
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });

                    // Delegate to Laravel's built-in broadcast authorization,
                    // which will match against the channel callbacks in channels.php
                    return Broadcast::auth($request);
                })->name('broadcasting.auth');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Replace the default Authenticate middleware with our custom one
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsurePasswordIsReset::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            // Check the route middleware to see which guard is being used
            $route = $request->route();
            if ($route) {
                $routeMiddleware = $route->middleware();
                foreach ($routeMiddleware as $m) {
                    if (is_string($m) && str_starts_with($m, 'auth:')) {
                        $guard = substr($m, 5);
                        if ($guard !== 'web') {
                            return route('login', ['guard' => $guard]);
                        }
                    }
                }
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
