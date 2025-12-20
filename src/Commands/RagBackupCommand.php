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

        $suggestedDirectory = storage_path('app/rag/backups/'.($tenant->enabled() ? ($tenant->current() ?? 'unknown')
                : 'single'));
        $outputOption = $this->option('output');
        $backupOutputPath = is_string($outputOption) && $outputOption !== ''
            ? $outputOption
            : $suggestedDirectory.'/backup-'.Date::now()->format('Ymd-His').'.json';

        $retentionDays = (int) $this->option('retain');

        //        $retentionDays = is_int($optRetain) || (is_string($optRetain) && is_numeric($optRetain))
        //            ? (int) $optRetain
        //            : 7;

        $noEncryption = $this->option('no-encryption');
        $shouldEncrypt = $noEncryption !== true;

        // Call export with compression and encryption by default
        $exportParams = [
            '--format' => 'json',
            '--output' => $backupOutputPath,
            '--compress' => true,
        ];
        if ($shouldEncrypt) {
            $exportParams['--encrypt'] = true;
        }

        $this->call('rag:export', $exportParams);

        // prune old backups
        $files->ensureDirectoryExists(dirname($backupOutputPath));
        $backupFilesList = collect($files->files(dirname($backupOutputPath)))
            ->filter(fn (SplFileInfo $fileInfo): bool => str_starts_with($files->name($fileInfo->getPathname()), 'backup-'))
            ->sortByDesc(fn (SplFileInfo $fileInfo): int => $files->lastModified($fileInfo->getPathname()))
            ->values();

        if ($backupFilesList->count() > $retentionDays) {
            $backupFilesList->slice($retentionDays)->each(fn (SplFileInfo $fileInfo) => $files->delete($fileInfo->getPathname()));
        }

        $this->newLine();
        $this->components->twoColumnDetail('Output', $backupOutputPath.(str_ends_with($backupOutputPath, '.gz') ? '' : '.gz'));
        $this->components->twoColumnDetail('Encryption', $shouldEncrypt ? 'enabled' : 'disabled');
        $this->components->twoColumnDetail('Retention', (string) $retentionDays);

        return self::SUCCESS;
    }
}
