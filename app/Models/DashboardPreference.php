<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'widget_visibility',
        'widget_order',
        'default_filters',
        'default_view',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'widget_visibility' => 'array',
            'widget_order' => 'array',
            'default_filters' => 'array',
        ];
    }

    /**
     * Get the user that owns the dashboard preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default widget visibility settings.
     *
     * @return array<string, bool>
     */
    public static function getDefaultWidgetVisibility(): array
    {
        return [
            'collection_status' => true,
            'pending_items' => true,
            'collection_trends' => true,
            'recycling_metrics' => true,
            'fleet_utilization' => true,
            'crew_performance' => true,
            'report_statistics' => true,
            'route_performance' => true,
            'system_usage' => true,
            'alerts' => true,
            'geographic_distribution' => true,
            'operational_costs' => true,
        ];
    }

    /**
     * Get default widget order.
     *
     * @return array<int, string>
     */
    public static function getDefaultWidgetOrder(): array
    {
        return [
            'collection_status',
            'pending_items',
            'alerts',
            'collection_trends',
            'recycling_metrics',
            'fleet_utilization',
            'crew_performance',
            'report_statistics',
            'route_performance',
            'system_usage',
            'geographic_distribution',
            'operational_costs',
        ];
    }

    /**
     * Reset preferences to default values.
     */
    public function resetToDefaults(): void
    {
        $this->widget_visibility = self::getDefaultWidgetVisibility();
        $this->widget_order = self::getDefaultWidgetOrder();
        $this->default_filters = [];
        $this->default_view = null;
        $this->save();
    }

    /**
     * Check if a widget is visible.
     */
    public function isWidgetVisible(string $widgetName): bool
    {
        return $this->widget_visibility[$widgetName] ?? true;
    }

    /**
     * Toggle widget visibility.
     */
    public function toggleWidget(string $widgetName): void
    {
        $visibility = $this->widget_visibility ?? self::getDefaultWidgetVisibility();
        $visibility[$widgetName] = !($visibility[$widgetName] ?? true);
        $this->widget_visibility = $visibility;
        $this->save();
    }
}
