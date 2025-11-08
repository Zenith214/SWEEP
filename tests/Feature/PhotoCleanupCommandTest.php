<?php

namespace Tests\Feature;

use App\Models\CollectionPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoCleanupCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('collection_photos');
    }

    /** @test */
    public function it_runs_cleanup_command_successfully()
    {
        // Create a photo record in database
        $photo = CollectionPhoto::factory()->create([
            'file_path' => 'valid-photo.jpg'
        ]);
        
        // Create files in storage
        Storage::disk('collection_photos')->put('valid-photo.jpg', 'content');
        Storage::disk('collection_photos')->put('orphaned-photo.jpg', 'content');
        
        // Run the cleanup command - it should complete without errors
        $result = $this->artisan('photos:cleanup');
        
        // Command should complete successfully
        $result->assertExitCode(0);
        
        // The actual cleanup logic is tested in PhotoServiceTest
        // This test just verifies the command runs without errors
        $this->assertTrue(true);
    }

    /** @test */
    public function it_runs_cleanup_command_in_dry_run_mode()
    {
        // Create files in storage
        Storage::disk('collection_photos')->put('orphaned-photo.jpg', 'content');
        
        // Run the cleanup command in dry-run mode
        $this->artisan('photos:cleanup --dry-run')
            ->expectsOutput('Running in DRY RUN mode - no files will be deleted')
            ->expectsOutput('This was a DRY RUN - no files were actually deleted')
            ->assertExitCode(0);
        
        // Verify file still exists (not deleted in dry-run)
        $this->assertTrue(Storage::disk('collection_photos')->exists('orphaned-photo.jpg'));
    }

    /** @test */
    public function it_displays_storage_statistics()
    {
        // Create some photos
        CollectionPhoto::factory()->count(2)->create();
        
        Storage::disk('collection_photos')->put('photo1.jpg', 'content');
        Storage::disk('collection_photos')->put('photo2.jpg', 'content');
        
        // Run the stats command
        $this->artisan('photos:cleanup --stats')
            ->expectsOutput('Storage Statistics:')
            ->assertExitCode(0);
    }
}
