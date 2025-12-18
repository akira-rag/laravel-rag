<?php

declare(strict_types=1);

namespace Akira\Rag\Database\Factories;

use Akira\Rag\Models\RagDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RagDocument>
 */
final class RagDocumentFactory extends Factory
{
    protected $model = RagDocument::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'title' => $this->faker->sentence(),
            'source_type' => $this->faker->randomElement(['file', 'url', 'text']),
            'source_ref' => $this->faker->url(),
            'hash' => hash('sha256', $this->faker->text()),
            'meta' => [],
        ];
    }
}
