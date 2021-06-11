<?php
namespace WanaKin\Auth;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     *
     * @return void
     */
    public function register() {

    }

    /**
     * Bootstrap package services
     *
     * @return void
     */
    public function boot()
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'auth');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/auth')
        ], 'auth');

        // Load API routes only if API is enabled in the config
        if (config('auth.routes.api', FALSE)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        // Load web routes if enabled (default to true)
        if (config('auth.routes.web', TRUE)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
    }
}
