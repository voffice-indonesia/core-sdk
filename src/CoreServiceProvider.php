<?php

namespace VoxDev\Core;

use Illuminate\Support\Facades\Auth;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VoxDev\Core\App\Auth\CoreAuthGuard;
use VoxDev\Core\App\Auth\CoreAuthUserProvider;
use VoxDev\Core\Commands\CoreSetupCommand;

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
            ->hasCommand(CoreSetupCommand::class);
    }

    public function bootingPackage()
    {
        Auth::extend(config('core.guard_name'), function($app) {
            return new CoreAuthGuard(Auth::createUserProvider(CoreAuthUserProvider::class), $app->make('request'));
        });
    }
}
