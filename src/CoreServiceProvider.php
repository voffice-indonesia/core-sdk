<?php

namespace VoxDev\Core;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VoxDev\Core\Commands\CoreCommand;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_core_sdk_table')
            ->hasCommand(CoreCommand::class);
    }
}
