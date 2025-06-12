<?php

namespace VoxDev\Core\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
        if (! app()->routesAreCached()) {
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
     * Get authenticated user from Core SDK
     */
    protected function getCoreUser()
    {
        return auth()->guard($this->getCoreGuard())->user();
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
    protected function requireCoreAuth()
    {
        if (! $this->isCoreAuthenticated()) {
            return redirect()->route('core.auth.login');
        }

        return $this->getCoreUser();
    }
}
