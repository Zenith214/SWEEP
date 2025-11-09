<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait MonitorsQueryPerformance
{
    /**
     * Execute a query with performance monitoring.
     * Logs slow queries (>2 seconds) and handles timeouts.
     *
     * @param callable $callback Query callback to execute
     * @param string $queryName Descriptive name for the query
     * @param int $timeoutSeconds Timeout in seconds (default: 30)
     * @return mixed Query result
     * @throws \Exception
     */
    protected function executeMonitoredQuery(callable $callback, string $queryName, int $timeoutSeconds = 30): mixed
    {
        $startTime = microtime(true);
        
        try {
            // Set statement timeout for this query (PostgreSQL/MySQL 8.0+)
            // Note: This may not work on all database systems
            if (config('database.default') === 'mysql') {
                DB::statement("SET SESSION max_execution_time = " . ($timeoutSeconds * 1000));
            }

            // Execute the query
            $result = $callback();

            // Calculate execution time
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // milliseconds

            // Log slow queries (>2 seconds)
            if ($executionTime > 2000) {
                Log::warning('Slow query detected', [
                    'query_name' => $queryName,
                    'execution_time_ms' => $executionTime,
                    'threshold_ms' => 2000,
                    'timestamp' => now()->toIso8601String(),
                ]);
            }

            // Reset timeout
            if (config('database.default') === 'mysql') {
                DB::statement("SET SESSION max_execution_time = 0");
            }

            return $result;
        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            // Check if it's a timeout error
            $isTimeout = $this->isTimeoutException($e);

            Log::error('Query execution failed', [
                'query_name' => $queryName,
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage(),
                'is_timeout' => $isTimeout,
                'timestamp' => now()->toIso8601String(),
            ]);

            // Reset timeout on error
            if (config('database.default') === 'mysql') {
                try {
                    DB::statement("SET SESSION max_execution_time = 0");
                } catch (\Exception $resetError) {
                    // Ignore reset errors
                }
            }

            throw $e;
        }
    }

    /**
     * Check if an exception is a timeout exception.
     *
     * @param \Exception $exception
     * @return bool
     */
    private function isTimeoutException(\Exception $exception): bool
    {
        $message = strtolower($exception->getMessage());
        
        $timeoutIndicators = [
            'max_execution_time',
            'query execution was interrupted',
            'timeout',
            'lock wait timeout exceeded',
            'statement timeout',
        ];

        foreach ($timeoutIndicators as $indicator) {
            if (str_contains($message, $indicator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Execute a query with fallback to cached data on failure.
     *
     * @param callable $callback Query callback to execute
     * @param string $cacheKey Cache key for fallback data
     * @param string $queryName Descriptive name for the query
     * @param mixed $defaultValue Default value if both query and cache fail
     * @return mixed Query result or cached/default value
     */
    protected function executeWithCacheFallback(
        callable $callback,
        string $cacheKey,
        string $queryName,
        mixed $defaultValue = []
    ): mixed {
        try {
            // Try to execute the query
            $result = $this->executeMonitoredQuery($callback, $queryName);
            
            // Cache the successful result for future fallback
            cache()->put($cacheKey . ':fallback', $result, now()->addHours(24));
            
            return $result;
        } catch (\Exception $e) {
            Log::warning('Query failed, attempting cache fallback', [
                'query_name' => $queryName,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);

            // Try to get cached fallback data
            $cachedData = cache()->get($cacheKey . ':fallback');
            
            if ($cachedData !== null) {
                Log::info('Using cached fallback data', [
                    'query_name' => $queryName,
                    'cache_key' => $cacheKey,
                ]);
                
                return $cachedData;
            }

            // If no cache available, return default value
            Log::warning('No cached fallback available, using default value', [
                'query_name' => $queryName,
                'default_value_type' => gettype($defaultValue),
            ]);

            return $defaultValue;
        }
    }

    /**
     * Log a user-friendly error message for missing data scenarios.
     *
     * @param string $context Context where data is missing
     * @param array $additionalInfo Additional information for logging
     * @return void
     */
    protected function logMissingDataScenario(string $context, array $additionalInfo = []): void
    {
        Log::info('Missing data scenario encountered', array_merge([
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ], $additionalInfo));
    }

    /**
     * Get a user-friendly error message for display.
     *
     * @param \Exception $exception
     * @param string $context
     * @return string
     */
    protected function getUserFriendlyErrorMessage(\Exception $exception, string $context): string
    {
        // Check if it's a timeout
        if ($this->isTimeoutException($exception)) {
            return "The request took too long to process. Please try narrowing your date range or filters.";
        }

        // Check for connection errors
        if (str_contains(strtolower($exception->getMessage()), 'connection')) {
            return "Unable to connect to the database. Please try again in a moment.";
        }

        // Check for permission errors
        if (str_contains(strtolower($exception->getMessage()), 'permission') ||
            str_contains(strtolower($exception->getMessage()), 'access denied')) {
            return "You don't have permission to access this data.";
        }

        // Generic error message
        return "Unable to load {$context}. Please try again or contact support if the problem persists.";
    }
}
