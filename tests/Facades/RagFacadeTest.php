<?php

declare(strict_types=1);

use Rag\Rag\Facades\Rag as RagFacade;

it('resolves the underlying class via the facade', function (): void {
    expect(RagFacade::getFacadeRoot())
        ->toBeInstanceOf(Rag\Rag\Rag::class);
});
