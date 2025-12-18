<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('runs the install command non-interactively', function (): void {
    artisan('rag:install --force')
        ->expectsOutputToContain('Installer')
        ->expectsOutputToContain('Install complete.')
        ->assertSuccessful();
});
