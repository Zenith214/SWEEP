<?php

namespace Database\Factories;

use App\Models\CollectionLog;
use App\Models\CollectionPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CollectionPhoto>
 */
class CollectionPhotoFactory extends Factory
{
    protected $model = CollectionPhoto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = $this->faker->uuid() . '.jpg';
        
        return [
            'collection_log_id' => CollectionLog::factory(),
            'file_path' => $filename,
            'file_name' => 'photo_' . $this->faker->numberBetween(1, 100) . '.jpg',
            'file_size' => $this->faker->numberBetween(100000, 5000000), // 100KB to 5MB
            'uploaded_at' => now(),
        ];
    }
}
