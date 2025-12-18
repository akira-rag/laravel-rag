<?php

declare(strict_types=1);

namespace Akira\Rag\Observability;

use Psr\Log\LoggerInterface;

final readonly class LogMetricsRecorder implements MetricsRecorder
{
    public function __construct(private LoggerInterface $logger) {}

    /**
     * @param  array<string,scalar|null>  $tags
     */
    public function increment(string $metric, int $by = 1, array $tags = []): void
    {

        $this->logger->info('metric.increment', [
            'metric' => $metric,
            'value' => $by,
            'tags' => $tags,
        ]);
    }

    /**
     * @param  array<string,scalar|null>  $tags
     */
    public function timing(string $metric, float $milliseconds, array $tags = []): void
    {

        $this->logger->info('metric.timing', [
            'metric' => $metric,
            'ms' => $milliseconds,
            'tags' => $tags,
        ]);
    }
}
