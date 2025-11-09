<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecyclingTarget extends Model
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
    public const TYPE_ALL = 'all';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'material_type',
        'target_weight',
        'month',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_weight' => 'decimal:2',
            'month' => 'date',
        ];
    }

    /**
     * Get the current progress percentage for this target.
     */
    public function getCurrentProgress(): float
    {
        $startOfMonth = $this->month->copy()->startOfMonth();
        $endOfMonth = $this->month->copy()->endOfMonth();

        $query = RecyclingLogMaterial::query()
            ->whereHas('recyclingLog', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('collection_date', [$startOfMonth, $endOfMonth]);
            });

        // Filter by material type if not 'all'
        if ($this->material_type && $this->material_type !== self::TYPE_ALL) {
            $query->where('material_type', $this->material_type);
        }

        $currentWeight = $query->sum('weight');

        if ($this->target_weight == 0) {
            return 0;
        }

        return round(($currentWeight / $this->target_weight) * 100, 2);
    }

    /**
     * Check if the target has been achieved.
     */
    public function isAchieved(): bool
    {
        return $this->getCurrentProgress() >= 100;
    }
}
