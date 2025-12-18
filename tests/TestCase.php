<?php

declare(strict_types=1);

namespace Akira\Rag\Tests;

use Akira\Rag\RagServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Akira\\Rag\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // Run package migrations
        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    final public function getEnvironmentSetUp($app): void
    {
        $app->make(\Illuminate\Contracts\Config\Repository::class)->set('database.default', 'testing');
        $app->make(\Illuminate\Contracts\Config\Repository::class)->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            RagServiceProvider::class,
        ];
    }
}
