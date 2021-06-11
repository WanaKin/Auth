<?php

use Illuminate\Support\Facades\Route;

// Load web middleware
Route::middleware(['web'])->group(function () {
    /**
     * The controller class
     *
     * @var string
     */
    $authController = config('auth.controller', \WanaKin\Auth\AuthController::class);

    // Registration
    Route::get('/register', [$authController, 'showRegistrationPage'])->name('auth.register');
    Route::post('/register', [$authController, 'register']);

    // Login
    Route::get('/login', [$authController, 'showLoginPage'])->name('login');
    Route::post('/login', [$authController, 'login']);

    // Routes that require authentication
    Route::middleware(['auth'])->group(function() use($authController) {
        // Dashboard
        Route::get('auth-settings', [$authController, 'dashboard'])->name('auth.dashboard');
        Route::post('auth-settings', [$authController, 'update']);
        Route::post('auth-settings/password', [$authController, 'updatePassword'])->name('auth.dashboard.password');
        Route::get('auth-settings/resend', [$authController, 'resend'])->name('auth.dashboard.resend');

        // Logout
        Route::get('/logout', [$authController, 'logout'])->name('auth.logout');
    });

    // Email verification
    Route::get('/verify-email/{emailVerification:verification_slug}', [$authController, 'verify'])->name('auth.verify');

    // Password reset
    Route::get('/forgot-password', [$authController, 'showForgotPasswordPage'])->name('auth.password.forgot');
    Route::post('/forgot-password', [$authController, 'forgotPassword']);
    Route::get('/password-reset/{passwordResetToken:token}', [$authController, 'showResetPasswordPage'])->name('auth.password.reset');
    Route::post('/password-reset/{passwordResetToken:token}', [$authController, 'resetPassword']);
});
