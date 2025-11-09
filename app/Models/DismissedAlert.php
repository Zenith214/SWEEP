<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DismissedAlert extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'alert_category',
        'alert_identifier',
        'dismissed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dismissed_at' => 'datetime',
        ];
    }

    /**
     * Get the user who dismissed this alert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if an alert has been dismissed by a user.
     *
     * @param int $userId
     * @param string $category
     * @param string $identifier
     * @return bool
     */
    public static function isAlertDismissed(int $userId, string $category, string $identifier): bool
    {
        return self::where('user_id', $userId)
            ->where('alert_category', $category)
            ->where('alert_identifier', $identifier)
            ->exists();
    }

    /**
     * Dismiss an alert for a user.
     *
     * @param int $userId
     * @param string $category
     * @param string $identifier
     * @return self
     */
    public static function dismissAlert(int $userId, string $category, string $identifier): self
    {
        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'alert_category' => $category,
                'alert_identifier' => $identifier,
            ],
            [
                'dismissed_at' => now(),
            ]
        );
    }

    /**
     * Clear all dismissed alerts for a user.
     *
     * @param int $userId
     * @return int Number of alerts cleared
     */
    public static function clearAllForUser(int $userId): int
    {
        return self::where('user_id', $userId)->delete();
    }

    /**
     * Clear dismissed alerts older than specified days.
     *
     * @param int $days
     * @return int Number of alerts cleared
     */
    public static function clearOldAlerts(int $days = 30): int
    {
        return self::where('dismissed_at', '<', now()->subDays($days))->delete();
    }
}
