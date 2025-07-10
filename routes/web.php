<?php

use Illuminate\Support\Facades\Route;
use VoxDev\Core\Controllers\Auth\CallbackController;
use VoxDev\Core\Controllers\Auth\CoreController;
use VoxDev\Core\Controllers\Auth\LivewireAuthController;

/*
|--------------------------------------------------------------------------
| OAuth Authentication Routes
|--------------------------------------------------------------------------
|
| Here we register the routes needed for OAuth authentication flow.
| These routes handle the authorization redirect and callback processing.
| IMPORTANT: These routes MUST use web middleware for session persistence.
|
*/

// Wrap all OAuth routes in web middleware to ensure session persistence
Route::middleware(['web'])->group(function () {

    // Route group for OAuth authentication
    Route::prefix(config('core.route_prefix', 'auth/oauth'))
        ->name('core.auth.')
        ->group(function () {

            // Redirect to OAuth authorization server
            Route::get('/redirect', [CoreController::class, 'redirect'])
                ->name('redirect');

            // Handle OAuth callback
            Route::get('/callback', [CallbackController::class, 'callback'])
                ->name('callback');

            // Logout route
            Route::post('/logout', [CoreController::class, 'logout'])
                ->name('logout');
        });

    // Alternative simpler routes (can be used if prefix is not desired)
    Route::get('/vauth/redirect', [CoreController::class, 'redirect'])->name('vauth.redirect');
    Route::get('/vauth/callback', [CallbackController::class, 'callback'])->name('vauth.callback');
    Route::post('/vauth/logout', [CoreController::class, 'logout'])->name('vauth.logout');

    // Livewire-powered authentication pages (optional - can be customized)
    Route::get('/oauth/login', [LivewireAuthController::class, 'login'])->name('oauth.login');
    Route::get('/oauth/callback-ui', [LivewireAuthController::class, 'callbackUi'])->name('oauth.callback-ui');
    Route::get('/oauth/dashboard', [LivewireAuthController::class, 'dashboard'])
        ->middleware(['vauth'])
        ->name('oauth.dashboard');
});
