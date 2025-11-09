<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'zone',
        'description',
        'notes',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all schedules for the route.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get active schedules for the route.
     */
    public function activeSchedules(): HasMany
    {
        return $this->hasMany(Schedule::class)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Get the recycling logs for the route.
     */
    public function recyclingLogs(): HasMany
    {
        return $this->hasMany(RecyclingLog::class);
    }

    /**
     * Check if the route has active schedules.
     */
    public function hasActiveSchedules(): bool
    {
        return $this->activeSchedules()->exists();
    }

    /**
     * Get the next collection date for this route.
     */
    public function getNextCollectionDate(): ?Carbon
    {
        $today = now()->startOfDay();
        $nextDate = null;

        foreach ($this->activeSchedules as $schedule) {
            $daysOfWeek = $schedule->getDaysOfWeek();
            
            // Check next 14 days to find the nearest collection date
            for ($i = 0; $i < 14; $i++) {
                $checkDate = $today->copy()->addDays($i);
                $dayOfWeek = $checkDate->dayOfWeek;
                
                if (in_array($dayOfWeek, $daysOfWeek) && $schedule->isActiveOn($checkDate)) {
                    if ($nextDate === null || $checkDate->lt($nextDate)) {
                        $nextDate = $checkDate;
                    }
                    break;
                }
            }
        }

        return $nextDate;
    }
}
