<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class ReportPhotoService
{
    /**
     * Maximum number of photos allowed per report.
     */
    public const MAX_PHOTOS = 3;

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
     * Storage disk for report photos.
     */
    protected string $disk = 'report_photos';

    /**
     * Upload and process a photo for a report.
     */
    public function uploadPhoto(UploadedFile $file, Report $report): ReportPhoto
    {
        // Validate photo count
        if (!$this->validatePhotoCount($report)) {
            throw new \Exception('Maximum of ' . self::MAX_PHOTOS . ' photos allowed per report. Please remove a photo before adding a new one.');
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
            $photo = ReportPhoto::create([
                'report_id' => $report->id,
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
     */
    public function deletePhoto(ReportPhoto $photo): bool
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
     * Validate that the report has less than the maximum allowed photos.
     */
    public function validatePhotoCount(Report $report): bool
    {
        return $report->photos()->count() < self::MAX_PHOTOS;
    }

    /**
     * Get the public URL for a photo.
     */
    public function getPhotoUrl(ReportPhoto $photo): string
    {
        return Storage::disk($this->disk)->url($photo->file_path);
    }

    /**
     * Get the public URL for a photo thumbnail.
     */
    public function getThumbnailUrl(ReportPhoto $photo): string
    {
        $pathInfo = pathinfo($photo->file_path);
        $thumbnailPath = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        return Storage::disk($this->disk)->url($thumbnailPath);
    }

    /**
     * Optimize and store an uploaded photo.
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
}
