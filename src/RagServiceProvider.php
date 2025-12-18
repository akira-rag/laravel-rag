<?php

declare(strict_types=1);

namespace Rag\Rag;

use Rag\Rag\Commands\RagCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class RagServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-rag')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_rag_table')
            ->hasCommand(RagCommand::class);
    }
}
