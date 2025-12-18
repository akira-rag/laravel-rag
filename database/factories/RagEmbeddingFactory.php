<?php

declare(strict_types=1);

namespace Akira\Rag\Database\Factories;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagEmbedding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RagEmbedding>
 */
final class RagEmbeddingFactory extends Factory
{
    protected $model = RagEmbedding::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'chunk_id' => RagChunk::factory(),
            'embedding' => '[]',
            'meta' => [],
        ];
    }
}
