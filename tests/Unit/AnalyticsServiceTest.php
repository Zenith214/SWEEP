<?php

namespace Tests\Unit;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\RecyclingLog;
use App\Models\Report;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = app(AnalyticsService::class);
    }

    /** @test */
    public function it_calculates_collection_metrics_correctly()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getCollectionMetrics($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_collections', $metrics);
        $this->assertArrayHasKey('completed', $metrics);
        $this->assertArrayHasKey('completion_rate', $metrics);
        $this->assertArrayHasKey('period_start', $metrics);
        $this->assertArrayHasKey('period_end', $metrics);
    }

    /** @test */
    public function it_handles_empty_collection_data()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getCollectionMetrics($startDate, $endDate);

        $this->assertEquals(0, $metrics['total_collections']);
        $this->assertEquals(0, $metrics['completed']);
        $this->assertEquals(0, $metrics['completion_rate']);
    }

    /** @test */
    public function it_calculates_recycling_metrics_correctly()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getRecyclingMetrics($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_weight', $metrics);
        $this->assertArrayHasKey('total_logs', $metrics);
        $this->assertArrayHasKey('material_breakdown', $metrics);
        $this->assertArrayHasKey('recycling_rate', $metrics);
    }

    /** @test */
    public function it_handles_empty_recycling_data()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getRecyclingMetrics($startDate, $endDate);

        $this->assertEquals(0, $metrics['total_weight']);
        $this->assertEquals(0, $metrics['total_logs']);
        $this->assertEmpty($metrics['material_breakdown']);
    }

    /** @test */
    public function it_calculates_fleet_metrics_correctly()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getFleetMetrics($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_trucks', $metrics);
        $this->assertArrayHasKey('operational', $metrics);
        $this->assertArrayHasKey('maintenance', $metrics);
        $this->assertArrayHasKey('average_utilization', $metrics);
    }

    /** @test */
    public function it_calculates_crew_performance_correctly()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getCrewPerformance($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('active_crew_count', $metrics);
        $this->assertArrayHasKey('total_collections', $metrics);
        $this->assertArrayHasKey('avg_collections_per_crew', $metrics);
        $this->assertArrayHasKey('top_performers', $metrics);
    }

    /** @test */
    public function it_calculates_report_statistics_correctly()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getReportStatistics($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_reports', $metrics);
        $this->assertArrayHasKey('by_status', $metrics);
        $this->assertArrayHasKey('by_type', $metrics);
    }

    /** @test */
    public function it_calculates_route_performance_correctly()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getRoutePerformance($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('all_routes', $metrics);
        $this->assertArrayHasKey('routes_with_lowest_completion', $metrics);
        $this->assertArrayHasKey('routes_with_most_issues', $metrics);
    }

    /** @test */
    public function it_calculates_usage_statistics_correctly()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getUsageStatistics($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('active_users_by_role', $metrics);
        $this->assertArrayHasKey('new_resident_registrations', $metrics);
    }

    /** @test */
    public function it_calculates_geographic_distribution_correctly()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getGeographicDistribution($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('collections_by_zone', $metrics);
        $this->assertArrayHasKey('reports_by_zone', $metrics);
        $this->assertArrayHasKey('zones_without_collections', $metrics);
    }

    /** @test */
    public function it_handles_operational_costs_when_not_available()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getOperationalCosts($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertFalse($metrics['available']);
        $this->assertEquals(0, $metrics['total_costs']);
    }

    /** @test */
    public function it_generates_chart_data_for_completion_trend()
    {
        $trendData = [
            ['date_formatted' => 'Jan 01', 'completion_rate' => 85.5],
            ['date_formatted' => 'Jan 02', 'completion_rate' => 90.0],
        ];

        $chartData = $this->analyticsService->generateChartData('completion_trend', $trendData);

        $this->assertArrayHasKey('labels', $chartData);
        $this->assertArrayHasKey('datasets', $chartData);
        $this->assertCount(2, $chartData['labels']);
    }

    /** @test */
    public function it_handles_invalid_date_ranges()
    {
        $startDate = Carbon::parse('2025-12-31');
        $endDate = Carbon::parse('2025-01-01');

        $metrics = $this->analyticsService->getCollectionMetrics($startDate, $endDate);

        $this->assertIsArray($metrics);
        $this->assertEquals(0, $metrics['total_collections']);
    }

    /** @test */
    public function it_handles_missing_data_gracefully()
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        $metrics = $this->analyticsService->getCrewPerformance($startDate, $endDate);

        $this->assertEquals(0, $metrics['active_crew_count']);
        $this->assertEmpty($metrics['top_performers']);
    }
}
