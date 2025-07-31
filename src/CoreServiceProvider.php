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
use VoxDev\Core\Infrastructure\Providers\CleanArchitectureServiceProvider;
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
        // Auto-register components based on configuration
        if (config('core.auto_register_guard', true)) {
            $this->registerAuthGuard();
            $this->registerUserProvider();
        }

        // Auto-register middleware
        if (config('core.auto_register_middleware', true)) {
            $router = $this->app->make(Router::class);
            $router->aliasMiddleware('vauth', VAuthMiddleware::class);
        }

        // Auto-register Livewire components
        if (config('core.auto_register_livewire', true) && class_exists(Livewire::class)) {
            try {
                Livewire::component('core-auth-redirect', AuthRedirect::class);
                Livewire::component('core-auth-callback', AuthCallback::class);
                Livewire::component('core-auth-status', AuthStatus::class);
            } catch (\Exception $e) {
                // Livewire not available, skip component registration
            }
        }

        // Auto-register routes
        if (config('core.auto_register_routes', true)) {
            $this->registerRoutes();
        }

        // Auto-configure auth settings if not already configured
        $this->autoConfigureAuth();

        // Auto-configure session settings
        if (config('core.auto_configure_session', true)) {
            $this->autoConfigureSession();
        }

        // Auto-configure events
        if (config('core.auto_register_events', true)) {
            $this->autoConfigureEvents();
        }
    }

    public function registeringPackage()
    {
        // Register clean architecture bindings
        $this->app->register(CleanArchitectureServiceProvider::class);

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

    /**
     * Auto-register the auth guard in Laravel's auth configuration
     */
    protected function registerAuthGuard(): void
    {
        $guardName = config('core.guard_name', 'core');

        // Register the custom guard driver
        Auth::extend($guardName, function ($app) {
            return new CoreAuthGuard(
                new AuthCoreAuthUserProvider,
                $app['session.store']
            );
        });
    }

    /**
     * Auto-register the user provider driver
     */
    protected function registerUserProvider(): void
    {
        // Register the user provider driver
        Auth::provider('core', function ($app, array $config) {
            return new AuthCoreAuthUserProvider;
        });

        // Auto-add provider configuration
        $guardName = config('core.guard_name', 'core');
        config([
            "auth.guards.{$guardName}" => [
                'driver' => $guardName,
                'provider' => 'core_users',
            ],
            'auth.providers.core_users' => [
                'driver' => 'core',
                'model' => \VoxDev\Core\Auth\CoreAuthUser::class,
            ],
        ]);
    }

    /**
     * Auto-configure authentication settings for seamless integration
     */
    protected function autoConfigureAuth(): void
    {
        $guardName = config('core.guard_name', 'core');

        // Auto-configure middleware groups
        if (config('core.auto_register_middleware', true)) {
            $this->autoConfigureMiddleware();
        }

        // Auto-configure default routes if enabled
        if (config('core.auto_register_routes', true)) {
            $this->autoConfigureRoutes();
        }

        // Auto-configure Filament if it exists and is enabled
        if (config('core.auto_configure_filament', true)) {
            $this->autoConfigureFilament($guardName);
        }

        // Auto-configure route protection patterns
        $this->autoConfigureRouteProtection();
    }

    /**
     * Register authentication routes
     */
    protected function registerAuthRoutes(): void
    {
        // This ensures auth routes are available without manual registration
        // Routes are loaded from the package's routes/web.php file
    }

    /**
     * Auto-register package routes
     */
    protected function registerRoutes(): void
    {
        // Register routes automatically - no need for manual route file inclusion
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    /**
     * Auto-configure middleware groups
     */
    protected function autoConfigureMiddleware(): void
    {
        // Add middleware alias for easy usage
        $router = $this->app->make(Router::class);

        // Create middleware group for OAuth protected routes
        if (! $router->hasMiddlewareGroup('auth.oauth')) {
            $router->middlewareGroup('auth.oauth', [
                'web',
                'vauth',
            ]);
        }

        // Create middleware group for auto-protected routes if patterns are defined
        $protectedPatterns = config('core.protected_route_patterns', []);
        if (! empty($protectedPatterns) && ! $router->hasMiddlewareGroup('auto.oauth')) {
            $router->middlewareGroup('auto.oauth', [
                'web',
                'vauth',
            ]);
        }
    }

    /**
     * Auto-configure route patterns
     */
    protected function autoConfigureRoutes(): void
    {
        // Add route model binding patterns if needed
        $router = $this->app->make(Router::class);

        // Auto-configure route caching considerations
        // Register additional route patterns for OAuth flows
        $router->pattern('oauth_state', '[a-zA-Z0-9]+');
        $router->pattern('oauth_code', '[a-zA-Z0-9\-\_]+');
    }

    /**
     * Auto-configure Filament integration
     */
    protected function autoConfigureFilament(string $guardName): void
    {
        // Only configure if Filament is actually installed and available
        // Check for multiple potential Filament classes to ensure it's really installed
        if (
            ! class_exists(\Filament\FilamentServiceProvider::class) &&
            ! class_exists(\Filament\Facades\Filament::class) &&
            ! class_exists(\Filament\Panel::class)
        ) {
            return;
        }

        // Auto-configure Filament to use our auth guard if no custom config exists
        if (! config('filament.auth.guard')) {
            config([
                'filament.auth.guard' => $guardName,
                'filament.auth.pages.login' => null, // Disable default login, use OAuth
            ]);
        }
    }

    /**
     * Auto-configure route protection patterns
     */
    protected function autoConfigureRouteProtection(): void
    {
        // Auto-apply OAuth protection to specified route patterns
        $protectedPatterns = config('core.protected_route_patterns', []);
        $excludePatterns = config('core.exclude_route_patterns', []);

        // Log the patterns for debugging if needed
        if (! empty($protectedPatterns)) {
            logger()->debug('OAuth route protection patterns configured', [
                'protected' => $protectedPatterns,
                'excluded' => $excludePatterns,
            ]);
        }
    }

    /**
     * Auto-configure session and cookie settings for OAuth
     */
    protected function autoConfigureSession(): void
    {
        // Ensure session configuration supports OAuth flows
        $sessionConfig = config('session');

        // Auto-configure session settings for OAuth compatibility
        if ($sessionConfig['driver'] === 'file') {
            // Recommend database sessions for OAuth in production
            if (! app()->environment('local')) {
                logger()->info('Core SDK: Consider using database sessions for OAuth in production');
            }
        }

        // Auto-configure cookie settings for OAuth security
        if (! config('core.cookie_secure')) {
            config([
                'core.cookie_secure' => app()->environment('production'),
                'core.cookie_same_site' => 'lax',
            ]);
        }

        // Auto-configure session lifetime for OAuth compatibility
        if (config('session.lifetime') <= 120) { // If default or short lifetime
            config(['session.lifetime' => 720]); // 12 hours default for OAuth
        }
    }

    /**
     * Auto-register authentication event listeners
     */
    protected function autoConfigureEvents(): void
    {
        // Auto-register event listeners for OAuth events
        if (config('core.auto_register_events', true)) {
            $this->app['events']->listen(
                \VoxDev\Core\Events\UserLoggedIn::class,
                function ($event) {
                    logger()->info('OAuth user logged in', [
                        'user_id' => $event->user->id ?? 'unknown',
                        'email' => $event->user->email ?? 'unknown',
                    ]);
                }
            );

            $this->app['events']->listen(
                \VoxDev\Core\Events\UserLoggedOut::class,
                function ($event) {
                    logger()->info('OAuth user logged out');
                }
            );
        }
    }
}
