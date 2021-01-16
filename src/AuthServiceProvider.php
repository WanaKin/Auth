<?php
namespace WanaKin\Auth;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider {
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
    public function boot() {
        // Load migrations
        $this->loadMigrationsFrom( __DIR__ . '/../database/migrations' );

        // Load views
        $this->loadViewsFrom( __DIR__ . '/../resources/views', 'auth' );

        // Load routes
        $this->loadRoutesFrom( __DIR__ . '/../routes/auth.php' );
    }
}
