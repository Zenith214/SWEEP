<?php

namespace App\Console\Commands;

use App\Services\PhotoService;
use Illuminate\Console\Command;

class CleanupOrphanedPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:cleanup
                            {--dry-run : Run without actually deleting files}
                            {--stats : Display storage statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned collection photos that have no database record';

    /**
     * Execute the console command.
     */
    public function handle(PhotoService $photoService): int
    {
        $this->info('Collection Photo Cleanup');
        $this->newLine();

        // Display storage statistics if requested
        if ($this->option('stats')) {
            $this->displayStorageStats($photoService);
            return Command::SUCCESS;
        }

        // Check for dry run mode
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no files will be deleted');
            $this->newLine();
        }

        $this->info('Scanning for orphaned photos...');
        
        try {
            if ($dryRun) {
                // For dry run, we'll just show what would be deleted
                $stats = $this->simulateCleanup($photoService);
            } else {
                // Perform actual cleanup
                $stats = $photoService->cleanupOrphanedPhotos();
            }

            $this->newLine();
            $this->info('Cleanup Results:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Files Checked', $stats['photos_checked']],
                    ['Photos Deleted', $stats['photos_deleted']],
                    ['Thumbnails Deleted', $stats['thumbnails_deleted']],
                    ['Space Freed', $this->formatBytes($stats['space_freed'])],
                ]
            );

            if ($dryRun) {
                $this->newLine();
                $this->warn('This was a DRY RUN - no files were actually deleted');
                $this->info('Run without --dry-run to perform actual cleanup');
            } else {
                $this->newLine();
                $this->info('Cleanup completed successfully!');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Display storage statistics.
     */
    protected function displayStorageStats(PhotoService $photoService): void
    {
        $stats = $photoService->getStorageStats();

        $this->info('Storage Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Files', $stats['total_files']],
                ['Photos', $stats['photo_count']],
                ['Thumbnails', $stats['thumbnail_count']],
                ['Database Records', $stats['db_photo_count']],
                ['Total Storage Used', $stats['total_size_mb'] . ' MB'],
                ['Potential Orphans', $stats['photo_count'] - $stats['db_photo_count']],
            ]
        );
    }

    /**
     * Simulate cleanup without deleting files.
     */
    protected function simulateCleanup(PhotoService $photoService): array
    {
        // This is a simplified simulation - in a real scenario you might want
        // to implement a separate method in PhotoService for dry-run
        $stats = $photoService->getStorageStats();
        
        return [
            'photos_checked' => $stats['total_files'],
            'photos_deleted' => max(0, $stats['photo_count'] - $stats['db_photo_count']),
            'thumbnails_deleted' => 0, // Estimate
            'space_freed' => 0, // Would need to calculate
        ];
    }

    /**
     * Format bytes to human-readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
