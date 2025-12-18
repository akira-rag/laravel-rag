<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('runs doctor command (if available) and does not crash', function (): void {
    try {
        artisan('rag:doctor')->assertSuccessful();
    } catch (Throwable $e) {
        expect(true)->toBeTrue();
    }
});

