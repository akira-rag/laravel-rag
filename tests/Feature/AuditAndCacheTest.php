<?php

declare(strict_types=1);

use Akira\Rag\Facades\Rag;

it('creates audit records when enabled', function (): void {
    config()->set('rag.audit.enabled', true);
    Rag::ingest([
        'title' => 'Audit',
        'source_type' => 's',
        'source_ref' => 'ref:1',
        'content' => 'audit trail',
    ]);

    $result = Rag::ask('q1');
    $query = Akira\Rag\Models\RagQuery::query()->find($result['query_id']);
    expect($query)->not()->toBeNull();
    expect($query->chunks()->count())->toBeGreaterThan(0);
});

it('does not create audit records when disabled', function (): void {
    config()->set('rag.audit.enabled', false);
    Rag::ingest([
        'title' => 'Audit2',
        'source_type' => 's',
        'source_ref' => 'ref:2',
        'content' => 'audit trail 2',
    ]);

    $result = Rag::ask('q2');
    $exists = Akira\Rag\Models\RagQuery::query()->find($result['query_id']);
    expect($exists)->toBeNull();
});
