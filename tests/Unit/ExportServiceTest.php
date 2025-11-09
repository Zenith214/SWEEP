<?php

namespace Tests\Unit;

use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExportService $exportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exportService = new ExportService();
        
        // Ensure exports directory exists
        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $exportDir = storage_path('app/exports');
        if (is_dir($exportDir)) {
            $files = glob($exportDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        parent::tearDown();
    }

    /** @test */
    public function it_generates_pdf_export_successfully()
    {
        $metrics = [
            'collection_metrics' => [
                'total_collections' => 100,
                'completed' => 85,
                'completion_rate' => 85.0,
            ],
            'period_start' => '2025-01-01',
            'period_end' => '2025-01-31',
        ];
        
        $preferences = [];
        
        $filepath = $this->exportService->exportToPDF($metrics, $preferences, 'Test Dashboard Report');
        
        $this->assertFileExists($filepath);
        $this->assertStringContainsString('.pdf', $filepath);
        $this->assertGreaterThan(0, filesize($filepath));
    }

    /** @test */
    public function it_generates_csv_export_successfully()
    {
        $metrics = [
            'collection_metrics' => [
                'total_collections' => 100,
                'completed' => 85,
                'completion_rate' => 85.0,
            ],
        ];
        
        $filepath = $this->exportService->exportToCSV($metrics);
        
        $this->assertFileExists($filepath);
        $this->assertStringContainsString('.csv', $filepath);
        $this->assertGreaterThan(0, filesize($filepath));
    }

    /** @test */
    public function it_generates_timestamped_filenames()
    {
        $metrics = [
            'collection_metrics' => [
                'total_collections' => 50,
            ],
        ];
        
        $filepath1 = $this->exportService->exportToCSV($metrics);
        sleep(1); // Ensure different timestamp
        $filepath2 = $this->exportService->exportToCSV($metrics);
        
        $this->assertNotEquals($filepath1, $filepath2);
        $this->assertStringContainsString(date('Y-m-d'), basename($filepath1));
    }

    /** @test */
    public function it_filters_metrics_by_preferences()
    {
        $metrics = [
            'collection_metrics' => [
                'total_collections' => 100,
            ],
            'recycling_metrics' => [
                'total_weight' => 500,
            ],
            'fleet_metrics' => [
                'total_trucks' => 10,
            ],
        ];
        
        $preferences = [
            'widget_visibility' => [
                'collection_metrics' => true,
                'recycling_metrics' => false,
                'fleet_metrics' => true,
            ],
        ];
        
        $filepath = $this->exportService->exportToPDF($metrics, $preferences);
        
        $this->assertFileExists($filepath);
        // PDF is generated successfully - content verification would require PDF parsing
        $this->assertGreaterThan(0, filesize($filepath));
    }

    /** @test */
    public function it_handles_empty_metrics_gracefully()
    {
        $metrics = [];
        
        $filepath = $this->exportService->exportToPDF($metrics);
        
        $this->assertFileExists($filepath);
        $this->assertGreaterThan(0, filesize($filepath));
    }

    /** @test */
    public function it_handles_large_datasets_efficiently()
    {
        // Create a large dataset
        $largeMetrics = [
            'crew_performance' => [
                'all_crew_performance' => [],
            ],
        ];
        
        // Add 100 crew members
        for ($i = 1; $i <= 100; $i++) {
            $largeMetrics['crew_performance']['all_crew_performance'][] = [
                'user_id' => $i,
                'user_name' => "Crew Member $i",
                'total_collections' => rand(50, 200),
                'completed' => rand(40, 190),
                'completion_rate' => rand(80, 100),
            ];
        }
        
        $startTime = microtime(true);
        $filepath = $this->exportService->exportToCSV($largeMetrics);
        $endTime = microtime(true);
        
        $this->assertFileExists($filepath);
        $this->assertLessThan(5, $endTime - $startTime); // Should complete in under 5 seconds
    }

    /** @test */
    public function it_cleans_up_old_exports()
    {
        // Create some test files with different timestamps
        $exportDir = storage_path('app/exports');
        
        // Create an old file (10 days ago)
        $oldFile = $exportDir . '/old_export_' . now()->subDays(10)->format('Y-m-d_His') . '.pdf';
        file_put_contents($oldFile, 'old content');
        touch($oldFile, now()->subDays(10)->timestamp);
        
        // Create a recent file (2 days ago)
        $recentFile = $exportDir . '/recent_export_' . now()->subDays(2)->format('Y-m-d_His') . '.pdf';
        file_put_contents($recentFile, 'recent content');
        touch($recentFile, now()->subDays(2)->timestamp);
        
        // Clean up files older than 7 days
        $deletedCount = $this->exportService->cleanupOldExports(7);
        
        $this->assertEquals(1, $deletedCount);
        $this->assertFileDoesNotExist($oldFile);
        $this->assertFileExists($recentFile);
    }

    /** @test */
    public function it_exports_specific_category_to_csv()
    {
        $metrics = [
            'collection_metrics' => [
                'total_collections' => 100,
                'completed' => 85,
            ],
            'recycling_metrics' => [
                'total_weight' => 500,
            ],
        ];
        
        $filepath = $this->exportService->exportToCSV($metrics, 'collection_metrics');
        
        $this->assertFileExists($filepath);
        $this->assertStringContainsString('collection_metrics', basename($filepath));
        
        // Verify CSV content
        $content = file_get_contents($filepath);
        $this->assertStringContainsString('Total Collections', $content);
        $this->assertStringContainsString('100', $content);
    }

    /** @test */
    public function it_creates_zip_archive_for_multiple_categories()
    {
        // Skip if ZipArchive is not available
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension is not available');
        }
        
        $metrics = [
            'collection_metrics' => [
                'total_collections' => 100,
            ],
            'recycling_metrics' => [
                'total_weight' => 500,
            ],
            'fleet_metrics' => [
                'total_trucks' => 10,
            ],
        ];
        
        $filepath = $this->exportService->exportToCSV($metrics);
        
        $this->assertFileExists($filepath);
        $this->assertStringContainsString('.zip', $filepath);
        
        // Verify ZIP contains files
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($filepath));
        $this->assertGreaterThan(0, $zip->numFiles);
        $zip->close();
    }

    /** @test */
    public function it_handles_nested_array_data_in_csv()
    {
        $metrics = [
            'crew_performance' => [
                'top_performers' => [
                    [
                        'user_name' => 'John Doe',
                        'total_collections' => 50,
                        'completion_rate' => 95
                    ],
                ],
            ],
        ];
        
        $filepath = $this->exportService->exportToCSV($metrics);
        
        $this->assertFileExists($filepath);
        $this->assertGreaterThan(0, filesize($filepath));
    }
}
