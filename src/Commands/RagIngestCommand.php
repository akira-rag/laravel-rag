<?php

declare(strict_types=1);

namespace Akira\Rag\Commands;

use Akira\Rag\Exceptions\InvalidPayload;
use Akira\Rag\Facades\Rag;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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

    public function handle(Filesystem $files): int
    {

        $title = (string) ($this->option('title') ?? '');
        $sourceType = (string) ($this->option('source_type') ?? '');
        $sourceRef = (string) ($this->option('source_ref') ?? '');
        $textContent = (string) ($this->option('text') ?? '');
        $filePath = (string) ($this->option('file') ?? '');
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
                    $k = text('Meta key (blank to finish)', required: false);
                    if ($k === '') {
                        break;
                    }
                    $v = text("Meta value for '{$k}'", required: false);
                    $metaPairs[] = $k.'='.$v;
                    if (! confirm('Add another meta pair?', false)) {
                        break;
                    }
                }
            }
        }
        // @codeCoverageIgnoreEnd

        // validate content source
        if ($textContent === '' && $filePath === '') {
            warning('You must provide either --text or --file');

            return self::INVALID;
        }

        if ($filePath !== '') {
            if (! $files->exists($filePath)) {
                warning('File not found: '.$filePath);

                return self::INVALID;
            }
            $ext = mb_strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (! in_array($ext, ['txt', 'md'], true)) {
                warning('Unsupported file extension. Only .txt and .md are supported.');

                return self::INVALID;
            }
            $textContent = $files->get($filePath);
        }

        $meta = $this->parseMeta($metaPairs);

        try {
            $result = Rag::ingest([
                'title' => $title,
                'source_type' => $sourceType,
                'source_ref' => $sourceRef,
                'content' => $textContent,
                'meta' => $meta,
            ]);
        } catch (InvalidPayload $e) {
            warning($e->getMessage());

            return self::INVALID;
        }

        $embeddingFlag = $this->option('no-embed') ? 'skipped' : ($this->option('sync') ? 'synced' : 'dispatched');

        $this->newLine();
        $this->components->twoColumnDetail('Document ID', $result['document_id']);
        $this->components->twoColumnDetail('Chunks', (string) $result['chunks']);
        $this->components->twoColumnDetail('Embedding', $embeddingFlag);

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $pairs
     * @return array<string,mixed>
     */
    private function parseMeta(array $pairs): array
    {

        $out = [];
        foreach ($pairs as $pair) {
            if ($pair === '') {
                continue;
            }
            $pos = mb_strpos($pair, '=');
            if ($pos === false) {
                $out[$pair] = true;

                continue;
            }
            $k = mb_substr($pair, 0, $pos);
            $v = mb_substr($pair, $pos + 1);
            $out[$k] = $v;
        }

        return $out;
    }
}
