<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

final class RagInstallCommand extends Command
{
    protected $signature = 'rag:install {--force : Run without prompts (non-interactive)}';

    protected $description = 'Interactive installer for Akira RAG';

    public function handle(Filesystem $files): int
    {
        intro('Akira RAG Installer');

        $nonInteractive = (bool) $this->option('force');
        $dryRun = (bool) env('RAG_INSTALL_DRY_RUN', app()->environment('testing'));

        $publishConfig = $nonInteractive || confirm('Publish configuration file?');
        if ($publishConfig) {
            if ($dryRun) {
                info('Skipping config publish in testing environment.');
            } else {
                spin(fn (): bool => $this->callSilent('vendor:publish', ['--tag' => 'laravel-rag-config']) === 0, 'Publishing config...');
            }
        }

        $publishMigrations = $nonInteractive || confirm('Publish migrations?');
        if ($publishMigrations) {
            if ($dryRun) {
                info('Skipping migrations publish in testing environment.');
            } else {
                spin(fn (): bool => $this->callSilent('vendor:publish', ['--tag' => 'laravel-rag-migrations']) === 0, 'Publishing migrations...');
            }
        }

        $runMigrate = !$nonInteractive && confirm('Run database migrations now?', false);
        if ($runMigrate) {
            if ($dryRun) {
                info('Skipping migrate in testing environment.');
            } else {
                $this->call('migrate');
            }
        }

        $enableTenancy = !$nonInteractive && confirm('Enable multi-tenant mode (tenancy.enabled = true)?', false);
        if ($enableTenancy) {
            $path = config_path('rag.php');
            if (! $files->exists($path)) {
                warning('Config file not found. Publish the config first.');
            } else {
                $contents = $files->get($path);
                $updated = preg_replace("/('enabled'\s*=>\s*)false/", '\\1true', $contents, 1) ?? $contents;
                $files->put($path, $updated);
                info('Enabled tenancy in config/rag.php');
            }
        }

        note('If you find this useful, please consider starring the repo:');
        info('https://github.com/akira-rag/laravel-rag');

        $star = $nonInteractive ? false : confirm('Open the GitHub repository now to leave a star?', false);
        if ($star && ! $dryRun) {
            $url = 'https://github.com/akira-rag/laravel-rag';
            $this->openUrl($url);
        }

        info('Install complete.');

        return self::SUCCESS;
    }

    private function openUrl(string $url): void
    {
        $cmd = PHP_OS_FAMILY === 'Darwin' ? 'open' : (PHP_OS_FAMILY === 'Windows' ? 'start' : 'xdg-open');
        try {
            @pclose(@popen($cmd.' '.escapeshellarg($url), 'r'));
        } catch (Throwable) {
            // ignore
        }
    }
}
