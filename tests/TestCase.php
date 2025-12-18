<?php

declare(strict_types=1);

namespace Rag\Rag\Tests;

use Akira\Debugger\DebuggerServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Rag\Rag\RagServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Rag\\Rag\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    final public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }

    protected function getPackageProviders($app): array
    {
        return [
            RagServiceProvider::class,
            DebuggerServiceProvider::class,
        ];
    }
}
