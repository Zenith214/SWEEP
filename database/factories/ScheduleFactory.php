<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');
        
        return [
            'route_id' => \App\Models\Route::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'collection_time' => fake()->time('H:i:s'),
            'is_active' => true,
        ];
    }
}
