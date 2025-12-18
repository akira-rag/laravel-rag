<?php

declare(strict_types=1);

namespace Akira\Rag\Database\Factories;

use Akira\Rag\Models\RagQuery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RagQuery>
 */
final class RagQueryFactory extends Factory
{
    protected $model = RagQuery::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'question' => $this->faker->sentence().'?',
            'meta' => [],
        ];
    }
}
