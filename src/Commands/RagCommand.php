<?php

declare(strict_types=1);

namespace Rag\Rag\Commands;

use Illuminate\Console\Command;

final class RagCommand extends Command
{
    public $signature = 'laravel-rag';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
