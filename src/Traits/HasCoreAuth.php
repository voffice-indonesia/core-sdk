<?php

namespace VoxDev\Core\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use VoxDev\Core\Domain\Entities\User as DomainUser;
use VoxDev\Core\Infrastructure\Auth\AuthenticatableUser;

/**
 * Trait HasCoreAuth
 *
 * This trait provides helper methods for integrating Core SDK authentication
 * into your Laravel application using clean architecture principles.
 * Use this trait in your controllers or models to easily work with Core SDK authentication.
 *
 * Usage example:
 *
 * ```php
 * class ProfileController extends Controller
 * {
 *     use HasCoreAuth;
 *
 *     public function show()
 *     {
 *         $user = $this->getCoreUser();
 *         $domainUser = $this->getCoreDomainUser();
 *         return view('profile.show', compact('user', 'domainUser'));
 *     }
 * }
 * ```
 */
trait HasCoreAuth
{
    /**
     * Configure Core SDK authentication for this model/controller
     */
    public function initializeCoreAuth(): void
    {
        // Set default guard for this model/controller
        config(['auth.defaults.guard' => config('core.guard_name', 'core')]);
    }

    /**
     * Auto-configure authentication for the entire application
     * Call this method in your AppServiceProvider or wherever you want
     * to make Core SDK the default authentication system
     */
    public static function configureAsDefault(): void
    {
        $guardName = config('core.guard_name', 'core');

        // Set Core SDK as the default authentication guard
        config(['auth.defaults.guard' => $guardName]);

        // Register default authentication routes if not already registered
        if (! app('router')->getRoutes()->hasNamedRoute('login')) {
            Route::middleware('web')->group(function () use ($guardName) {
                Route::get('/login', function () {
                    return view('core::auth.login');
                })->name('login');

                Route::get('/dashboard', function () {
                    return view('core::dashboard');
                })->middleware("auth:{$guardName}")->name('dashboard');
            });
        }
    }

    /**
     * Get the Core SDK auth guard
     */
    protected function getCoreGuard(): string
    {
        return config('core.guard_name', 'core');
    }

    /**
     * Get authenticated user from Core SDK (Laravel Authenticatable)
     */
    protected function getCoreUser(): ?AuthenticatableUser
    {
        $user = auth()->guard($this->getCoreGuard())->user();

        return $user instanceof AuthenticatableUser ? $user : null;
    }

    /**
     * Get the domain user entity
     */
    protected function getCoreDomainUser(): ?DomainUser
    {
        $user = $this->getCoreUser();

        return $user?->getDomainUser();
    }

    /**
     * Check if user is authenticated via Core SDK
     */
    protected function isCoreAuthenticated(): bool
    {
        return auth()->guard($this->getCoreGuard())->check();
    }

    /**
     * Apply Core authentication to specific routes
     */
    protected function applyCoreAuth(): void
    {
        $guardName = $this->getCoreGuard();

        // Apply vauth middleware to current controller
        $this->middleware("auth:{$guardName}");
        $this->middleware('vauth');
    }

    /**
     * Redirect to Core authentication if not authenticated
     */
    protected function requireCoreAuth(): AuthenticatableUser|string
    {
        if (! $this->isCoreAuthenticated()) {
            return redirect()->route('core.auth.login');
        }

        return $this->getCoreUser();
    }

    /**
     * Get user attribute using clean architecture
     */
    protected function getCoreUserAttribute(string $key): mixed
    {
        $domainUser = $this->getCoreDomainUser();

        return $domainUser?->getAttribute($key);
    }

    /**
     * Check if user has specific attribute
     */
    protected function coreUserHasAttribute(string $key): bool
    {
        $domainUser = $this->getCoreDomainUser();

        return $domainUser?->hasAttribute($key) ?? false;
    }
}
