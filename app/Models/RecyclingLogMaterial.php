<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecyclingLogMaterial extends Model
{
    use HasFactory;

    /**
     * Material type constants.
     */
    public const TYPE_PLASTIC = 'plastic';
    public const TYPE_PAPER = 'paper';
    public const TYPE_GLASS = 'glass';
    public const TYPE_METAL = 'metal';
    public const TYPE_CARDBOARD = 'cardboard';
    public const TYPE_ORGANIC = 'organic';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'recycling_log_id',
        'material_type',
        'weight',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    /**
     * Get the recycling log that owns this material.
     */
    public function recyclingLog(): BelongsTo
    {
        return $this->belongsTo(RecyclingLog::class);
    }

    /**
     * Get all available material types.
     *
     * @return array<string>
     */
    public static function getMaterialTypes(): array
    {
        return [
            self::TYPE_PLASTIC,
            self::TYPE_PAPER,
            self::TYPE_GLASS,
            self::TYPE_METAL,
            self::TYPE_CARDBOARD,
            self::TYPE_ORGANIC,
        ];
    }
}
