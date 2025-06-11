<?php

namespace VoxDev\Core;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VoxDev\Core\Auth\CoreAuthGuard;
use VoxDev\Core\Auth\CoreAuthUserProvider as AuthCoreAuthUserProvider;
use VoxDev\Core\Commands\CoreSetupCommand;
use VoxDev\Core\Livewire\AuthCallback;
use VoxDev\Core\Livewire\AuthRedirect;
use VoxDev\Core\Livewire\AuthStatus;
use VoxDev\Core\Middleware\VAuthMiddleware;

class CoreServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('core-sdk')
            ->hasConfigFile('core')
            ->hasRoute('web')
            ->hasViews()
            ->hasCommand(CoreSetupCommand::class);
    }

    public function bootingPackage()
    {
        // Register custom auth guard
        Auth::extend(config('core.guard_name', 'core'), function ($app) {
            return new CoreAuthGuard(
                new AuthCoreAuthUserProvider(),
                $app['session.store']
            );
        });

        // Register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('vauth', VAuthMiddleware::class);

        // Register Livewire components
        if (class_exists(Livewire::class)) {
            try {
                Livewire::component('core-auth-redirect', AuthRedirect::class);
                Livewire::component('core-auth-callback', AuthCallback::class);
                Livewire::component('core-auth-status', AuthStatus::class);
            } catch (\Exception $e) {
                // Livewire not available, skip component registration
            }
        }
    }

    public function registeringPackage()
    {
        // Merge additional configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/core.php', 'core');
    }

    public function packageBooted()
    {
        // Publish views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath('/../resources/views') => resource_path('views/vendor/core'),
            ], 'core-sdk-views');

            // Publish Livewire components
            $this->publishes([
                $this->package->basePath('/src/Livewire') => app_path('Livewire/Core'),
            ], 'core-sdk-livewire');

            // Publish individual page templates
            $this->publishes([
                $this->package->basePath('/../resources/views/auth/login.blade.php') => resource_path('views/auth/oauth-login.blade.php'),
                $this->package->basePath('/../resources/views/auth/callback.blade.php') => resource_path('views/auth/oauth-callback.blade.php'),
                $this->package->basePath('/../resources/views/dashboard.blade.php') => resource_path('views/oauth-dashboard.blade.php'),
            ], 'core-sdk-pages');
        }
    }
}
