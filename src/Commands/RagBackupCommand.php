<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;


final class RagBackupCommand extends Command
{
    protected $signature = 'rag:backup'
        .' {--output=}'
        .' {--retain=7}'
        .' {--no-encryption : Disable encryption explicitly}';

    protected $description = 'Create encrypted backups of the RAG knowledge base';

    public function handle(TenantContext $tenant, Filesystem $files): int
    {
        $suggestDir = storage_path('app/rag/backups/'.($tenant->enabled() ? ($tenant->current() ?? 'unknown') : 'single'));
        $output = (string) ($this->option('output') ?? $suggestDir.'/backup-'.Carbon::now()->format('Ymd-His').'.json');
        $retain = (int) ($this->option('retain') ?? 7);
        $encrypt = ! (bool) $this->option('no-encryption');

        // Call export with compression and encryption by default
        $params = [
            '--format' => 'json',
            '--output' => $output,
            '--compress' => true,
        ];
        if ($encrypt) {
            $params['--encrypt'] = true;
        }

        $this->call('rag:export', $params);

        // prune old backups
        $files->ensureDirectoryExists(dirname($output));
        $filesList = collect($files->files(dirname($output)))
            ->filter(fn ($f) => str_starts_with($files->name($f->getPathname()), 'backup-'))
            ->sortByDesc(fn ($f) => $files->lastModified($f->getPathname()))
            ->values();

        if ($filesList->count() > $retain) {
            $filesList->slice($retain)->each(fn ($f) => $files->delete($f->getPathname()));
        }

        $this->newLine();
        $this->components->twoColumnDetail('Output', $output.(str_ends_with($output, '.gz') ? '' : '.gz'));
        $this->components->twoColumnDetail('Encryption', $encrypt ? 'enabled' : 'disabled');
        $this->components->twoColumnDetail('Retention', (string) $retain);

        return self::SUCCESS;
    }
}


