<?php

namespace Tests\Unit;

use App\Models\CollectionLog;
use App\Models\CollectionPhoto;
use App\Services\PhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PhotoService $photoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->photoService = new PhotoService();
        
        // Fake the collection_photos disk
        Storage::fake('collection_photos');
    }

    /** @test */
    public function it_validates_photo_count_correctly()
    {
        $log = CollectionLog::factory()->create();
        
        // Should be valid with 0 photos
        $this->assertTrue($this->photoService->validatePhotoCount($log));
        
        // Create 4 photos
        CollectionPhoto::factory()->count(4)->create([
            'collection_log_id' => $log->id
        ]);
        
        // Should still be valid with 4 photos
        $this->assertTrue($this->photoService->validatePhotoCount($log));
        
        // Create 5th photo
        CollectionPhoto::factory()->create([
            'collection_log_id' => $log->id
        ]);
        
        // Should be invalid with 5 photos
        $this->assertFalse($this->photoService->validatePhotoCount($log));
    }

    /** @test */
    public function it_gets_photo_url_correctly()
    {
        $photo = CollectionPhoto::factory()->create([
            'file_path' => 'test-photo.jpg'
        ]);
        
        $url = $this->photoService->getPhotoUrl($photo);
        
        $this->assertStringContainsString('test-photo.jpg', $url);
    }

    /** @test */
    public function it_gets_thumbnail_url_correctly()
    {
        $photo = CollectionPhoto::factory()->create([
            'file_path' => 'test-photo.jpg'
        ]);
        
        $url = $this->photoService->getThumbnailUrl($photo);
        
        $this->assertStringContainsString('test-photo_thumb.jpg', $url);
    }

    /** @test */
    public function it_cleans_up_orphaned_photos()
    {
        // Create a photo record in database
        $photo = CollectionPhoto::factory()->create([
            'file_path' => 'valid-photo.jpg'
        ]);
        
        // Create files in storage
        Storage::disk('collection_photos')->put('valid-photo.jpg', 'content');
        Storage::disk('collection_photos')->put('valid-photo_thumb.jpg', 'content');
        Storage::disk('collection_photos')->put('orphaned-photo.jpg', 'content');
        Storage::disk('collection_photos')->put('orphaned-photo_thumb.jpg', 'content');
        
        // Run cleanup
        $stats = $this->photoService->cleanupOrphanedPhotos();
        
        // Assert orphaned files were deleted
        $this->assertFalse(Storage::disk('collection_photos')->exists('orphaned-photo.jpg'));
        $this->assertFalse(Storage::disk('collection_photos')->exists('orphaned-photo_thumb.jpg'));
        
        // Assert valid files still exist
        $this->assertTrue(Storage::disk('collection_photos')->exists('valid-photo.jpg'));
        $this->assertTrue(Storage::disk('collection_photos')->exists('valid-photo_thumb.jpg'));
        
        // Assert stats are correct
        $this->assertEquals(4, $stats['photos_checked']);
        $this->assertEquals(1, $stats['photos_deleted']);
        $this->assertEquals(1, $stats['thumbnails_deleted']);
        $this->assertGreaterThan(0, $stats['space_freed']);
    }

    /** @test */
    public function it_gets_storage_statistics()
    {
        // Create some photos in database
        CollectionPhoto::factory()->count(3)->create();
        
        // Create files in storage (including orphaned ones)
        Storage::disk('collection_photos')->put('photo1.jpg', 'content');
        Storage::disk('collection_photos')->put('photo1_thumb.jpg', 'content');
        Storage::disk('collection_photos')->put('photo2.jpg', 'content');
        Storage::disk('collection_photos')->put('orphaned.jpg', 'content');
        
        $stats = $this->photoService->getStorageStats();
        
        $this->assertEquals(4, $stats['total_files']);
        $this->assertEquals(3, $stats['photo_count']);
        $this->assertEquals(1, $stats['thumbnail_count']);
        $this->assertEquals(3, $stats['db_photo_count']);
        $this->assertArrayHasKey('total_size', $stats);
        $this->assertArrayHasKey('total_size_mb', $stats);
    }

    /** @test */
    public function it_deletes_orphaned_thumbnails_when_parent_photo_is_missing()
    {
        // Create only a thumbnail without parent photo in database
        Storage::disk('collection_photos')->put('orphaned_thumb.jpg', 'content');
        
        $stats = $this->photoService->cleanupOrphanedPhotos();
        
        // Thumbnail should be deleted
        $this->assertFalse(Storage::disk('collection_photos')->exists('orphaned_thumb.jpg'));
        $this->assertEquals(1, $stats['thumbnails_deleted']);
    }
}
