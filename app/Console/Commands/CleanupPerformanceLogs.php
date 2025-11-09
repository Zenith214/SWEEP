<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupPerformanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:cleanup-logs {--days=30 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old dashboard performance logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysToKeep = (int) $this->option('days');
        
        $this->info("Cleaning up dashboard performance logs older than {$daysToKeep} days...");
        
        try {
            // Get log directory
            $logPath = storage_path('logs');
            
            if (!is_dir($logPath)) {
                $this->error('Log directory not found');
                return self::FAILURE;
            }
            
            $cutoffDate = now()->subDays($daysToKeep);
            $deletedCount = 0;
            $totalSize = 0;
            
            // Find and process log files
            $files = glob($logPath . '/laravel-*.log');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileTime = \Carbon\Carbon::createFromTimestamp(filemtime($file));
                    
                    if ($fileTime->lt($cutoffDate)) {
                        $fileSize = filesize($file);
                        
                        if (unlink($file)) {
                            $deletedCount++;
                            $totalSize += $fileSize;
                            $this->line("Deleted: " . basename($file));
                        }
                    }
                }
            }
            
            $totalSizeMB = round($totalSize / 1024 / 1024, 2);
            
            $this->info("Cleanup complete!");
            $this->info("Deleted {$deletedCount} log file(s)");
            $this->info("Freed {$totalSizeMB} MB of disk space");
            
            Log::info('Dashboard performance logs cleaned up', [
                'deleted_count' => $deletedCount,
                'freed_space_mb' => $totalSizeMB,
                'days_kept' => $daysToKeep,
            ]);
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error cleaning up logs: ' . $e->getMessage());
            
            Log::error('Failed to cleanup dashboard performance logs', [
                'error' => $e->getMessage(),
            ]);
            
            return self::FAILURE;
        }
    }
}
