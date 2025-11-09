<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ScheduledReport extends Model
{
    use HasFactory;

    /**
     * Frequency constants.
     */
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';

    public const FREQUENCIES = [
        self::FREQUENCY_DAILY => 'Daily',
        self::FREQUENCY_WEEKLY => 'Weekly',
        self::FREQUENCY_MONTHLY => 'Monthly',
    ];

    /**
     * Format constants.
     */
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_CSV = 'csv';

    public const FORMATS = [
        self::FORMAT_PDF => 'PDF',
        self::FORMAT_CSV => 'CSV',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'frequency',
        'metrics',
        'format',
        'last_generated_at',
        'next_generation_at',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'last_generated_at' => 'datetime',
            'next_generation_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the scheduled report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the generated reports for this scheduled report.
     */
    public function generatedReports(): HasMany
    {
        return $this->hasMany(GeneratedReport::class);
    }

    /**
     * Scope to filter active scheduled reports.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter scheduled reports due for generation.
     */
    public function scopeDueForGeneration(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_generation_at')
                    ->orWhere('next_generation_at', '<=', now());
            });
    }

    /**
     * Calculate the next generation date based on frequency.
     */
    public function calculateNextGenerationDate(?Carbon $fromDate = null): Carbon
    {
        $fromDate = $fromDate ?? now();

        return match ($this->frequency) {
            self::FREQUENCY_DAILY => $fromDate->copy()->addDay()->startOfDay(),
            self::FREQUENCY_WEEKLY => $fromDate->copy()->addWeek()->startOfDay(),
            self::FREQUENCY_MONTHLY => $fromDate->copy()->addMonth()->startOfDay(),
            default => $fromDate->copy()->addDay()->startOfDay(),
        };
    }

    /**
     * Update the next generation date.
     */
    public function updateNextGenerationDate(): void
    {
        $this->next_generation_at = $this->calculateNextGenerationDate();
        $this->save();
    }

    /**
     * Mark report as generated.
     */
    public function markAsGenerated(): void
    {
        $this->last_generated_at = now();
        $this->next_generation_at = $this->calculateNextGenerationDate();
        $this->save();
    }

    /**
     * Check if the report is due for generation.
     */
    public function isDueForGeneration(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->next_generation_at === null) {
            return true;
        }

        return $this->next_generation_at->lte(now());
    }

    /**
     * Get available metrics for scheduled reports.
     *
     * @return array<string, string>
     */
    public static function getAvailableMetrics(): array
    {
        return [
            'collection_status' => 'Collection Status',
            'collection_trends' => 'Collection Trends',
            'recycling_metrics' => 'Recycling Metrics',
            'fleet_utilization' => 'Fleet Utilization',
            'crew_performance' => 'Crew Performance',
            'report_statistics' => 'Report Statistics',
            'route_performance' => 'Route Performance',
            'system_usage' => 'System Usage',
            'geographic_distribution' => 'Geographic Distribution',
            'operational_costs' => 'Operational Costs',
        ];
    }

    /**
     * Activate the scheduled report.
     */
    public function activate(): void
    {
        $this->is_active = true;
        if ($this->next_generation_at === null) {
            $this->next_generation_at = $this->calculateNextGenerationDate();
        }
        $this->save();
    }

    /**
     * Deactivate the scheduled report.
     */
    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }
}
