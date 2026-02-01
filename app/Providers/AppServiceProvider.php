<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Auth\AdminProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('admin', function ($app, array $config) {
            return new AdminProvider($app['hash'], $config['model']);
        });

        /** Announce AppSetting to Global */

        if (class_exists(\App\Models\AppSetting::class)) {
            view()->share('AppSetting', \App\Models\AppSetting::class);
        }

        /** Announce Line Event */
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('line', \SocialiteProviders\Line\Provider::class);
        });

        if (! $this->app->runningInConsole()) {
        $appName = \App\Models\AppSetting::getSetting('name') ?? config('app.name');
        config(['mail.from.name' => $appName]);
        }
    }
}
