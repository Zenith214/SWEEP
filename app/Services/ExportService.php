<?php

namespace App\Services;

use App\Traits\MonitorsQueryPerformance;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    use MonitorsQueryPerformance;
    /**
     * Export dashboard metrics to PDF format.
     * Generates a formatted PDF report with dashboard metrics.
     *
     * @param array $metrics Dashboard metrics data
     * @param array $preferences User preferences for visible widgets
     * @param string|null $title Optional report title
     * @param bool $storeInStorage Whether to store in storage disk (for scheduled reports)
     * @return string Path to generated PDF file (absolute path or storage path)
     * @throws \Exception
     */
    public function exportToPDF(array $metrics, array $preferences = [], ?string $title = null, bool $storeInStorage = false): string
    {
        $startTime = microtime(true);
        
        try {
            $title = $title ?? 'Dashboard Report';
            $generatedAt = now();
            
            // Validate metrics data
            if (empty($metrics)) {
                $this->logMissingDataScenario('PDF export', ['reason' => 'Empty metrics array']);
                throw new \Exception('No data available to export');
            }
            
            // Filter metrics based on preferences if provided
            $visibleMetrics = $this->filterMetricsByPreferences($metrics, $preferences);
            
            if (empty($visibleMetrics)) {
                $this->logMissingDataScenario('PDF export', ['reason' => 'All metrics filtered out by preferences']);
                throw new \Exception('No visible metrics to export based on your preferences');
            }
            
            // Generate PDF using DomPDF with timeout protection
            $pdf = $this->executeMonitoredQuery(
                function () use ($title, $visibleMetrics, $generatedAt, $preferences) {
                    return Pdf::loadView('exports.dashboard-pdf', [
                        'title' => $title,
                        'metrics' => $visibleMetrics,
                        'generatedAt' => $generatedAt,
                        'preferences' => $preferences,
                    ]);
                },
                'pdf_generation',
                60 // 60 second timeout for PDF generation
            );
            
            // Configure PDF options
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            
            // Generate filename
            $filename = $this->generateFilename('pdf', $generatedAt);
            
            if ($storeInStorage) {
                // Store in storage disk for scheduled reports
                $storagePath = 'reports/' . $filename;
                \Illuminate\Support\Facades\Storage::put($storagePath, $pdf->output());
                
                $executionTime = round((microtime(true) - $startTime) * 1000, 2);
                
                Log::info('PDF export stored in storage disk', [
                    'filename' => $filename,
                    'storage_path' => $storagePath,
                    'execution_time_ms' => $executionTime,
                ]);
                
                return $storagePath;
            } else {
                // Store in temporary exports directory for immediate download
                $filepath = storage_path('app/exports/' . $filename);
                
                // Ensure exports directory exists
                $this->ensureExportDirectoryExists();
                
                // Save PDF
                $pdf->save($filepath);
                
                $executionTime = round((microtime(true) - $startTime) * 1000, 2);
                $fileSize = filesize($filepath);
                
                // Log slow exports (>5 seconds)
                if ($executionTime > 5000) {
                    Log::warning('Slow PDF export detected', [
                        'filename' => $filename,
                        'execution_time_ms' => $executionTime,
                        'file_size_bytes' => $fileSize,
                        'threshold_ms' => 5000,
                    ]);
                } else {
                    Log::info('PDF export generated successfully', [
                        'filename' => $filename,
                        'filepath' => $filepath,
                        'size' => $fileSize,
                        'execution_time_ms' => $executionTime,
                    ]);
                }
                
                return $filepath;
            }
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error('Error generating PDF export', [
                'error' => $e->getMessage(),
                'execution_time_ms' => $executionTime,
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Provide user-friendly error message
            $userMessage = $this->getUserFriendlyErrorMessage($e, 'PDF export');
            throw new \Exception($userMessage);
        }
    }

    /**
     * Export dashboard metrics to CSV format.
     * Generates CSV files for each metric category.
     *
     * @param array $metrics Dashboard metrics data
     * @param bool $storeInStorage Whether to store in storage disk (for scheduled reports)
     * @param string|null $category Optional specific category to export
     * @return string Path to generated CSV file (or ZIP if multiple files)
     * @throws \Exception
     */
    public function exportToCSV(array $metrics, bool $storeInStorage = false, ?string $category = null): string
    {
        try {
            $generatedAt = now();
            
            // If specific category requested, export only that
            if ($category && isset($metrics[$category])) {
                return $this->exportCategoryToCSV($category, $metrics[$category], $generatedAt, $storeInStorage);
            }
            
            // Export all metrics to separate CSV files
            $exportedFiles = [];
            
            foreach ($metrics as $categoryName => $categoryData) {
                if (is_array($categoryData) && !empty($categoryData)) {
                    $filepath = $this->exportCategoryToCSV($categoryName, $categoryData, $generatedAt, $storeInStorage);
                    $exportedFiles[] = $filepath;
                }
            }
            
            // If multiple files, create a ZIP archive (if ZipArchive is available)
            if (count($exportedFiles) > 1 && class_exists('ZipArchive')) {
                return $this->createZipArchive($exportedFiles, $generatedAt, $storeInStorage);
            }
            
            // Return single file path (or first file if ZIP not available)
            return $exportedFiles[0] ?? throw new \Exception('No data to export');
        } catch (\Exception $e) {
            Log::error('Error generating CSV export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new \Exception('Failed to generate CSV export: ' . $e->getMessage());
        }
    }

    /**
     * Export a specific metric category to CSV.
     *
     * @param string $category Category name
     * @param array $data Category data
     * @param Carbon $generatedAt Generation timestamp
     * @param bool $storeInStorage Whether to store in storage disk
     * @return string Path to generated CSV file
     */
    private function exportCategoryToCSV(string $category, array $data, Carbon $generatedAt, bool $storeInStorage = false): string
    {
        $filename = $this->generateFilename('csv', $generatedAt, $category);
        
        // Convert data to CSV format
        $csvData = $this->convertToCsvFormat($data);
        
        // Generate CSV content
        $csvContent = '';
        if (!empty($csvData)) {
            // Add headers
            $csvContent .= implode(',', array_map(function($header) {
                return '"' . str_replace('"', '""', $header) . '"';
            }, array_keys($csvData[0]))) . "\n";
            
            // Add data rows
            foreach ($csvData as $row) {
                $csvContent .= implode(',', array_map(function($value) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }, $row)) . "\n";
            }
        }
        
        if ($storeInStorage) {
            // Store in storage disk for scheduled reports
            $storagePath = 'reports/' . $filename;
            \Illuminate\Support\Facades\Storage::put($storagePath, $csvContent);
            
            Log::info('CSV export stored in storage disk', [
                'category' => $category,
                'filename' => $filename,
                'storage_path' => $storagePath,
                'rows' => count($csvData),
            ]);
            
            return $storagePath;
        } else {
            // Store in temporary exports directory for immediate download
            $filepath = storage_path('app/exports/' . $filename);
            
            // Ensure exports directory exists
            $this->ensureExportDirectoryExists();
            
            // Write CSV file
            file_put_contents($filepath, $csvContent);
            
            Log::info('CSV export generated successfully', [
                'category' => $category,
                'filename' => $filename,
                'filepath' => $filepath,
                'rows' => count($csvData),
            ]);
            
            return $filepath;
        }
    }

    /**
     * Convert nested array data to flat CSV format.
     * Handles large datasets efficiently by processing in chunks.
     *
     * @param array $data Data to convert
     * @return array Flattened data ready for CSV
     */
    private function convertToCsvFormat(array $data): array
    {
        $csvData = [];
        
        // Handle different data structures
        if ($this->isAssociativeArray($data)) {
            // Simple key-value pairs
            foreach ($data as $key => $value) {
                if (is_scalar($value) || is_null($value)) {
                    $csvData[] = [
                        'Metric' => $this->formatKey($key),
                        'Value' => $value ?? 'N/A',
                    ];
                } elseif (is_array($value) && $this->isAssociativeArray($value)) {
                    // Nested associative array
                    foreach ($value as $subKey => $subValue) {
                        if (is_scalar($subValue) || is_null($subValue)) {
                            $csvData[] = [
                                'Category' => $this->formatKey($key),
                                'Metric' => $this->formatKey($subKey),
                                'Value' => $subValue ?? 'N/A',
                            ];
                        }
                    }
                }
            }
        } else {
            // Array of items (e.g., crew performance, route data)
            foreach ($data as $item) {
                if (is_array($item)) {
                    $csvData[] = $this->flattenArray($item);
                }
            }
        }
        
        return $csvData;
    }

    /**
     * Flatten a nested array to a single level.
     *
     * @param array $array Array to flatten
     * @param string $prefix Prefix for nested keys
     * @return array Flattened array
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '_' . $key : $key;
            
            if (is_array($value) && !empty($value)) {
                // Check if it's a simple list
                if (array_keys($value) === range(0, count($value) - 1)) {
                    // It's a numeric array, convert to string
                    $result[$this->formatKey($newKey)] = implode(', ', $value);
                } else {
                    // Recursively flatten
                    $result = array_merge($result, $this->flattenArray($value, $newKey));
                }
            } else {
                $result[$this->formatKey($newKey)] = $value ?? 'N/A';
            }
        }
        
        return $result;
    }

    /**
     * Create a ZIP archive containing multiple CSV files.
     *
     * @param array $files Array of file paths to include
     * @param Carbon $generatedAt Generation timestamp
     * @param bool $storeInStorage Whether to store in storage disk
     * @return string Path to generated ZIP file
     * @throws \Exception
     */
    private function createZipArchive(array $files, Carbon $generatedAt, bool $storeInStorage = false): string
    {
        $zipFilename = $this->generateFilename('zip', $generatedAt);
        
        if ($storeInStorage) {
            // For storage disk, create temp file then move
            $tempZipPath = sys_get_temp_dir() . '/' . $zipFilename;
            
            $zip = new \ZipArchive();
            
            if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Failed to create ZIP archive');
            }
            
            foreach ($files as $storagePath) {
                $content = \Illuminate\Support\Facades\Storage::get($storagePath);
                $zip->addFromString(basename($storagePath), $content);
            }
            
            $zip->close();
            
            // Move to storage disk
            $storagePath = 'reports/' . $zipFilename;
            \Illuminate\Support\Facades\Storage::put($storagePath, file_get_contents($tempZipPath));
            
            // Clean up temp file and individual CSV files
            unlink($tempZipPath);
            foreach ($files as $storagePath) {
                \Illuminate\Support\Facades\Storage::delete($storagePath);
            }
            
            Log::info('ZIP archive stored in storage disk', [
                'filename' => $zipFilename,
                'files_count' => count($files),
            ]);
            
            return $storagePath;
        } else {
            $zipPath = storage_path('app/exports/' . $zipFilename);
            
            $zip = new \ZipArchive();
            
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Failed to create ZIP archive');
            }
            
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            
            $zip->close();
            
            // Clean up individual CSV files
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            
            Log::info('ZIP archive created successfully', [
                'filename' => $zipFilename,
                'files_count' => count($files),
            ]);
            
            return $zipPath;
        }
    }

    /**
     * Generate a timestamped filename for exports.
     *
     * @param string $format File format (pdf, csv, zip)
     * @param Carbon $date Generation date
     * @param string|null $category Optional category name for CSV files
     * @return string Generated filename
     */
    private function generateFilename(string $format, Carbon $date, ?string $category = null): string
    {
        $timestamp = $date->format('Y-m-d_His');
        $categoryPart = $category ? '_' . str_replace(' ', '-', strtolower($category)) : '';
        
        return "dashboard_export{$categoryPart}_{$timestamp}.{$format}";
    }

    /**
     * Filter metrics based on user preferences.
     *
     * @param array $metrics All metrics
     * @param array $preferences User preferences
     * @return array Filtered metrics
     */
    private function filterMetricsByPreferences(array $metrics, array $preferences): array
    {
        if (empty($preferences) || !isset($preferences['widget_visibility'])) {
            return $metrics;
        }
        
        $visibility = $preferences['widget_visibility'];
        $filtered = [];
        
        foreach ($metrics as $key => $value) {
            // Include metric if visibility is not specified or if it's set to visible
            if (!isset($visibility[$key]) || $visibility[$key] === true) {
                $filtered[$key] = $value;
            }
        }
        
        return $filtered;
    }

    /**
     * Ensure the exports directory exists.
     *
     * @return void
     */
    private function ensureExportDirectoryExists(): void
    {
        $exportDir = storage_path('app/exports');
        
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }
    }

    /**
     * Check if an array is associative.
     *
     * @param array $array Array to check
     * @return bool True if associative, false if numeric
     */
    private function isAssociativeArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Format a key for display (convert snake_case to Title Case).
     *
     * @param string $key Key to format
     * @return string Formatted key
     */
    private function formatKey(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Clean up old export files.
     * Removes export files older than the specified number of days.
     *
     * @param int $daysToKeep Number of days to keep files (default: 7)
     * @return int Number of files deleted
     */
    public function cleanupOldExports(int $daysToKeep = 7): int
    {
        try {
            $exportDir = storage_path('app/exports');
            
            if (!is_dir($exportDir)) {
                return 0;
            }
            
            $cutoffDate = now()->subDays($daysToKeep);
            $deletedCount = 0;
            
            $files = glob($exportDir . '/*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $fileTime = Carbon::createFromTimestamp(filemtime($file));
                    
                    if ($fileTime->lt($cutoffDate)) {
                        unlink($file);
                        $deletedCount++;
                    }
                }
            }
            
            Log::info('Old export files cleaned up', [
                'deleted_count' => $deletedCount,
                'days_to_keep' => $daysToKeep,
            ]);
            
            return $deletedCount;
        } catch (\Exception $e) {
            Log::error('Error cleaning up old exports', [
                'error' => $e->getMessage(),
            ]);
            
            return 0;
        }
    }
}
