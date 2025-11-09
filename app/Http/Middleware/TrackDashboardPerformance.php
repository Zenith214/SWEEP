<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackDashboardPerformance
{
    /**
     * Handle an incoming request and track dashboard load times.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track dashboard routes
        if (!$this->isDashboardRoute($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Process the request
        $response = $next($request);

        // Calculate metrics
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $loadTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2); // Convert to MB

        // Log performance metrics
        $this->logPerformanceMetrics($request, $loadTime, $memoryUsed);

        // Add performance headers for debugging (only in non-production)
        if (!app()->environment('production')) {
            $response->headers->set('X-Dashboard-Load-Time', $loadTime . 'ms');
            $response->headers->set('X-Dashboard-Memory-Used', $memoryUsed . 'MB');
        }

        return $response;
    }

    /**
     * Check if the current route is a dashboard route.
     *
     * @param Request $request
     * @return bool
     */
    private function isDashboardRoute(Request $request): bool
    {
        $route = $request->route();
        
        if (!$route) {
            return false;
        }

        $routeName = $route->getName();
        
        // Track these dashboard routes
        $dashboardRoutes = [
            'dashboard',
            'dashboard.admin',
            'dashboard.crew',
            'dashboard.resident',
            'dashboard.metrics',
            'dashboard.export',
        ];

        return in_array($routeName, $dashboardRoutes) || 
               str_starts_with($routeName ?? '', 'dashboard.');
    }

    /**
     * Log performance metrics for monitoring.
     *
     * @param Request $request
     * @param float $loadTime Load time in milliseconds
     * @param float $memoryUsed Memory used in MB
     * @return void
     */
    private function logPerformanceMetrics(Request $request, float $loadTime, float $memoryUsed): void
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : 'unknown';
        $user = $request->user();

        $context = [
            'route' => $routeName,
            'load_time_ms' => $loadTime,
            'memory_used_mb' => $memoryUsed,
            'user_id' => $user?->id,
            'user_role' => $user?->roles->first()?->name,
            'filters' => $request->query(),
            'timestamp' => now()->toIso8601String(),
        ];

        // Log slow dashboard loads (>2 seconds)
        if ($loadTime > 2000) {
            Log::warning('Slow dashboard load detected', array_merge($context, [
                'threshold_exceeded' => true,
                'threshold_ms' => 2000,
            ]));
        } else {
            // Log normal loads at info level
            Log::info('Dashboard load completed', $context);
        }

        // Log high memory usage (>50MB)
        if ($memoryUsed > 50) {
            Log::warning('High memory usage on dashboard load', array_merge($context, [
                'memory_threshold_exceeded' => true,
                'threshold_mb' => 50,
            ]));
        }
    }
}
