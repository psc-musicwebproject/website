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
        // Don't auto-register channels to avoid default Broadcast::routes()
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Manually load channel definitions without registering routes
            require __DIR__ . '/../routes/channels.php';

            // Custom broadcasting routes that check both guards
            Route::middleware(['web'])->group(function () {
                Route::match(['get', 'post'], '/broadcasting/auth', function (Illuminate\Http\Request $request) {
                    // Try to authenticate with either admin or web guard
                    $user = Auth::guard('admin')->user() ?? Auth::guard('web')->user();

                    if (!$user) {
                        return response()->json(['error' => 'Unauthenticated'], 403);
                    }

                    // Set user resolver so Broadcast facade can find the authenticated user
                    $request->setUserResolver(function () use ($user) {
                        return $user;
                    });

                    try {
                        // Use Laravel's channel authorization from routes/channels.php
                        // This will automatically use whatever channel callbacks you define
                        return Broadcast::auth($request);
                    } catch (\Exception $e) {
                        // Authorization failed
                        return response()->json(['error' => 'Forbidden'], 403);
                    }
                })->name('broadcasting.auth');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Replace the default Authenticate middleware with our custom one
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
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
