<?php

namespace VoxDev\Core\Infrastructure\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use VoxDev\Core\Application\UseCases\AuthenticateUser;
use VoxDev\Core\Application\UseCases\RefreshUserToken;
use VoxDev\Core\Domain\Repositories\TokenRepositoryInterface;
use VoxDev\Core\Domain\Repositories\UserRepositoryInterface;
use VoxDev\Core\Domain\Services\OAuthServiceInterface;
use VoxDev\Core\Infrastructure\Guards\CleanArchitectureGuard;
use VoxDev\Core\Infrastructure\Repositories\CookieTokenRepository;
use VoxDev\Core\Infrastructure\Repositories\SessionUserRepository;
use VoxDev\Core\Infrastructure\Services\HttpOAuthService;

/**
 * Clean Architecture Service Provider
 *
 * Binds all clean architecture dependencies.
 */
class CleanArchitectureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(UserRepositoryInterface::class, SessionUserRepository::class);
        $this->app->bind(TokenRepositoryInterface::class, CookieTokenRepository::class);

        // Bind service interfaces to implementations
        $this->app->bind(OAuthServiceInterface::class, function ($app) {
            return new HttpOAuthService(config('core.url'));
        });

        // Register use cases
        $this->app->bind(AuthenticateUser::class);
        $this->app->bind(RefreshUserToken::class);
    }

    public function boot(): void
    {
        // Register custom auth guard
        Auth::extend('clean-oauth', function ($app, $name, $config) {
            return new CleanArchitectureGuard(
                $app->make(UserRepositoryInterface::class),
                $app['session.store']
            );
        });

        // Register user provider (if needed for compatibility)
        Auth::provider('clean-oauth', function ($app, $config) {
            return new class implements \Illuminate\Contracts\Auth\UserProvider
            {
                public function retrieveById($identifier)
                {
                    return null;
                }

                public function retrieveByToken($identifier, $token)
                {
                    return null;
                }

                public function updateRememberToken(\Illuminate\Contracts\Auth\Authenticatable $user, $token) {}

                public function retrieveByCredentials(array $credentials)
                {
                    return null;
                }

                public function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials)
                {
                    return false;
                }

                public function rehashPasswordIfRequired(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials, bool $force = false) {}
            };
        });
    }
}
