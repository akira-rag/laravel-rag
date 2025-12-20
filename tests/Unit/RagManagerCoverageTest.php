<?php

declare(strict_types=1);

use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Exceptions\InvalidQuestionException;
use Akira\Rag\RagManager;

it('rejects empty question', function (): void {

    expect(fn () => resolve(RagManager::class)->ask(''))
        ->toThrow(InvalidQuestionException::class);
});

it('rejects tenant_id in ingest payload', function (): void {

    expect(fn () => resolve(RagManager::class)->ingest([
        'title' => 't', 'source_type' => 's', 'source_ref' => 'r', 'content' => 'c', 'tenant_id' => 'bad',
    ]))->toThrow(InvalidPayload::class);
});
