<?php

declare(strict_types=1);

use Tests\Helpers\FakeTenantResolver;
use function Pest\Laravel\artisan;

it('creates encrypted compressed backups and prunes old ones', function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    config()->set('rag.tenancy.enabled', false);

    $dir = storage_path('app/rag/backups/single');
    @mkdir($dir, 0777, true);

    // create some older backups
    foreach (range(1, 3) as $i) {
        file_put_contents($dir.'/backup-20250101-0'.$i.'.json.gz', 'x');
    }

    $out = $dir.'/backup.json';
    artisan('rag:backup', [
        '--output' => $out,
        '--retain' => 2,
    ])->assertSuccessful();

    // Expect 2 retained old backups + the new backup = 3 files
    expect(glob($dir.'/*.gz'))->toHaveCount(3);
});

it('isolates backups per tenant in multi-tenant', function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    config()->set('rag.tenancy.enabled', true);
    config()->set('rag.tenancy.resolver', FakeTenantResolver::class);

    app()->bind(FakeTenantResolver::class, fn () => new FakeTenantResolver('tenant-A'));
    artisan('rag:backup')->assertSuccessful();

    app()->bind(FakeTenantResolver::class, fn () => new FakeTenantResolver('tenant-B'));
    artisan('rag:backup')->assertSuccessful();

    expect(is_dir(storage_path('app/rag/backups/tenant-A')))->toBeTrue();
    expect(is_dir(storage_path('app/rag/backups/tenant-B')))->toBeTrue();
});
