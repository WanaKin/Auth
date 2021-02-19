<?php
use Illuminate\Support\Facades\Route;
use WanaKin\Auth\AuthApiController as AuthController;

// Load middleware
Route::middleware( ['api'] )->prefix( '/api' )->group( function() {
        // Registration
        Route::post( '/register', [AuthController::class, 'register'] )->name( 'api.register' );

        // Login
        Route::post( '/login', [AuthController::class, 'login'] )->name( 'api.login' );

        // Routes that require authentication
        Route::middleware( ['auth'] )->group( function() {
                // Dashboard
                Route::put( '/auth', [AuthController::class, 'update'] )->name( 'api.auth.update' );
                Route::put( '/auth/password', [AuthController::class, 'updatePassword'] )->name( 'auth.dashboard.password' );

                // Logout
                Route::get( '/logout', [AuthController::class, 'logout'] )->name( 'api.auth.logout' );
            } );
        
        // Email verification
        //Route::get( '/verify-email/{emailVerification:verification_slug}', [AuthController::class, 'verify'] )->name( 'auth.verify' );

        // Password reset
        Route::post( '/forgot-password', [AuthController::class, 'forgotPassword'] )->name( 'api.auth.forgot' );
        Route::post( '/password-reset/{passwordResetToken:token}', [AuthController::class, 'resetPassword'] )->name( 'api.auth.reset' );
    } );
