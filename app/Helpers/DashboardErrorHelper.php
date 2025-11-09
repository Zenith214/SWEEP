<?php

namespace App\Helpers;

class DashboardErrorHelper
{
    /**
     * Check if a metric has an error.
     *
     * @param array $metric
     * @return bool
     */
    public static function hasError(array $metric): bool
    {
        return isset($metric['error']) && !empty($metric['error']);
    }

    /**
     * Get the error message from a metric.
     *
     * @param array $metric
     * @return string|null
     */
    public static function getErrorMessage(array $metric): ?string
    {
        return $metric['error'] ?? null;
    }

    /**
     * Check if data is missing or empty.
     *
     * @param mixed $data
     * @return bool
     */
    public static function isMissingData(mixed $data): bool
    {
        if (is_array($data)) {
            // Check if array is empty or only contains metadata/error keys
            $dataKeys = array_diff(array_keys($data), ['error', 'metadata', 'period_start', 'period_end']);
            
            if (empty($dataKeys)) {
                return true;
            }
            
            // Check if all data values are empty
            $hasData = false;
            foreach ($dataKeys as $key) {
                if (!empty($data[$key])) {
                    $hasData = true;
                    break;
                }
            }
            
            return !$hasData;
        }
        
        return empty($data);
    }

    /**
     * Get a user-friendly message for missing data.
     *
     * @param string $context
     * @return string
     */
    public static function getMissingDataMessage(string $context): string
    {
        return match ($context) {
            'collection_metrics' => 'No collection data available for the selected period.',
            'recycling_metrics' => 'No recycling data available for the selected period.',
            'fleet_metrics' => 'No fleet data available for the selected period.',
            'crew_performance' => 'No crew performance data available for the selected period.',
            'report_statistics' => 'No report data available for the selected period.',
            'route_performance' => 'No route performance data available for the selected period.',
            'usage_statistics' => 'No usage statistics available for the selected period.',
            'geographic_distribution' => 'No geographic data available for the selected period.',
            'operational_costs' => 'Cost tracking is not yet enabled. Contact your administrator to enable this feature.',
            default => 'No data available for the selected period. Try adjusting your filters or date range.',
        };
    }

    /**
     * Get suggestions for resolving missing data.
     *
     * @param string $context
     * @return array
     */
    public static function getSuggestions(string $context): array
    {
        $commonSuggestions = [
            'Try expanding your date range',
            'Check if data has been entered for this period',
            'Verify your filter selections',
        ];

        $specificSuggestions = match ($context) {
            'collection_metrics' => [
                'Ensure collection logs have been submitted',
                'Check if routes are assigned for this period',
            ],
            'recycling_metrics' => [
                'Verify that recycling data has been logged',
                'Check if collection logs include recycling information',
            ],
            'crew_performance' => [
                'Ensure crew members have been assigned to routes',
                'Check if collection logs have been submitted',
            ],
            'operational_costs' => [
                'Contact your administrator to enable cost tracking',
                'This feature requires additional configuration',
            ],
            default => [],
        };

        return array_merge($commonSuggestions, $specificSuggestions);
    }

    /**
     * Format an error for display in the dashboard.
     *
     * @param array $metric
     * @param string $context
     * @return array
     */
    public static function formatErrorForDisplay(array $metric, string $context): array
    {
        if (self::hasError($metric)) {
            return [
                'has_error' => true,
                'error_message' => self::getErrorMessage($metric),
                'suggestions' => self::getSuggestions($context),
            ];
        }

        if (self::isMissingData($metric)) {
            return [
                'has_error' => false,
                'is_empty' => true,
                'empty_message' => self::getMissingDataMessage($context),
                'suggestions' => self::getSuggestions($context),
            ];
        }

        return [
            'has_error' => false,
            'is_empty' => false,
        ];
    }

    /**
     * Get an icon class for the error type.
     *
     * @param bool $isError
     * @return string
     */
    public static function getIconClass(bool $isError): string
    {
        return $isError ? 'text-red-500' : 'text-yellow-500';
    }

    /**
     * Get a CSS class for the error container.
     *
     * @param bool $isError
     * @return string
     */
    public static function getContainerClass(bool $isError): string
    {
        return $isError 
            ? 'bg-red-50 border-red-200 text-red-800' 
            : 'bg-yellow-50 border-yellow-200 text-yellow-800';
    }
}
