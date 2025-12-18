<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('enables tenancy flag update when config exists', function (): void {
    putenv('RAG_INSTALL_DRY_RUN=1');

    $cfg = config_path('rag.php');
    @mkdir(dirname($cfg), 0777, true);
    file_put_contents($cfg, "<?php return ['tenancy' => ['enabled' => false]];");

    artisan('rag:install --force --with-tenancy')->assertSuccessful();

    $content = file_get_contents($cfg);
    expect($content)->toContain("'enabled' => true");
});

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
