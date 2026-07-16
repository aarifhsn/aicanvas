<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GenerationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'provider' => 'gemini',
            'template_key' => null,
            'prompt' => $this->faker->sentence(),
            'result' => $this->faker->paragraph(),
            'latency_ms' => $this->faker->numberBetween(200, 1500),
        ];
    }
}