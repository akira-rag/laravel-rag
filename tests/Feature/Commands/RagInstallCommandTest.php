<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('runs rag:install non-interactively and is idempotent', function (): void {
    putenv('RAG_INSTALL_DRY_RUN=1');

    artisan('rag:install --force --run-migrate')
        ->expectsOutputToContain('Installer')
        ->expectsOutputToContain('Install complete.')
        ->assertSuccessful();

    // Run again (idempotent)
    artisan('rag:install --force --run-migrate')
        ->expectsOutputToContain('Install complete.')
        ->assertSuccessful();
});
