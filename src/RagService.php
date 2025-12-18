<?php

declare(strict_types=1);

namespace Akira\Rag;

final readonly class RagService
{
    public function __construct(private RagManager $manager) {}

    /**
     * @param  array<string,mixed>  $payload
     * @return array{document_id:string, chunks:int}
     */
    public function ingest(array $payload): array
    {

        return $this->manager->ingest($payload);
    }

    /**
     * @param  array<string,mixed>  $filters
     * @return array{answer:string,chunks:array<int,array{id:string,score:float}>,query_id:string}
     */
    public function ask(string $question, array $filters = []): array
    {

        return $this->manager->ask($question, $filters);
    }
}
