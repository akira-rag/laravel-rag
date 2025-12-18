<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Finder\SplFileInfo;

final class RagBackupCommand extends Command
{
    protected $signature
        = 'rag:backup'
        .' {--output=}'
        .' {--retain=7}'
        .' {--no-encryption : Disable encryption explicitly}';

    protected $description = 'Create encrypted backups of the RAG knowledge base';

    public function handle(TenantContext $tenant, Filesystem $files): int
    {

        $suggestDir = storage_path('app/rag/backups/'.($tenant->enabled() ? ($tenant->current() ?? 'unknown')
                : 'single'));
        $optOutput = $this->option('output');
        $output = is_string($optOutput) && $optOutput !== ''
            ? $optOutput
            : $suggestDir.'/backup-'.Date::now()->format('Ymd-His').'.json';

        $retain = (int) $this->option('retain');

        //        $retain = is_int($optRetain) || (is_string($optRetain) && is_numeric($optRetain))
        //            ? (int) $optRetain
        //            : 7;

        $noEncryption = $this->option('no-encryption');
        $encrypt = $noEncryption !== true;

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
            ->filter(fn (SplFileInfo $f): bool => str_starts_with($files->name($f->getPathname()), 'backup-'))
            ->sortByDesc(fn (SplFileInfo $f): int => $files->lastModified($f->getPathname()))
            ->values();

        if ($filesList->count() > $retain) {
            $filesList->slice($retain)->each(fn (SplFileInfo $f) => $files->delete($f->getPathname()));
        }

        $this->newLine();
        $this->components->twoColumnDetail('Output', $output.(str_ends_with($output, '.gz') ? '' : '.gz'));
        $this->components->twoColumnDetail('Encryption', $encrypt ? 'enabled' : 'disabled');
        $this->components->twoColumnDetail('Retention', (string) $retain);

        return self::SUCCESS;
    }
}
