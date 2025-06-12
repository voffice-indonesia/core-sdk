<?php

namespace VoxDev\Core\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class AutoConfig
{
    /**
     * Auto-configure the entire Laravel application to use Core SDK
     * This method handles all the configuration needed for plug-and-play integration
     */
    public static function configure(): void
    {
        static::configureAuthDefaults();
        static::configureRoutes();
        static::configureMiddleware();
    }

    /**
     * Set Core SDK as the default authentication system
     */
    public static function configureAuthDefaults(): void
    {
        $guardName = config('core.guard_name', 'core');

        // Set as default auth guard
        Config::set('auth.defaults.guard', $guardName);

        // Ensure auth middleware uses Core guard by default
        Config::set('auth.defaults.passwords', 'users'); // Keep default password resets
    }

    /**
     * Configure standard authentication routes
     */
    public static function configureRoutes(): void
    {
        if (! app()->routesAreCached()) {
            Route::middleware('web')->group(function () {
                // Override default login route to use Core SDK
                Route::get('/login', function () {
                    return redirect()->route('core.auth.login');
                })->name('login');

                // Add a default dashboard route that uses Core authentication
                Route::get('/dashboard', function () {
                    return view('core::dashboard');
                })->middleware('vauth')->name('dashboard');

                // Home route that redirects to dashboard if authenticated
                Route::get('/home', function () {
                    return redirect()->route('dashboard');
                })->name('home');
            });
        }
    }

    /**
     * Configure middleware aliases
     */
    public static function configureMiddleware(): void
    {
        $guardName = config('core.guard_name', 'core');

        // Create an alias for the auth middleware with Core guard
        app('router')->aliasMiddleware('core-auth', "auth:{$guardName}");

        // Create a combined middleware for both auth and vauth
        app('router')->aliasMiddleware('core-protect', ['core-auth', 'vauth']);
    }

    /**
     * Configure Filament (if installed) to use Core SDK
     */
    public static function configureFilament(): void
    {
        if (class_exists(\Filament\FilamentServiceProvider::class)) {
            $guardName = config('core.guard_name', 'core');

            Config::set('filament.auth.guard', $guardName);
            Config::set('filament.auth.pages.login', \VoxDev\Core\Filament\Pages\Login::class);
        }
    }

    /**
     * Get recommended configuration for the client app
     */
    public static function getRecommendedConfig(): array
    {
        return [
            'auth' => [
                'defaults' => [
                    'guard' => config('core.guard_name', 'core'),
                ],
            ],
            'session' => [
                'driver' => 'cookie', // Recommended for OAuth flows
                'encrypt' => true,
            ],
        ];
    }

    /**
     * Apply all recommended configurations
     */
    public static function applyRecommendedConfig(): void
    {
        $config = static::getRecommendedConfig();

        foreach ($config as $key => $values) {
            foreach ($values as $subKey => $subValues) {
                if (is_array($subValues)) {
                    foreach ($subValues as $finalKey => $finalValue) {
                        Config::set("{$key}.{$subKey}.{$finalKey}", $finalValue);
                    }
                } else {
                    Config::set("{$key}.{$subKey}", $subValues);
                }
            }
        }
    }
}
