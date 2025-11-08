<?php

namespace Database\Seeders;

use App\Models\CollectionLog;
use App\Models\CollectionPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CollectionPhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all collection logs
        $collectionLogs = CollectionLog::all();

        if ($collectionLogs->isEmpty()) {
            $this->command->warn('No collection logs found. Run CollectionLogSeeder first.');
            return;
        }

        $this->command->info('Creating photos for collection logs...');

        // Ensure storage directory exists
        Storage::makeDirectory('public/collection-photos');
        Storage::makeDirectory('public/collection-photos/thumbnails');

        $totalPhotos = 0;

        foreach ($collectionLogs as $log) {
            // 70% of logs will have photos
            if (rand(1, 100) <= 70) {
                $photoCount = $this->getRandomPhotoCount($log->status);
                $totalPhotos += $this->createPhotosForLog($log, $photoCount);
            }
        }

        $this->command->info("Created {$totalPhotos} photos for collection logs!");
    }

    /**
     * Get random photo count based on log status.
     */
    private function getRandomPhotoCount(string $status): int
    {
        // Issue reports tend to have more photos
        if ($status === CollectionLog::STATUS_ISSUE_REPORTED) {
            return rand(2, 5);
        }

        // Completed logs might have 1-3 photos
        if ($status === CollectionLog::STATUS_COMPLETED) {
            return rand(1, 3);
        }

        // Incomplete logs might have 1-2 photos
        return rand(1, 2);
    }

    /**
     * Create photos for a collection log.
     */
    private function createPhotosForLog(CollectionLog $log, int $count): int
    {
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            try {
                $this->createPhoto($log, $i + 1);
                $created++;
            } catch (\Exception $e) {
                $this->command->warn("Failed to create photo for log {$log->id}: " . $e->getMessage());
            }
        }

        return $created;
    }

    /**
     * Create a single photo for a collection log.
     */
    private function createPhoto(CollectionLog $log, int $photoNumber): void
    {
        // Create a placeholder image
        $manager = new ImageManager(new Driver());
        
        // Generate image with different colors based on status
        $color = $this->getColorForStatus($log->status);
        $width = 800;
        $height = 600;
        
        // Create image
        $image = $manager->create($width, $height)->fill($color);
        
        // Add text overlay
        $text = $this->getPhotoText($log, $photoNumber);
        
        // Note: Text rendering requires GD with FreeType support
        // For seeding purposes, we'll create simple colored rectangles
        // In production, actual photos would be uploaded
        
        // Generate unique filename
        $filename = 'log_' . $log->id . '_photo_' . $photoNumber . '_' . time() . rand(1000, 9999) . '.jpg';
        $filepath = 'public/collection-photos/' . $filename;
        $thumbnailFilename = 'log_' . $log->id . '_photo_' . $photoNumber . '_' . time() . rand(1000, 9999) . '_thumb.jpg';
        $thumbnailPath = 'public/collection-photos/thumbnails/' . $thumbnailFilename;
        
        // Save full-size image
        $encodedImage = $image->toJpeg(85);
        Storage::put($filepath, $encodedImage);
        
        // Create and save thumbnail (200x200)
        $thumbnail = $image->scale(width: 200);
        $encodedThumbnail = $thumbnail->toJpeg(85);
        Storage::put($thumbnailPath, $encodedThumbnail);
        
        // Get file size
        $fileSize = strlen($encodedImage);
        
        // Create database record
        CollectionPhoto::create([
            'collection_log_id' => $log->id,
            'file_path' => 'collection-photos/' . $filename,
            'file_name' => $filename,
            'file_size' => $fileSize,
            'uploaded_at' => $log->created_at->copy()->addMinutes(rand(1, 30)),
        ]);
    }

    /**
     * Get color for status.
     */
    private function getColorForStatus(string $status): string
    {
        return match($status) {
            CollectionLog::STATUS_COMPLETED => '#10b981',      // Green
            CollectionLog::STATUS_INCOMPLETE => '#f59e0b',     // Orange
            CollectionLog::STATUS_ISSUE_REPORTED => '#ef4444', // Red
            default => '#6b7280',                               // Gray
        };
    }

    /**
     * Get descriptive text for photo.
     */
    private function getPhotoText(CollectionLog $log, int $photoNumber): string
    {
        $statusText = match($log->status) {
            CollectionLog::STATUS_COMPLETED => 'Completed Collection',
            CollectionLog::STATUS_INCOMPLETE => 'Incomplete Collection',
            CollectionLog::STATUS_ISSUE_REPORTED => 'Issue Documentation',
            default => 'Collection Photo',
        };

        return "{$statusText}\nLog #{$log->id}\nPhoto {$photoNumber}";
    }
}
