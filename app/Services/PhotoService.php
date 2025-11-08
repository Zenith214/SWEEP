<?php

namespace App\Services;

use App\Models\CollectionLog;
use App\Models\CollectionPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class PhotoService
{
    /**
     * Maximum number of photos allowed per collection log.
     */
    public const MAX_PHOTOS = 5;

    /**
     * Thumbnail dimensions.
     */
    public const THUMBNAIL_WIDTH = 200;
    public const THUMBNAIL_HEIGHT = 200;

    /**
     * Maximum image dimensions for optimization.
     */
    public const MAX_IMAGE_WIDTH = 1920;
    public const MAX_IMAGE_HEIGHT = 1920;

    /**
     * Image quality for compression (1-100).
     */
    public const IMAGE_QUALITY = 85;

    /**
     * Storage disk for collection photos.
     */
    protected string $disk = 'collection_photos';

    /**
     * Upload and process a photo for a collection log.
     *
     * @param UploadedFile $file
     * @param CollectionLog $log
     * @return CollectionPhoto
     * @throws \Exception
     */
    public function uploadPhoto(UploadedFile $file, CollectionLog $log): CollectionPhoto
    {
        // Validate photo count
        if (!$this->validatePhotoCount($log)) {
            throw new \Exception('Maximum of ' . self::MAX_PHOTOS . ' photos allowed per collection log. Please remove a photo before adding a new one.');
        }

        try {
            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Optimize and store the photo
            $path = $this->optimizeAndStore($file, $filename);

            if (!$path) {
                throw new \Exception('Failed to store photo file. Please try again.');
            }

            // Create thumbnail
            $thumbnailPath = $this->createThumbnail($path);

            // Create photo record
            $photo = CollectionPhoto::create([
                'collection_log_id' => $log->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'uploaded_at' => now()
            ]);

            return $photo;
        } catch (\Exception $e) {
            // Clean up if photo was stored but record creation failed
            if (isset($path) && Storage::disk($this->disk)->exists($path)) {
                Storage::disk($this->disk)->delete($path);
            }
            if (isset($thumbnailPath) && Storage::disk($this->disk)->exists('thumbnails/' . basename($path))) {
                Storage::disk($this->disk)->delete('thumbnails/' . basename($path));
            }
            
            throw new \Exception('Failed to upload photo: ' . $e->getMessage());
        }
    }

    /**
     * Create a thumbnail for the given photo path using Intervention Image.
     *
     * @param string $path
     * @return string Thumbnail path
     * @throws \Exception
     */
    public function createThumbnail(string $path): string
    {
        // Get the full path to the original image
        $fullPath = Storage::disk($this->disk)->path($path);

        // Generate thumbnail filename
        $pathInfo = pathinfo($path);
        $thumbnailFilename = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        $thumbnailPath = $thumbnailFilename;

        // Create thumbnail using Intervention Image
        $image = Image::read($fullPath);
        
        // Resize to 200x200 maintaining aspect ratio and crop to fit
        $image->cover(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);

        // Save thumbnail
        $thumbnailFullPath = Storage::disk($this->disk)->path($thumbnailPath);
        $image->save($thumbnailFullPath);

        return $thumbnailPath;
    }

    /**
     * Delete a photo and its thumbnail from storage.
     *
     * @param CollectionPhoto $photo
     * @return bool
     */
    public function deletePhoto(CollectionPhoto $photo): bool
    {
        // Delete the original photo
        if (Storage::disk($this->disk)->exists($photo->file_path)) {
            Storage::disk($this->disk)->delete($photo->file_path);
        }

        // Delete the thumbnail
        $pathInfo = pathinfo($photo->file_path);
        $thumbnailPath = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            Storage::disk($this->disk)->delete($thumbnailPath);
        }

        // Delete the database record
        return $photo->delete();
    }

    /**
     * Validate that the collection log has less than the maximum allowed photos.
     *
     * @param CollectionLog $log
     * @return bool
     */
    public function validatePhotoCount(CollectionLog $log): bool
    {
        return $log->photos()->count() < self::MAX_PHOTOS;
    }

    /**
     * Get the public URL for a photo.
     *
     * @param CollectionPhoto $photo
     * @return string
     */
    public function getPhotoUrl(CollectionPhoto $photo): string
    {
        return Storage::disk($this->disk)->url($photo->file_path);
    }

    /**
     * Get the public URL for a photo thumbnail.
     *
     * @param CollectionPhoto $photo
     * @return string
     */
    public function getThumbnailUrl(CollectionPhoto $photo): string
    {
        $pathInfo = pathinfo($photo->file_path);
        $thumbnailPath = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        return Storage::disk($this->disk)->url($thumbnailPath);
    }

    /**
     * Optimize and store an uploaded photo.
     *
     * @param UploadedFile $file
     * @param string $filename
     * @return string Path to stored file
     * @throws \Exception
     */
    protected function optimizeAndStore(UploadedFile $file, string $filename): string
    {
        // Read the uploaded image
        $image = Image::read($file->getRealPath());

        // Get current dimensions
        $width = $image->width();
        $height = $image->height();

        // Resize if image is larger than maximum dimensions
        if ($width > self::MAX_IMAGE_WIDTH || $height > self::MAX_IMAGE_HEIGHT) {
            $image->scale(
                width: $width > self::MAX_IMAGE_WIDTH ? self::MAX_IMAGE_WIDTH : null,
                height: $height > self::MAX_IMAGE_HEIGHT ? self::MAX_IMAGE_HEIGHT : null
            );
        }

        // Encode with compression
        $encoded = $image->toJpeg(quality: self::IMAGE_QUALITY);

        // Store the optimized image
        $path = $filename;
        Storage::disk($this->disk)->put($path, $encoded);

        return $path;
    }

    /**
     * Clean up orphaned photos that have no associated database record.
     *
     * @return array Statistics about cleanup operation
     */
    public function cleanupOrphanedPhotos(): array
    {
        $stats = [
            'photos_checked' => 0,
            'photos_deleted' => 0,
            'thumbnails_deleted' => 0,
            'space_freed' => 0, // in bytes
        ];

        // Get all files from storage
        $allFiles = Storage::disk($this->disk)->files();
        
        // Get all photo paths from database
        $dbPhotoPaths = CollectionPhoto::pluck('file_path')->toArray();
        
        // Generate expected thumbnail paths from database photos
        $dbThumbnailPaths = array_map(function($path) {
            $pathInfo = pathinfo($path);
            return $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        }, $dbPhotoPaths);

        // Track thumbnails that will be deleted with their parent photos
        $thumbnailsToSkip = [];

        // First pass: identify and delete orphaned photos and their thumbnails
        foreach ($allFiles as $file) {
            $stats['photos_checked']++;
            
            // Check if this is a thumbnail
            $isThumbnail = str_contains($file, '_thumb.');
            
            if (!$isThumbnail) {
                // Check if photo exists in database
                if (!in_array($file, $dbPhotoPaths)) {
                    $size = Storage::disk($this->disk)->size($file);
                    Storage::disk($this->disk)->delete($file);
                    $stats['photos_deleted']++;
                    $stats['space_freed'] += $size;
                    
                    // Also delete associated thumbnail if it exists
                    $pathInfo = pathinfo($file);
                    $thumbnailPath = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
                    if (Storage::disk($this->disk)->exists($thumbnailPath)) {
                        $thumbSize = Storage::disk($this->disk)->size($thumbnailPath);
                        Storage::disk($this->disk)->delete($thumbnailPath);
                        $stats['thumbnails_deleted']++;
                        $stats['space_freed'] += $thumbSize;
                        // Mark this thumbnail as already processed
                        $thumbnailsToSkip[] = $thumbnailPath;
                    }
                }
            }
        }

        // Second pass: delete orphaned thumbnails that weren't deleted in first pass
        foreach ($allFiles as $file) {
            $isThumbnail = str_contains($file, '_thumb.');
            
            if ($isThumbnail && !in_array($file, $thumbnailsToSkip)) {
                // Check if thumbnail's parent photo exists in database
                if (!in_array($file, $dbThumbnailPaths)) {
                    if (Storage::disk($this->disk)->exists($file)) {
                        $size = Storage::disk($this->disk)->size($file);
                        Storage::disk($this->disk)->delete($file);
                        $stats['thumbnails_deleted']++;
                        $stats['space_freed'] += $size;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Get statistics about photo storage usage.
     *
     * @return array
     */
    public function getStorageStats(): array
    {
        $allFiles = Storage::disk($this->disk)->files();
        $totalSize = 0;
        $photoCount = 0;
        $thumbnailCount = 0;

        foreach ($allFiles as $file) {
            $size = Storage::disk($this->disk)->size($file);
            $totalSize += $size;
            
            if (str_contains($file, '_thumb.')) {
                $thumbnailCount++;
            } else {
                $photoCount++;
            }
        }

        return [
            'total_files' => count($allFiles),
            'photo_count' => $photoCount,
            'thumbnail_count' => $thumbnailCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'db_photo_count' => CollectionPhoto::count(),
        ];
    }
}
