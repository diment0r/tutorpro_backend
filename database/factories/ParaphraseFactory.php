<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Paraphrase>
 */
class ParaphraseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic' => fake()->realText(rand(10, 30)),
            'paraphrase' => fake()->realText(rand(500, 700)),
        ];
    }
}
