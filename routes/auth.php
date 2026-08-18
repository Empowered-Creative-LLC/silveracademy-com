<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle user authentication for the parent portal.
| Note: Registration is for PARENTS ONLY. Staff are imported via admin.
|
*/

Route::middleware('guest')->group(function () {
    // Parent Registration (staff are imported via admin)
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // Parent signup with code (first-time: email + parent code)
    Route::get('parent/signup', [\App\Http\Controllers\Auth\ParentSignupController::class, 'create'])
        ->name('parent.signup');

    Route::post('parent/signup', [\App\Http\Controllers\Auth\ParentSignupController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('parent.signup.store');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Forgot password (throttle to prevent abuse)
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    // Reset password (route name required by Laravel's ResetPassword notification)
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// Pending approval page (accessible without auth)
Route::get('pending-approval', [RegisteredUserController::class, 'pendingApproval'])
    ->name('pending-approval');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
