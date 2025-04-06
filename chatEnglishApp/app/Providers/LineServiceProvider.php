<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LineNotificationService;

class LineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('line-notification', function ($app) {
            return new LineNotificationService(
                config('services.line.channel_access_token'),
                config('services.line.channel_secret')
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
