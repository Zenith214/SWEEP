<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TruckStatusHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'truck_status_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'truck_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    /**
     * Indicates if the model should be timestamped.
     * We only use created_at, not updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     * Automatically set created_at when creating a new record.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    /**
     * Get the truck for this status history entry.
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    /**
     * Get the user who changed the status.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
