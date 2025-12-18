<?php

declare(strict_types=1);

use Akira\Rag\Observability\LogMetricsRecorder;
use Akira\Rag\Observability\MetricsRecorder;

it('binds MetricsRecorder to LogMetricsRecorder via provider', function (): void {
    $rec = resolve(MetricsRecorder::class);
    expect($rec)->toBeInstanceOf(LogMetricsRecorder::class);
});
