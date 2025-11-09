<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardService $dashboardService;
    protected User $adminUser;
    protected User $crewUser;
    protected User $residentUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = app(DashboardService::class);
        
        // Create roles
        Role::create(['name' => 'administrator']);
        Role::create(['name' => 'collection_crew']);
        Role::create(['name' => 'resident']);
        
        // Create test users with roles
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('administrator');
        
        $this->crewUser = User::factory()->create();
        $this->crewUser->assignRole('collection_crew');
        
        $this->residentUser = User::factory()->create();
        $this->residentUser->assignRole('resident');
    }

    /** @test */
    public function it_gets_admin_metrics_successfully()
    {
        $filters = ['period' => '30days'];
        
        $metrics = $this->dashboardService->getAdminMetrics($filters);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('collection_metrics', $metrics);
        $this->assertArrayHasKey('recycling_metrics', $metrics);
        $this->assertArrayHasKey('fleet_metrics', $metrics);
        $this->assertArrayHasKey('metadata', $metrics);
    }

    /** @test */
    public function it_caches_admin_metrics()
    {
        Cache::flush();
        
        $filters = ['period' => '30days'];
        
        // First call - should cache
        $metrics1 = $this->dashboardService->getAdminMetrics($filters);
        
        // Second call - should use cache
        $metrics2 = $this->dashboardService->getAdminMetrics($filters);

        $this->assertEquals($metrics1, $metrics2);
    }

    /** @test */
    public function it_gets_crew_metrics_successfully()
    {
        $metrics = $this->dashboardService->getCrewMetrics($this->crewUser);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('today_assignment', $metrics);
        $this->assertArrayHasKey('upcoming_assignments', $metrics);
        $this->assertArrayHasKey('performance', $metrics);
        $this->assertArrayHasKey('recent_logs', $metrics);
    }

    /** @test */
    public function it_gets_resident_metrics_successfully()
    {
        $metrics = $this->dashboardService->getResidentMetrics($this->residentUser);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('zone', $metrics);
        $this->assertArrayHasKey('next_collection', $metrics);
        $this->assertArrayHasKey('recent_reports', $metrics);
        $this->assertArrayHasKey('collection_schedule', $metrics);
    }

    /** @test */
    public function it_calculates_trends_correctly()
    {
        $periods = [
            'current' => 100,
            'previous' => 80,
        ];

        $trend = $this->dashboardService->calculateTrends('completion_rate', $periods);

        $this->assertIsArray($trend);
        $this->assertEquals('completion_rate', $trend['metric']);
        $this->assertEquals(100, $trend['current_value']);
        $this->assertEquals(80, $trend['previous_value']);
        $this->assertEquals(20, $trend['change']);
        $this->assertEquals(25, $trend['percentage_change']);
        $this->assertEquals('increasing', $trend['trend']);
    }

    /** @test */
    public function it_identifies_stable_trends()
    {
        $periods = [
            'current' => 100,
            'previous' => 98,
        ];

        $trend = $this->dashboardService->calculateTrends('completion_rate', $periods);

        $this->assertEquals('stable', $trend['trend']);
    }

    /** @test */
    public function it_handles_zero_previous_value_in_trends()
    {
        $periods = [
            'current' => 50,
            'previous' => 0,
        ];

        $trend = $this->dashboardService->calculateTrends('completion_rate', $periods);

        $this->assertEquals(100, $trend['percentage_change']);
        $this->assertEquals('increasing', $trend['trend']);
    }

    /** @test */
    public function it_generates_comparisons_correctly()
    {
        $currentData = [
            'collection_metrics' => [
                'total_collections' => 100,
                'completion_rate' => 85.0,
                'issues_reported' => 5,
            ],
        ];

        $previousData = [
            'collection_metrics' => [
                'total_collections' => 80,
                'completion_rate' => 80.0,
                'issues_reported' => 8,
            ],
        ];

        $comparisons = $this->dashboardService->generateComparisons($currentData, $previousData);

        $this->assertIsArray($comparisons);
        $this->assertArrayHasKey('collections', $comparisons);
        $this->assertArrayHasKey('total', $comparisons['collections']);
        $this->assertArrayHasKey('completion_rate', $comparisons['collections']);
    }

    /** @test */
    public function it_saves_user_preferences_successfully()
    {
        $preferences = [
            'widget_visibility' => [
                'collection_metrics' => true,
                'recycling_metrics' => false,
            ],
            'widget_order' => ['collection_metrics', 'fleet_metrics'],
        ];

        $result = $this->dashboardService->saveUserPreferences($this->adminUser, $preferences);

        $this->assertTrue($result);
        
        // Verify preferences were saved
        $savedPreferences = $this->dashboardService->getUserPreferences($this->adminUser);
        $this->assertArrayHasKey('widget_visibility', $savedPreferences);
        $this->assertArrayHasKey('widget_order', $savedPreferences);
        $this->assertEquals(['collection_metrics', 'fleet_metrics'], $savedPreferences['widget_order']);
    }

    /** @test */
    public function it_gets_user_preferences_with_defaults()
    {
        $preferences = $this->dashboardService->getUserPreferences($this->adminUser);

        $this->assertIsArray($preferences);
        $this->assertArrayHasKey('widget_visibility', $preferences);
        $this->assertArrayHasKey('widget_order', $preferences);
        $this->assertArrayHasKey('default_filters', $preferences);
    }

    /** @test */
    public function it_handles_empty_metrics_gracefully()
    {
        $filters = ['period' => '7days'];
        
        $metrics = $this->dashboardService->getAdminMetrics($filters);

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('pending_items', $metrics);
        $this->assertEquals(0, $metrics['pending_items']['unassigned_routes']);
    }

    /** @test */
    public function it_parses_date_ranges_correctly()
    {
        $filters = ['period' => '7days'];
        $metrics = $this->dashboardService->getAdminMetrics($filters);
        
        $this->assertArrayHasKey('metadata', $metrics);
        $this->assertArrayHasKey('period_start', $metrics['metadata']);
        $this->assertArrayHasKey('period_end', $metrics['metadata']);
    }

    /** @test */
    public function it_handles_custom_date_ranges()
    {
        $filters = [
            'period' => 'custom',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
        ];
        
        $metrics = $this->dashboardService->getAdminMetrics($filters);

        $this->assertEquals('2025-01-01', $metrics['metadata']['period_start']);
        $this->assertEquals('2025-01-31', $metrics['metadata']['period_end']);
    }

    /** @test */
    public function it_gets_comparison_metrics_for_previous_week()
    {
        $currentStart = Carbon::parse('2025-01-08');
        $currentEnd = Carbon::parse('2025-01-14');

        $comparisons = $this->dashboardService->getComparisonMetrics($currentStart, $currentEnd, 'previous_week');

        $this->assertIsArray($comparisons);
        $this->assertArrayHasKey('period_info', $comparisons);
        $this->assertEquals('previous_week', $comparisons['period_info']['comparison_type']);
    }

    /** @test */
    public function it_dismisses_alerts_successfully()
    {
        $this->actingAs($this->adminUser);
        
        $alertId = 'assignment_123';
        $result = $this->dashboardService->dismissAlert($alertId);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_invalid_alert_ids()
    {
        $this->actingAs($this->adminUser);
        
        $alertId = 'invalid_format';
        $result = $this->dashboardService->dismissAlert($alertId);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_gets_drill_down_data_for_collections()
    {
        $filters = ['period' => '30days'];
        
        $drillDown = $this->dashboardService->getDrillDownData('collections_today', $filters, $this->adminUser);

        $this->assertIsArray($drillDown);
    }

    /** @test */
    public function it_handles_unknown_metric_types_in_drill_down()
    {
        $filters = ['period' => '30days'];
        
        $drillDown = $this->dashboardService->getDrillDownData('unknown_metric', $filters, $this->adminUser);

        $this->assertArrayHasKey('error', $drillDown);
    }

    /** @test */
    public function it_gets_geographic_distribution_with_filters()
    {
        $filters = ['period' => '30days'];
        
        $geoData = $this->dashboardService->getGeographicDistribution($filters);

        $this->assertIsArray($geoData);
        $this->assertArrayHasKey('collections_by_zone', $geoData);
        $this->assertArrayHasKey('reports_by_zone', $geoData);
    }

    /** @test */
    public function it_gets_chart_data_for_collection_trends()
    {
        $filters = ['period' => '30days'];
        
        $chartData = $this->dashboardService->getChartData('collection_trends', $filters, $this->adminUser);

        $this->assertIsArray($chartData);
        $this->assertArrayHasKey('labels', $chartData);
        $this->assertArrayHasKey('values', $chartData);
    }

    /** @test */
    public function it_handles_invalid_chart_types()
    {
        $filters = ['period' => '30days'];
        
        $chartData = $this->dashboardService->getChartData('invalid_type', $filters, $this->adminUser);

        $this->assertIsArray($chartData);
        $this->assertEmpty($chartData['labels']);
        $this->assertEmpty($chartData['values']);
    }
}
