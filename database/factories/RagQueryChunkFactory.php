<?php

declare(strict_types=1);

namespace Akira\Rag\Database\Factories;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagQuery;
use Akira\Rag\Models\RagQueryChunk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RagQueryChunk>
 */
final class RagQueryChunkFactory extends Factory
{
    protected $model = RagQueryChunk::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'query_id' => RagQuery::factory(),
            'chunk_id' => RagChunk::factory(),
            'score' => $this->faker->randomFloat(2, 0, 1),
            'rank' => $this->faker->numberBetween(0, 10),
            'meta' => [],
        ];
    }
}
