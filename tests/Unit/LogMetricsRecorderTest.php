<?php

declare(strict_types=1);

use Akira\Rag\Observability\LogMetricsRecorder;
use Tests\Support\FakeLogger;

it('records increment and timing to logger', function (): void {
    $logger = new FakeLogger();
    $recorder = new LogMetricsRecorder($logger);

    $recorder->increment('rag.ingest.count', 2, ['tenant' => 't1']);
    $recorder->timing('rag.query.duration_ms', 12.5, ['tenant' => 't1']);

    expect($logger->logs)->toHaveCount(2)
        ->and($logger->logs[0]['message'])->toBe('metric.increment')
        ->and($logger->logs[0]['context']['metric'])->toBe('rag.ingest.count')
        ->and($logger->logs[0]['context']['value'])->toBe(2)
        ->and($logger->logs[1]['message'])->toBe('metric.timing')
        ->and($logger->logs[1]['context']['metric'])->toBe('rag.query.duration_ms')
        ->and($logger->logs[1]['context']['ms'])->toBe(12.5);
});
