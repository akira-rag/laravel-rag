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
    protected $signature
        = 'rag:install'
        .' {--force : Run without prompts (non-interactive)}'
        .' {--with-tenancy : Enable multi-tenant mode when running with --force}'
        .' {--run-migrate : Run database migrations when running with --force}'
        .' {--star : Open the GitHub repository to leave a star when running with --force}';

    protected $description = 'Interactive installer for Akira RAG';

    public function handle(Filesystem $files): int
    {

        intro('Akira RAG Installer');

        $isNonInteractive = (bool) $this->option('force');

        $isDryRun = (bool) app()->environment('testing');

        $shouldPublishConfig = $isNonInteractive || (fn (): bool => confirm('Publish configuration file?'))();

        if ($shouldPublishConfig) {
            if ($isDryRun) {
                info('Skipping config publish in testing environment.');
            } else {
                spin(fn (): bool => $this->callSilent('vendor:publish', ['--tag' => 'laravel-rag-config']) === 0,
                    'Publishing config...');
            }
        }

        $shouldPublishMigrations = $isNonInteractive
            || (
                // @codeCoverageIgnoreStart

                fn (): bool => confirm('Publish migrations?'))(); // @codeCoverageIgnoreEnd
        if ($shouldPublishMigrations) {
            if ($isDryRun) {
                info('Skipping migrations publish in testing environment.');
            } else {
                spin(fn (): bool => $this->callSilent('vendor:publish', ['--tag' => 'laravel-rag-migrations']) === 0,
                    'Publishing migrations...');
            }
        }

        $shouldRunMigrations = $isNonInteractive
            ? (bool) $this->option('run-migrate')
            : (
                // @codeCoverageIgnoreStart

                fn (): bool => confirm('Run database migrations now?', false))(); // @codeCoverageIgnoreEnd
        if ($shouldRunMigrations) {
            if ($isDryRun) {
                info('Skipping migrate in testing environment.');
            } else {
                $this->call('migrate');
            }
        }

        $shouldEnableTenancy = $isNonInteractive
            ? (bool) $this->option('with-tenancy')
            : (
                // @codeCoverageIgnoreStart

                fn (): bool => confirm('Enable multi-tenant mode (tenancy.enabled = true)?',
                    false))(); // @codeCoverageIgnoreEnd
        if ($shouldEnableTenancy) {
            $configFilePath = config_path('rag.php');
            if (! $files->exists($configFilePath)) {
                warning('Config file not found. Publish the config first.');
            } else {
                $configFileContents = $files->get($configFilePath);
                $updatedConfigContents = preg_replace("/('enabled'\s*=>\s*)false/", '\\1true', $configFileContents, 1) ?? $configFileContents;
                $files->put($configFilePath, $updatedConfigContents);
                info('Enabled tenancy in config/rag.php');
            }
        }

        note('If you find this useful, please consider starring the repo:');
        info('https://github.com/akira-rag/laravel-rag');

        $shouldOpenGitHub = $isNonInteractive
            ? (bool) $this->option('star')
            : (
                // @codeCoverageIgnoreStart

                fn (): bool => confirm('Open the GitHub repository now to leave a star?',
                    false))(); // @codeCoverageIgnoreEnd
        if ($shouldOpenGitHub && ! $isDryRun) {
            $githubRepositoryUrl = 'https://github.com/akira-rag/laravel-rag';
            $this->openUrl($githubRepositoryUrl);
        }

        info('Install complete.');

        return self::SUCCESS;
    }

    private function openUrl(string $url): void
    {

        $openCommand = PHP_OS_FAMILY === 'Darwin' ? 'open' : (PHP_OS_FAMILY === 'Windows' ? 'start' : 'xdg-open');
        try {
            $processHandle = @popen($openCommand.' '.escapeshellarg($url), 'r');
            if (is_resource($processHandle)) {
                pclose($processHandle);
            }
        } catch (Throwable) {
            // ignore
        }
    }
}
