<?php

declare(strict_types=1);

namespace Akira\Rag;

use Akira\Rag\Commands\RagInstallCommand;
use Akira\Rag\Commands\RagIngestCommand;
use Akira\Rag\Commands\RagReembedCommand;
use Akira\Rag\Commands\RagStatsCommand;
use Akira\Rag\Commands\RagExportCommand;
use Akira\Rag\Commands\RagBackupCommand;
use Akira\Rag\Commands\RagRestoreCommand;
use Akira\Rag\Commands\RagImportPdfCommand;
use Akira\Rag\Tenant\TenantContext;
use Akira\Rag\Observability\MetricsRecorder;
use Akira\Rag\Observability\LogMetricsRecorder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class RagServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-rag')
            ->hasConfigFile('rag')
            ->hasMigrations('2025_01_01_000000_create_rag_schema')
            ->hasCommands([
                RagInstallCommand::class,
                RagIngestCommand::class,
                RagReembedCommand::class,
                RagStatsCommand::class,
                RagExportCommand::class,
                RagBackupCommand::class,
                RagRestoreCommand::class,
                RagImportPdfCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(RagManager::class);
        $this->app->singleton('akira.rag', fn ($app): RagService => new RagService($app->make(RagManager::class)));
        $this->app->bind(MetricsRecorder::class, function ($app): MetricsRecorder {
            $recorder = config('rag.observability.metrics.recorder');
            return new $recorder($app->make('log')->channel(config('rag.observability.logging.channel', 'rag')));
        });
    }
}
