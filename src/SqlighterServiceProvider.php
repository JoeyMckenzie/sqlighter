<?php

declare(strict_types=1);

namespace JoeyMcKenzie\Sqlighter;

use JoeyMcKenzie\Sqlighter\Commands\RunDatabaseBackup;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class SqlighterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sqlighter')
            ->hasConfigFile()
            ->hasCommand(RunDatabaseBackup::class);
    }
}
