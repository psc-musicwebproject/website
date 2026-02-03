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
                        \Illuminate\Support\Facades\Log::debug('Broadcasting auth: no user found');
                        return response()->json(['error' => 'Unauthenticated'], 403);
                    }

                    $channelName = $request->input('channel_name');
                    $socketId = $request->input('socket_id');

                    \Illuminate\Support\Facades\Log::debug('Broadcasting auth attempt', [
                        'user_id' => $user->id,
                        'user_type' => $user->type,
                        'channel' => $channelName,
                        'socket_id' => $socketId,
                    ]);

                    // Remove 'private-' prefix for matching
                    $channelWithoutPrefix = preg_replace('/^private-/', '', $channelName);

                    // Direct channel authorization
                    $authorized = false;
                    $channelData = [];

                    // Match admin.{id} pattern
                    if (preg_match('/^admin\.(\d+)$/', $channelWithoutPrefix, $matches)) {
                        $channelId = (int) $matches[1];
                        $authorized = (int) $user->id === $channelId && $user->type === 'admin';
                        \Illuminate\Support\Facades\Log::debug('Admin channel check', [
                            'channel_id' => $channelId,
                            'user_id' => $user->id,
                            'user_type' => $user->type,
                            'id_match' => (int) $user->id === $channelId,
                            'type_match' => $user->type === 'admin',
                            'authorized' => $authorized,
                        ]);
                    }
                    // Match user.{id} pattern
                    elseif (preg_match('/^user\.(\d+)$/', $channelWithoutPrefix, $matches)) {
                        $channelId = (int) $matches[1];
                        $authorized = (int) $user->id === $channelId && $user->type !== 'admin';
                        \Illuminate\Support\Facades\Log::debug('User channel check', [
                            'channel_id' => $channelId,
                            'user_id' => $user->id,
                            'user_type' => $user->type,
                            'id_match' => (int) $user->id === $channelId,
                            'type_match' => $user->type !== 'admin',
                            'authorized' => $authorized,
                        ]);
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Unknown channel pattern', ['channel' => $channelWithoutPrefix]);
                    }

                    if (!$authorized) {
                        return response()->json(['error' => 'Forbidden', 'message' => 'Channel authorization failed'], 403);
                    }

                    // Return Pusher/Reverb compatible auth response
                    $pusher = new \Pusher\Pusher(
                        config('broadcasting.connections.reverb.key'),
                        config('broadcasting.connections.reverb.secret'),
                        config('broadcasting.connections.reverb.app_id')
                    );

                    $auth = $pusher->authorizeChannel($channelName, $socketId);

                    return response()->json(json_decode($auth, true));
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
