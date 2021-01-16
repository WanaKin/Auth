<?php
use Illuminate\Support\Facades\Route;
use WanaKin\Auth\AuthController;

// Load middleware
Route::middleware( ['web'] )->group( function() {
        // Registration
        Route::get( '/register', [AuthController::class, 'showRegistrationPage'] )->name( 'auth.register' );
        Route::post( '/register', [AuthController::class, 'register'] );

        // Login
        Route::get( '/login', [AuthController::class, 'showLoginPage' ] )->name( 'auth.login' );
        Route::post( '/login', [AuthController::class, 'login'] );

        // Routes that require authentication
        Route::middleware( ['auth'] )->group( function() {
                // Dashboard
                Route::get( '/dashboard/auth', [AuthController::class, 'dashboard'] )->name( 'auth.dashboard' );
                Route::post( '/dashboard/auth', [AuthController::class, 'update'] );
                Route::post( '/dashboard/auth/password', [AuthController::class, 'updatePassword'] )->name( 'auth.dashboard.password' );
                Route::get( '/dashboard/auth/resend', [AuthController::class, 'resend'] )->name( 'auth.dashboard.resend' );

                // Logout
                Route::get( '/logout', [AuthController::class, 'logout'] )->name( 'auth.logout' );
            } );
        
        // Email verification
        Route::get( '/verify-email/{emailVerification:verification_slug}', [AuthController::class, 'verify'] )->name( 'auth.verify' );

        // Password reset
        Route::get( '/forgot-password', [AuthController::class, 'showForgotPasswordPage'] )->name( 'auth.password.forgot' );
        Route::post( '/forgot-password', [AuthController::class, 'forgotPassword'] );
        Route::get( '/password-reset/{passwordResetToken:token}', [AuthController::class, 'showResetPasswordPage' ] )->name( 'auth.password.reset' );
        Route::post( '/password-reset/{passwordResetToken:token}', [AuthController::class, 'resetPassword'] );
    } );
