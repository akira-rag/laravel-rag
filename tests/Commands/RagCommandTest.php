<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('runs the laravel-rag command successfully', function (): void {
    artisan('laravel-rag')
        ->expectsOutputToContain('All done')
        ->assertSuccessful();
});
