<?php

namespace Database\Factories;

use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Truck>
 */
class TruckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'truck_number' => 'T-' . fake()->unique()->numberBetween(1000, 9999),
            'license_plate' => fake()->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
            'capacity' => fake()->randomFloat(2, 5, 20),
            'operational_status' => Truck::STATUS_OPERATIONAL,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the truck is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'operational_status' => Truck::STATUS_MAINTENANCE,
        ]);
    }

    /**
     * Indicate that the truck is out of service.
     */
    public function outOfService(): static
    {
        return $this->state(fn (array $attributes) => [
            'operational_status' => Truck::STATUS_OUT_OF_SERVICE,
        ]);
    }
}
