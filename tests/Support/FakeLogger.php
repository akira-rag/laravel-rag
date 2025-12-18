<?php

declare(strict_types=1);

namespace Tests\Support;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class FakeLogger implements LoggerInterface
{
    /** @var array<int,array{level:string,message:string,context:array<string,mixed>}> */
    public array $logs = [];

    public function emergency($message, array $context = []): void { $this->log(LogLevel::EMERGENCY, (string) $message, $context); }
    public function alert($message, array $context = []): void { $this->log(LogLevel::ALERT, (string) $message, $context); }
    public function critical($message, array $context = []): void { $this->log(LogLevel::CRITICAL, (string) $message, $context); }
    public function error($message, array $context = []): void { $this->log(LogLevel::ERROR, (string) $message, $context); }
    public function warning($message, array $context = []): void { $this->log(LogLevel::WARNING, (string) $message, $context); }
    public function notice($message, array $context = []): void { $this->log(LogLevel::NOTICE, (string) $message, $context); }
    public function info($message, array $context = []): void { $this->log(LogLevel::INFO, (string) $message, $context); }
    public function debug($message, array $context = []): void { $this->log(LogLevel::DEBUG, (string) $message, $context); }

    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => (string) $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}

