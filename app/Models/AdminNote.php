<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'collection_log_id',
        'admin_id',
        'note'
    ];

    /**
     * Get the collection log for this admin note.
     */
    public function collectionLog(): BelongsTo
    {
        return $this->belongsTo(CollectionLog::class);
    }

    /**
     * Get the admin user who created this note.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
