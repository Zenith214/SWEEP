<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true) . ' Route',
            'zone' => fake()->unique()->bothify('R-###'),
            'description' => fake()->sentence(),
            'notes' => fake()->optional()->paragraph(),
            'is_active' => true,
        ];
    }
}
