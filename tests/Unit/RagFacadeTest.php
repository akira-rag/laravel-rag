<?php

declare(strict_types=1);

use Akira\Rag\Facades\Rag as RagFacade;

it('resolves the underlying service via the facade', function (): void {
    expect(RagFacade::getFacadeRoot())
        ->toBeInstanceOf(Akira\Rag\RagService::class);
});
