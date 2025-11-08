<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CollectionPhoto extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'collection_log_id',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'file_size' => 'integer'
        ];
    }

    /**
     * Get the collection log for this photo.
     */
    public function collectionLog(): BelongsTo
    {
        return $this->belongsTo(CollectionLog::class);
    }

    /**
     * Get the public URL for this photo.
     */
    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the public URL for the thumbnail.
     */
    public function getThumbnailUrl(): string
    {
        // Assuming thumbnails are stored with '_thumb' suffix before extension
        $pathInfo = pathinfo($this->file_path);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        return Storage::url($thumbnailPath);
    }

    /**
     * Get the formatted file size (human-readable).
     */
    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }
}
