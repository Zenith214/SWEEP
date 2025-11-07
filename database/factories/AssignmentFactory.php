<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Truck;
use App\Models\User;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'truck_id' => Truck::factory(),
            'user_id' => User::factory(),
            'route_id' => Route::factory(),
            'assignment_date' => now()->addDays(fake()->numberBetween(1, 30)),
            'status' => Assignment::STATUS_ACTIVE,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the assignment is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Assignment::STATUS_CANCELLED,
            'cancellation_reason' => fake()->sentence(),
        ]);
    }

    /**
     * Set the assignment for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_date' => now(),
        ]);
    }

    /**
     * Set the assignment for a future date.
     */
    public function future(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_date' => now()->addDays($days),
        ]);
    }

    /**
     * Set the assignment for a past date.
     */
    public function past(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'assignment_date' => now()->subDays($days),
        ]);
    }
}
