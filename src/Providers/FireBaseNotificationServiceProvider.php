<?php

namespace SendFireBaseNotificationPHP\Providers;

use Illuminate\Support\ServiceProvider;

class FireBaseNotificationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register config file
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/firebase.php', 'firebase'
        );

        // Register repository and service
        $this->app->singleton(\SendFireBaseNotificationPHP\Repositories\FirebaseNotificationRepository::class);
        $this->app->singleton(\SendFireBaseNotificationPHP\Services\FirebaseNotificationService::class);
    }

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Config/firebase.php' => config_path('firebase.php'),
        ], 'config');
    }
}
