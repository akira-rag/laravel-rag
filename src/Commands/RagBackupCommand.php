<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

use function Laravel\Prompts\warning;

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
        $tenantId = $tenant->current();
        Log::info('[rag:backup] Starting backup', ['tenant' => $tenantId]);

        $suggestedDirectory = storage_path('app/rag/backups/'.($tenant->enabled() ? ($tenant->current() ?? 'unknown')
                : 'single'));
        $outputOption = $this->option('output');
        $backupOutputPath = is_string($outputOption) && $outputOption !== ''
            ? $outputOption
            : $suggestedDirectory.'/backup-'.Date::now()->format('Ymd-His').'.json';

        $retentionDays = (int) $this->option('retain');

        if ($retentionDays < 1) {
            Log::warning('[rag:backup] Invalid retention days', ['tenant' => $tenantId, 'retain' => $retentionDays]);
            warning('Retention must be at least 1 day');

            return self::INVALID;
        }

        $noEncryption = $this->option('no-encryption');
        $shouldEncrypt = $noEncryption !== true;

        try {
            $exportParams = [
                '--format' => 'json',
                '--output' => $backupOutputPath,
                '--compress' => true,
                '--include-embeddings' => true,
                '--include-audit' => true,
            ];
            if ($shouldEncrypt) {
                $exportParams['--encrypt'] = true;
            }

            $exportResult = $this->call('rag:export', $exportParams);

            if ($exportResult !== self::SUCCESS) {
                Log::error('[rag:backup] Export command failed', ['tenant' => $tenantId]);
                warning('Backup export failed');

                return self::FAILURE;
            }

            $files->ensureDirectoryExists(dirname($backupOutputPath));
            $backupFilesList = collect($files->files(dirname($backupOutputPath)))
                ->filter(fn (SplFileInfo $fileInfo): bool => str_starts_with($files->name($fileInfo->getPathname()), 'backup-'))
                ->sortByDesc(fn (SplFileInfo $fileInfo): int => $files->lastModified($fileInfo->getPathname()))
                ->values();

            if ($backupFilesList->count() > $retentionDays) {
                $deletedCount = 0;
                $backupFilesList->slice($retentionDays)->each(function (SplFileInfo $fileInfo) use ($files, &$deletedCount): void {
                    $files->delete($fileInfo->getPathname());
                    $deletedCount++;
                });
                Log::info('[rag:backup] Pruned old backups', ['tenant' => $tenantId, 'deleted' => $deletedCount]);
            }

            Log::info('[rag:backup] Backup completed', [
                'tenant' => $tenantId,
                'output' => $backupOutputPath,
                'encrypted' => $shouldEncrypt,
            ]);

            $this->newLine();
            $this->components->twoColumnDetail('Output', $backupOutputPath.(str_ends_with($backupOutputPath, '.gz') ? '' : '.gz'));
            $this->components->twoColumnDetail('Encryption', $shouldEncrypt ? 'enabled' : 'disabled');
            $this->components->twoColumnDetail('Retention', (string) $retentionDays);

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('[rag:backup] Unexpected error', ['tenant' => $tenantId, 'error' => $e->getMessage()]);
            warning('Backup failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
