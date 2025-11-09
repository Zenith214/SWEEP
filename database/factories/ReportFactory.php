<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate unique reference number with microseconds to avoid collisions
        static $counter = 0;
        $counter++;
        $date = now()->format('Ymd');
        $reference = "REP-{$date}-" . str_pad($counter, 4, '0', STR_PAD_LEFT);
        
        return [
            'reference_number' => $reference,
            'resident_id' => User::factory(),
            'report_type' => fake()->randomElement([
                Report::TYPE_MISSED_PICKUP,
                Report::TYPE_UNCOLLECTED_WASTE,
                Report::TYPE_ILLEGAL_DUMPING,
                Report::TYPE_OTHER
            ]),
            'location' => fake()->streetAddress(),
            'description' => fake()->paragraph(),
            'status' => Report::STATUS_PENDING,
            'route_id' => null,
            'assigned_to' => null,
            'resolved_at' => null,
        ];
    }

    /**
     * Indicate that the report is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Report::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Indicate that the report is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Report::STATUS_RESOLVED,
            'resolved_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the report is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Report::STATUS_CLOSED,
            'resolved_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
