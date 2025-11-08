<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CollectionLog>
 */
class CollectionLogFactory extends Factory
{
    protected $model = CollectionLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'completion_time' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'status' => $this->faker->randomElement([
                CollectionLog::STATUS_COMPLETED,
                CollectionLog::STATUS_INCOMPLETE,
                CollectionLog::STATUS_ISSUE_REPORTED
            ]),
            'issue_type' => null,
            'issue_description' => null,
            'completion_percentage' => $this->faker->numberBetween(50, 100),
            'crew_notes' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
            'edited_at' => null,
        ];
    }

    /**
     * Indicate that the collection log is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'completion_percentage' => 100,
            'issue_type' => null,
            'issue_description' => null,
        ]);
    }

    /**
     * Indicate that the collection log has an issue.
     */
    public function withIssue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_type' => $this->faker->randomElement(array_keys(CollectionLog::ISSUE_TYPES)),
            'issue_description' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the collection log is incomplete.
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CollectionLog::STATUS_INCOMPLETE,
            'completion_percentage' => $this->faker->numberBetween(10, 90),
            'issue_type' => null,
            'issue_description' => null,
        ]);
    }
}
