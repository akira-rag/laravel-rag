<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Facades\Rag;
use Akira\Rag\Tenant\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;
use function Laravel\Prompts\warning;

final class RagIngestCommand extends Command
{
    protected $signature
        = 'rag:ingest'
        .' {--title=}'
        .' {--source_type=}'
        .' {--source_ref=}'
        .' {--text=}'
        .' {--file=}'
        .' {--meta=*}'
        .' {--no-embed : Store document and chunks but skip embedding job dispatch}'
        .' {--sync : Run embedding synchronously (no queue), if supported}';

    protected $description = 'Ingest content into the RAG knowledge base';

    public function handle(Filesystem $files, TenantContext $tenant): int
    {
        $tenantId = $tenant->current();
        Log::info('[rag:ingest] Starting ingestion', ['tenant' => $tenantId]);

        $titleOpt = $this->option('title');
        $title = is_string($titleOpt) ? $titleOpt : '';

        $sourceTypeOpt = $this->option('source_type');
        $sourceType = is_string($sourceTypeOpt) ? $sourceTypeOpt : '';

        $sourceRefOpt = $this->option('source_ref');
        $sourceRef = is_string($sourceRefOpt) ? $sourceRefOpt : '';

        $textOpt = $this->option('text');
        $textContent = is_string($textOpt) ? $textOpt : '';

        $fileOpt = $this->option('file');
        $filePath = is_string($fileOpt) ? $fileOpt : '';
        /** @var list<string> $metaPairs */
        $metaPairs = array_values(array_filter((array) $this->option('meta'), is_string(...)));

        $interactive = ($this->input->isInteractive())
            && ($title === '' || $sourceType === ''
                || ($textContent === ''
                    && $filePath === ''));

        // @codeCoverageIgnoreStart
        if ($interactive) {
            info('Akira RAG - Ingestion');

            if ($title === '') {
                $title = text('Title');
            }

            if ($sourceType === '') {
                $sourceType = select('Source type', [
                    'law' => 'law',
                    'pdf' => 'pdf',
                    'web' => 'web',
                    'faq' => 'faq',
                    'ticket' => 'ticket',
                    'note' => 'note',
                ], 'note');
            }

            if ($sourceRef === '') {
                $sourceRef = text('Source reference (optional)', required: false);
            }

            $ingestSource = select('Ingestion source', [
                'paste' => 'Paste text',
                'file' => 'Read from file',
            ], ($textContent !== '') ? 'paste' : 'file');

            if ($ingestSource === 'paste') {
                if ($textContent === '') {
                    $textContent = textarea('Paste the content');
                }
            } elseif ($filePath === '') {
                $filePath = text('Path to .txt or .md file');
            }

            // Meta key=value pairs loop
            if ($metaPairs === []) {
                while (true) {
                    $metaKey = text('Meta key (blank to finish)', required: false);
                    if ($metaKey === '') {
                        break;
                    }
                    $metaValue = text("Meta value for '{$metaKey}'", required: false);
                    $metaPairs[] = $metaKey.'='.$metaValue;
                    if (! confirm('Add another meta pair?', false)) {
                        break;
                    }
                }
            }
        }
        // @codeCoverageIgnoreEnd

        if ($textContent === '' && $filePath === '') {
            Log::warning('[rag:ingest] Missing content source', ['tenant' => $tenantId]);
            warning('You must provide either --text or --file');

            return self::INVALID;
        }

        if ($title === '') {
            Log::warning('[rag:ingest] Missing title', ['tenant' => $tenantId]);
            warning('Document title is required');

            return self::INVALID;
        }

        if ($filePath !== '') {
            if (! $files->exists($filePath)) {
                Log::warning('[rag:ingest] File not found', ['tenant' => $tenantId, 'path' => $filePath]);
                warning('File not found: '.$filePath);

                return self::INVALID;
            }

            if (! $files->isReadable($filePath)) {
                Log::warning('[rag:ingest] File not readable', ['tenant' => $tenantId, 'path' => $filePath]);
                warning('File not readable: '.$filePath);

                return self::INVALID;
            }

            $ext = mb_strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (! in_array($ext, ['txt', 'md'], true)) {
                Log::warning('[rag:ingest] Unsupported file extension', ['tenant' => $tenantId, 'path' => $filePath, 'ext' => $ext]);
                warning('Unsupported file extension. Only .txt and .md are supported.');

                return self::INVALID;
            }

            $textContent = $files->get($filePath);
            if ($textContent === '') {
                Log::warning('[rag:ingest] Empty file content', ['tenant' => $tenantId, 'path' => $filePath]);
                warning('File is empty: '.$filePath);

                return self::INVALID;
            }
        }

        if (mb_strlen($textContent) === 0) {
            Log::warning('[rag:ingest] Empty text content', ['tenant' => $tenantId]);
            warning('Content cannot be empty');

            return self::INVALID;
        }

        $meta = $this->parseMeta($metaPairs);

        try {
            $ingestResult = Rag::ingest([
                'title' => $title,
                'source_type' => $sourceType,
                'source_ref' => $sourceRef,
                'content' => $textContent,
                'meta' => $meta,
            ]);

            Log::info('[rag:ingest] Ingestion successful', [
                'tenant' => $tenantId,
                'document_id' => $ingestResult['document_id'],
                'chunks' => $ingestResult['chunks'],
            ]);
        } catch (InvalidPayload $e) {
            Log::error('[rag:ingest] Invalid payload', ['tenant' => $tenantId, 'error' => $e->getMessage()]);
            warning($e->getMessage());

            return self::INVALID;
        } catch (Throwable $e) {
            Log::error('[rag:ingest] Unexpected error', ['tenant' => $tenantId, 'error' => $e->getMessage()]);
            warning('Failed to ingest document: '.$e->getMessage());

            return self::FAILURE;
        }

        $embeddingFlag = $this->option('no-embed') ? 'skipped' : ($this->option('sync') ? 'synced' : 'dispatched');

        $this->newLine();
        $this->components->twoColumnDetail('Document ID', $ingestResult['document_id']);
        $this->components->twoColumnDetail('Chunks', (string) $ingestResult['chunks']);
        $this->components->twoColumnDetail('Embedding', $embeddingFlag);

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $pairs
     * @return array<string,mixed>
     */
    private function parseMeta(array $pairs): array
    {

        $metadata = [];
        foreach ($pairs as $pair) {
            if ($pair === '') {
                continue;
            }
            $equalPosition = mb_strpos($pair, '=');
            if ($equalPosition === false) {
                $metadata[$pair] = true;

                continue;
            }
            $metaKey = mb_substr($pair, 0, $equalPosition);
            $metaValue = mb_substr($pair, $equalPosition + 1);
            $metadata[$metaKey] = $metaValue;
        }

        return $metadata;
    }
}
