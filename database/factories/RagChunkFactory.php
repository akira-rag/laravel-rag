<?php

declare(strict_types=1);

namespace Akira\Rag\Database\Factories;

use Akira\Rag\Models\RagChunk;
use Akira\Rag\Models\RagDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RagChunk>
 */
final class RagChunkFactory extends Factory
{
    protected $model = RagChunk::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'document_id' => RagDocument::factory(),
            'position' => $this->faker->numberBetween(0, 100),
            'content' => $this->faker->paragraph(),
            'meta' => [],
        ];
    }
}
