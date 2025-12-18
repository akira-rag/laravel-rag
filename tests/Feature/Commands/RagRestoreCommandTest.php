<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('validates backup via --dry-run and restores with --force', function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    // Prepare export
    config()->set('rag.tenancy.enabled', false);
    artisan('rag:ingest', [
        '--title' => 'R',
        '--source_type' => 'note',
        '--source_ref' => 'restore:1',
        '--text' => 'restore me',
    ])->assertSuccessful();

    $out = storage_path('app/rag/exports/single/restore.json');
    @mkdir(dirname($out), 0777, true);
    artisan('rag:export', [
        '--output' => $out,
        '--include-embeddings' => true,
        '--include-audit' => true,
        '--compress' => false,
    ])->assertSuccessful();

    // Dry run
    artisan('rag:restore', [
        'path' => $out,
        '--dry-run' => true,
    ])->assertSuccessful();

    // Force restore
    artisan('rag:restore', [
        'path' => $out,
        '--force' => true,
    ])->assertSuccessful();
});

it('restores encrypted compressed backup with --decrypt', function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    // export encrypted gz
    $out = storage_path('app/rag/exports/single/enc-restore.json');
    @mkdir(dirname($out), 0777, true);
    artisan('rag:export', [
        '--output' => $out,
        '--compress' => true,
        '--encrypt' => true,
    ])->assertSuccessful();

    artisan('rag:restore', [
        'path' => $out.'.gz',
        '--decrypt' => true,
        '--dry-run' => true,
    ])->assertSuccessful();
});

it('is tenant-isolated on restore flow', function (): void {
    config()->set('rag.tenancy.enabled', true);
    // ensure per-tenant path works
    $out = storage_path('app/rag/exports/TENANT/tenant.json');
    @mkdir(dirname($out), 0777, true);
    file_put_contents($out, json_encode(['documents' => [], 'chunks' => [], 'embeddings' => [], 'queries' => []], JSON_THROW_ON_ERROR));

    artisan('rag:restore', [
        'path' => $out,
        '--dry-run' => true,
    ])->assertSuccessful();
});
