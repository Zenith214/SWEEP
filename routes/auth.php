<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
|
| These routes are accessible to guests (unauthenticated users) and
| handle login and password reset functionality.
|
*/

Route::middleware('guest')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Registration Routes (Disabled)
    |--------------------------------------------------------------------------
    |
    | Public registration is disabled. Only administrators can create user
    | accounts through the admin user management interface.
    |
    */
    // Route::get('register', [RegisteredUserController::class, 'create'])
    //     ->name('register');
    // Route::post('register', [RegisteredUserController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Login Routes
    |--------------------------------------------------------------------------
    |
    | The login POST route includes the RateLimitLogin middleware which
    | limits attempts to 5 per 15 minutes and locks out for 30 minutes
    | after the limit is exceeded.
    |
    */
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('rate.limit.login');

    /*
    |--------------------------------------------------------------------------
    | Password Reset Routes
    |--------------------------------------------------------------------------
    |
    | These routes handle the password reset flow including requesting a
    | reset link and setting a new password. Reset tokens expire after
    | 60 minutes.
    |
    */
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| These routes require authentication and handle logout, email verification,
| password confirmation, and password updates.
|
*/

Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Email Verification Routes
    |--------------------------------------------------------------------------
    */
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Routes
    |--------------------------------------------------------------------------
    */
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    /*
    |--------------------------------------------------------------------------
    | Logout Route
    |--------------------------------------------------------------------------
    |
    | Terminates the user's session and redirects to the login page.
    |
    */
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
