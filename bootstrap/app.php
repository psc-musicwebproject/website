<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
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
