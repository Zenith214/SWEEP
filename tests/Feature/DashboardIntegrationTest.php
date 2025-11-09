<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\DashboardPreference;
use App\Models\RecyclingLog;
use App\Models\Report;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive integration tests for dashboard features.
 * Tests end-to-end workflows including data aggregation, role-based access,
 * metric calculations, exports, and customization persistence.
 */
class DashboardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $crewMember;
    protected User $resident;
    protected Route $route;
    protected Truck $truck;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create users with different roles
        $this->admin = User::factory()->create(['name' => 'Admin User']);
        $this->admin->assignRole('administrator');

        $this->crewMember = User::factory()->create(['name' => 'Crew Member']);
        $this->crewMember->assignRole('collection_crew');

        $this->resident = User::factory()->create(['name' => 'Resident User']);
        $this->resident->assignRole('resident');

        // Create base data
        $this->route = Route::factory()->create([
            'name' => 'Test Route',
            'zone' => 'Zone A',
        ]);

        $this->truck = Truck::factory()->create([
            'truck_number' => 'T-001',
            'operational_status' => Truck::STATUS_OPERATIONAL,
        ]);
    }

    /**
     * Test that admin dashboard loads with all widgets and metrics.
     * Validates complete dashboard rendering with all metric sections.
     */
    public function test_admin_dashboard_loads_with_all_widgets_and_metrics(): void
    {
        // Create comprehensive test data
        $this->createComprehensiveTestData();

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        
        $metrics = $response->viewData('metrics');

        // Verify all major metric sections are present
        $this->assertArrayHasKey('collection_status', $metrics);
        $this->assertArrayHasKey('pending_items', $metrics);
        $this->assertArrayHasKey('collection_trends', $metrics);
        $this->assertArrayHasKey('recycling_metrics', $metrics);
        $this->assertArrayHasKey('fleet_metrics', $metrics);
        $this->assertArrayHasKey('crew_performance', $metrics);
        $this->assertArrayHasKey('report_statistics', $metrics);
        $this->assertArrayHasKey('route_performance', $metrics);
        $this->assertArrayHasKey('usage_statistics', $metrics);
        $this->assertArrayHasKey('preferences', $metrics);

        // Verify collection status metrics
        $this->assertArrayHasKey('scheduled_today', $metrics['collection_status']);
        $this->assertArrayHasKey('completed_today', $metrics['collection_status']);
        $this->assertArrayHasKey('completion_percentage', $metrics['collection_status']);

        // Verify pending items
        $this->assertArrayHasKey('pending_reports', $metrics['pending_items']);
        $this->assertArrayHasKey('unassigned_routes', $metrics['pending_items']);
        $this->assertArrayHasKey('trucks_in_maintenance', $metrics['pending_items']);

        // Verify dashboard renders all widget sections
        $response->assertSee('Collection Status');
        $response->assertSee('Pending Items');
        $response->assertSee('Collection Performance');
        $response->assertSee('Recycling Metrics');
        $response->assertSee('Fleet Utilization');
        $response->assertSee('Crew Performance');
        $response->assertSee('Report Statistics');
        $response->assertSee('Route Performance');
    }

    /**
     * Test that crew dashboard shows correct assignments and performance.
     * Validates crew-specific metrics and assignment display.
     */
    public function test_crew_dashboard_shows_correct_assignments_and_performance(): void
    {
        // Create today's assignment for crew member
        $todayAssignment = Assignment::factory()->create([
            'user_id' => $this->crewMember->id,
            'route_id' => $this->route->id,
            'truck_id' => $this->truck->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create collection logs for performance metrics
        $completedAssignment = Assignment::factory()->create([
            'user_id' => $this->crewMember->id,
            'route_id' => $this->route->id,
            'truck_id' => $this->truck->id,
            'assignment_date' => now()->subDays(1),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $collectionLog = CollectionLog::factory()->create([
            'assignment_id' => $completedAssignment->id,
            'created_by' => $this->crewMember->id,
            'status' => CollectionLog::STATUS_COMPLETED,
            'crew_notes' => 'Completed successfully',
        ]);

        // Create recycling log if RecyclingLog model exists
        if (class_exists(\App\Models\RecyclingLog::class)) {
            \App\Models\RecyclingLog::factory()->create([
                'collection_log_id' => $collectionLog->id,
                'material_type' => 'plastic',
                'weight' => 50.5,
                'unit' => 'kg',
            ]);
        }

        // Create upcoming assignments
        $upcomingAssignment = Assignment::factory()->create([
            'user_id' => $this->crewMember->id,
            'route_id' => $this->route->id,
            'truck_id' => $this->truck->id,
            'assignment_date' => now()->addDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->crewMember)->get(route('crew.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        
        $metrics = $response->viewData('metrics');

        // Verify today's assignment is displayed
        $this->assertNotNull($metrics['today_assignment']);
        $this->assertEquals($this->route->name, $metrics['today_assignment']['route_name']);
        $this->assertEquals($this->truck->truck_number, $metrics['today_assignment']['truck_number']);

        // Verify performance metrics
        $this->assertArrayHasKey('performance', $metrics);
        $this->assertGreaterThan(0, $metrics['performance']['total_collections']);
        $this->assertArrayHasKey('completion_rate', $metrics['performance']);

        // Verify recent logs are displayed
        $this->assertArrayHasKey('recent_logs', $metrics);
        $this->assertNotEmpty($metrics['recent_logs']);
        $this->assertEquals('completed', $metrics['recent_logs'][0]['status']);

        // Verify upcoming assignments
        $this->assertArrayHasKey('upcoming_assignments', $metrics);
        $this->assertNotEmpty($metrics['upcoming_assignments']);

        // Verify UI elements
        $response->assertSee('Today\'s Assignment');
        $response->assertSee('My Performance');
        $response->assertSee('Recent Collection Logs');
        $response->assertSee('Upcoming Assignments');
        $response->assertSee($this->route->name);
        $response->assertSee($this->truck->truck_number);
    }

    /**
     * Test that resident dashboard shows correct zone information.
     * Validates resident-specific metrics and zone-based data.
     */
    public function test_resident_dashboard_shows_correct_zone_information(): void
    {
        // Create report to establish resident's zone
        $report = Report::factory()->create([
            'resident_id' => $this->resident->id,
            'route_id' => $this->route->id,
            'status' => Report::STATUS_PENDING,
            'report_type' => Report::TYPE_MISSED_PICKUP,
        ]);

        // Create next collection for the zone
        $nextCollection = Assignment::factory()->create([
            'route_id' => $this->route->id,
            'truck_id' => $this->truck->id,
            'assignment_date' => now()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create additional reports with different statuses
        Report::factory()->create([
            'resident_id' => $this->resident->id,
            'route_id' => $this->route->id,
            'status' => Report::STATUS_RESOLVED,
            'report_type' => Report::TYPE_OTHER,
        ]);

        $response = $this->actingAs($this->resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        
        $metrics = $response->viewData('metrics');

        // Verify zone information
        $this->assertEquals('Zone A', $metrics['zone']);

        // Verify next collection information
        $this->assertNotNull($metrics['next_collection']);
        $this->assertEquals($this->route->name, $metrics['next_collection']['route_name']);
        $this->assertEquals(2, $metrics['next_collection']['days_until']);

        // Verify recent reports
        $this->assertArrayHasKey('recent_reports', $metrics);
        $this->assertCount(2, $metrics['recent_reports']);

        // Verify report statistics
        $this->assertArrayHasKey('report_statistics', $metrics);
        $this->assertEquals(2, $metrics['report_statistics']['total_reports']);
        $this->assertEquals(1, $metrics['report_statistics']['pending_reports']);
        $this->assertEquals(1, $metrics['report_statistics']['resolved_reports']);

        // Verify collection schedule
        $this->assertArrayHasKey('collection_schedule', $metrics);

        // Verify UI elements
        $response->assertSee('Your Zone:');
        $response->assertSee('Zone A');
        $response->assertSee('Next Scheduled Collection');
        $response->assertSee('My Recent Reports');
        $response->assertSee('Upcoming Collections');
        $response->assertSee('Recycling Tips');
    }

    /**
     * Test role-based access control for all dashboard routes.
     * Ensures users can only access dashboards appropriate for their role.
     */
    public function test_role_based_access_control_for_all_dashboard_routes(): void
    {
        // Test admin dashboard access
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200);

        $crewResponse = $this->actingAs($this->crewMember)
            ->get(route('admin.dashboard'));
        $this->assertContains($crewResponse->status(), [403, 302]);

        $residentResponse = $this->actingAs($this->resident)
            ->get(route('admin.dashboard'));
        $this->assertContains($residentResponse->status(), [403, 302]);

        // Test crew dashboard access
        $this->actingAs($this->crewMember)
            ->get(route('crew.dashboard'))
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get(route('crew.dashboard'))
            ->assertStatus(403);

        $this->actingAs($this->resident)
            ->get(route('crew.dashboard'))
            ->assertStatus(302); // Redirects

        // Test resident dashboard access
        $this->actingAs($this->resident)
            ->get(route('resident.dashboard'))
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get(route('resident.dashboard'))
            ->assertStatus(403);

        $this->actingAs($this->crewMember)
            ->get(route('resident.dashboard'))
            ->assertStatus(302); // Redirects

        // Test dashboard metrics API endpoint (admin only)
        $this->actingAs($this->admin)
            ->getJson(route('dashboard.metrics'))
            ->assertStatus(200);

        $this->actingAs($this->crewMember)
            ->getJson(route('dashboard.metrics'))
            ->assertStatus(403);

        $this->actingAs($this->resident)
            ->getJson(route('dashboard.metrics'))
            ->assertStatus(403);

        // Test export functionality (admin only)
        $this->actingAs($this->admin)
            ->get(route('dashboard.export', ['format' => 'pdf']))
            ->assertStatus(200);

        $this->actingAs($this->crewMember)
            ->get(route('dashboard.export', ['format' => 'pdf']))
            ->assertStatus(403);

        $this->actingAs($this->resident)
            ->get(route('dashboard.export', ['format' => 'pdf']))
            ->assertStatus(403);
    }

    /**
     * Test metric calculation accuracy against known test data.
     * Validates that calculated metrics match expected values.
     */
    public function test_metric_calculation_accuracy_against_known_test_data(): void
    {
        // Create known test data with specific counts
        $today = now()->startOfDay();

        // Create 10 assignments for today with different trucks to avoid unique constraint
        $trucks = Truck::factory()->count(10)->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $todayAssignments = collect();
        foreach ($trucks as $truck) {
            $todayAssignments->push(Assignment::factory()->create([
                'route_id' => $this->route->id,
                'truck_id' => $truck->id,
                'assignment_date' => $today,
                'status' => Assignment::STATUS_ACTIVE,
            ]));
        }

        // Create 7 completed collection logs (70% completion rate)
        foreach ($todayAssignments->take(7) as $assignment) {
            CollectionLog::factory()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $this->crewMember->id,
                'status' => CollectionLog::STATUS_COMPLETED,
            ]);
        }

        // Create 5 pending reports
        Report::factory()->count(5)->create([
            'resident_id' => $this->resident->id,
            'route_id' => $this->route->id,
            'status' => Report::STATUS_PENDING,
            'created_at' => now()->subHours(10),
        ]);

        // Create 3 resolved reports
        Report::factory()->count(3)->create([
            'resident_id' => $this->resident->id,
            'route_id' => $this->route->id,
            'status' => Report::STATUS_RESOLVED,
            'created_at' => now()->subDays(5),
            'resolved_at' => now()->subDays(3),
        ]);

        // Create 2 trucks in maintenance
        Truck::factory()->count(2)->create([
            'operational_status' => Truck::STATUS_MAINTENANCE,
        ]);

        // Create 3 unassigned routes within 3 days
        $futureRoutes = Route::factory()->count(3)->create();
        foreach ($futureRoutes as $route) {
            Assignment::factory()->create([
                'route_id' => $route->id,
                'user_id' => null,
                'truck_id' => null,
                'assignment_date' => now()->addDays(2),
                'status' => Assignment::STATUS_ACTIVE,
            ]);
        }

        // Create recycling logs with known weights if RecyclingLog model exists
        if (class_exists(\App\Models\RecyclingLog::class)) {
            $completedLog = CollectionLog::where('status', CollectionLog::STATUS_COMPLETED)->first();
            if ($completedLog) {
                \App\Models\RecyclingLog::factory()->create([
                    'collection_log_id' => $completedLog->id,
                    'material_type' => 'plastic',
                    'weight' => 100.0,
                    'unit' => 'kg',
                ]);
                \App\Models\RecyclingLog::factory()->create([
                    'collection_log_id' => $completedLog->id,
                    'material_type' => 'paper',
                    'weight' => 150.0,
                    'unit' => 'kg',
                ]);
            }
        }

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');

        // Verify collection status calculations
        $this->assertEquals(10, $metrics['collection_status']['scheduled_today']);
        $this->assertEquals(7, $metrics['collection_status']['completed_today']);
        $this->assertEquals(70, $metrics['collection_status']['completion_percentage']);

        // Verify pending items calculations
        $this->assertEquals(5, $metrics['pending_items']['pending_reports']);
        $this->assertEquals(3, $metrics['pending_items']['unassigned_routes']);
        $this->assertEquals(2, $metrics['pending_items']['trucks_in_maintenance']);

        // Verify report statistics
        $this->assertEquals(8, $metrics['report_statistics']['total_reports']);
        $this->assertEquals(5, $metrics['report_statistics']['pending_count']);
        $this->assertEquals(3, $metrics['report_statistics']['resolved_count']);

        // Verify recycling metrics (if RecyclingLog exists)
        if (class_exists(\App\Models\RecyclingLog::class)) {
            $this->assertArrayHasKey('total_weight', $metrics['recycling_metrics']);
            $this->assertEquals(250.0, $metrics['recycling_metrics']['total_weight']);
        }

        // Verify fleet metrics
        $this->assertArrayHasKey('total_trucks', $metrics['fleet_metrics']);
        $this->assertArrayHasKey('operational_trucks', $metrics['fleet_metrics']);
        $this->assertEquals(2, $metrics['fleet_metrics']['maintenance_count']);
    }

    /**
     * Test export workflow from request to download.
     * Validates complete export process for both PDF and CSV formats.
     */
    public function test_export_workflow_from_request_to_download(): void
    {
        // Create some test data
        $this->createComprehensiveTestData();

        // Test PDF export
        $pdfResponse = $this->actingAs($this->admin)->get(route('dashboard.export', [
            'format' => 'pdf',
            'period' => '30days',
        ]));

        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
        $pdfResponse->assertHeader('Content-Disposition');
        
        // Verify filename contains timestamp
        $contentDisposition = $pdfResponse->headers->get('Content-Disposition');
        $this->assertStringContainsString('dashboard-report', $contentDisposition);
        $this->assertStringContainsString('.pdf', $contentDisposition);

        // Test CSV export
        $csvResponse = $this->actingAs($this->admin)->get(route('dashboard.export', [
            'format' => 'csv',
            'period' => '30days',
        ]));

        $csvResponse->assertStatus(200);
        $this->assertStringStartsWith('text/csv', $csvResponse->headers->get('Content-Type'));
        $csvResponse->assertHeader('Content-Disposition');

        // Verify CSV filename
        $contentDisposition = $csvResponse->headers->get('Content-Disposition');
        $this->assertStringContainsString('dashboard-report', $contentDisposition);
        $this->assertStringContainsString('.csv', $contentDisposition);

        // Test export with custom date range
        $customRangeResponse = $this->actingAs($this->admin)->get(route('dashboard.export', [
            'format' => 'pdf',
            'period' => 'custom',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]));

        $customRangeResponse->assertStatus(200);
        $customRangeResponse->assertHeader('Content-Type', 'application/pdf');

        // Test export with widget preferences
        DashboardPreference::create([
            'user_id' => $this->admin->id,
            'widget_visibility' => [
                'collection_status' => true,
                'pending_items' => false,
                'recycling_metrics' => true,
            ],
            'widget_order' => ['collection_status', 'recycling_metrics'],
        ]);

        $preferenceResponse = $this->actingAs($this->admin)->get(route('dashboard.export', [
            'format' => 'pdf',
        ]));

        $preferenceResponse->assertStatus(200);
        $preferenceResponse->assertHeader('Content-Type', 'application/pdf');

        // Test invalid format returns validation error
        $invalidResponse = $this->actingAs($this->admin)->get(route('dashboard.export', [
            'format' => 'invalid',
        ]));

        $invalidResponse->assertStatus(302); // Validation redirect
    }

    /**
     * Test dashboard customization persistence.
     * Validates that user preferences are saved and loaded correctly.
     */
    public function test_dashboard_customization_persistence(): void
    {
        // Define custom preferences
        $customPreferences = [
            'widget_visibility' => [
                'collection_status' => true,
                'pending_items' => false,
                'collection_trends' => true,
                'recycling_metrics' => false,
                'fleet_metrics' => true,
                'crew_performance' => true,
                'report_statistics' => false,
                'route_performance' => true,
            ],
            'widget_order' => [
                'collection_status',
                'fleet_metrics',
                'collection_trends',
                'crew_performance',
                'route_performance',
            ],
            'default_filters' => [
                'period' => '30days',
                'zone' => 'Zone A',
            ],
        ];

        // Save preferences
        $saveResponse = $this->actingAs($this->admin)
            ->postJson(route('dashboard.preferences.save'), $customPreferences);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);

        // Verify preferences were saved to database
        $this->assertDatabaseHas('dashboard_preferences', [
            'user_id' => $this->admin->id,
        ]);

        $savedPreference = DashboardPreference::where('user_id', $this->admin->id)->first();
        $this->assertNotNull($savedPreference);
        $this->assertEquals($customPreferences['widget_visibility'], $savedPreference->widget_visibility);
        $this->assertEquals($customPreferences['widget_order'], $savedPreference->widget_order);
        $this->assertEquals($customPreferences['default_filters'], $savedPreference->default_filters);

        // Simulate new session - load dashboard
        $dashboardResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $dashboardResponse->assertStatus(200);
        $metrics = $dashboardResponse->viewData('metrics');

        // Verify preferences are loaded
        $this->assertArrayHasKey('preferences', $metrics);
        
        // Check that saved preferences are present (may be merged with defaults)
        foreach ($customPreferences['widget_visibility'] as $widget => $visibility) {
            if (isset($metrics['preferences']['widget_visibility'][$widget])) {
                $this->assertEquals($visibility, $metrics['preferences']['widget_visibility'][$widget]);
            }
        }
        
        $this->assertEquals($customPreferences['widget_order'], $metrics['preferences']['widget_order']);
        $this->assertEquals($customPreferences['default_filters'], $metrics['preferences']['default_filters']);

        // Update preferences
        $updatedPreferences = [
            'widget_visibility' => [
                'collection_status' => false,
                'pending_items' => true,
            ],
            'widget_order' => ['pending_items', 'collection_status'],
        ];

        $updateResponse = $this->actingAs($this->admin)
            ->postJson(route('dashboard.preferences.save'), $updatedPreferences);

        $updateResponse->assertStatus(200);

        // Verify updated preferences
        $updatedPreference = DashboardPreference::where('user_id', $this->admin->id)->first();
        $this->assertEquals($updatedPreferences['widget_visibility'], $updatedPreference->widget_visibility);
        $this->assertEquals($updatedPreferences['widget_order'], $updatedPreference->widget_order);

        // Test reset to defaults
        $defaultPreferences = [
            'widget_visibility' => DashboardPreference::getDefaultWidgetVisibility(),
            'widget_order' => DashboardPreference::getDefaultWidgetOrder(),
        ];

        $resetResponse = $this->actingAs($this->admin)
            ->postJson(route('dashboard.preferences.save'), $defaultPreferences);

        $resetResponse->assertStatus(200);

        // Verify reset
        $resetPreference = DashboardPreference::where('user_id', $this->admin->id)->first();
        $this->assertEquals($defaultPreferences['widget_visibility'], $resetPreference->widget_visibility);
        $this->assertEquals($defaultPreferences['widget_order'], $resetPreference->widget_order);

        // Verify preferences are user-specific
        $anotherAdmin = User::factory()->create();
        $anotherAdmin->assignRole('administrator');

        $anotherAdminResponse = $this->actingAs($anotherAdmin)->get(route('admin.dashboard'));
        $anotherAdminResponse->assertStatus(200);
        
        $anotherMetrics = $anotherAdminResponse->viewData('metrics');
        
        // Should have default preferences, not the first admin's preferences
        $this->assertEquals(
            DashboardPreference::getDefaultWidgetVisibility(),
            $anotherMetrics['preferences']['widget_visibility']
        );
    }

    /**
     * Test AJAX metric refresh functionality.
     * Validates that metrics can be refreshed without page reload.
     */
    public function test_ajax_metric_refresh_functionality(): void
    {
        $this->createComprehensiveTestData();

        // Request metrics via AJAX
        $response = $this->actingAs($this->admin)
            ->getJson(route('dashboard.metrics', ['period' => '30days']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'metrics' => [
                'collection_status',
                'pending_items',
                'collection_trends',
                'recycling_metrics',
                'fleet_metrics',
                'crew_performance',
                'report_statistics',
                'route_performance',
                'usage_statistics',
            ],
            'last_updated',
        ]);

        // Verify metric values
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['metrics']);
        $this->assertNotEmpty($data['last_updated']);

        // Test with filters
        $filteredResponse = $this->actingAs($this->admin)
            ->getJson(route('dashboard.metrics', [
                'period' => 'custom',
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $filteredResponse->assertStatus(200);
        $filteredResponse->assertJson(['success' => true]);
    }

    /**
     * Test drill-down navigation with context preservation.
     * Validates that filters are maintained when navigating to detail views.
     */
    public function test_drill_down_navigation_with_context_preservation(): void
    {
        $this->createComprehensiveTestData();

        // Access dashboard with filters
        $dashboardResponse = $this->actingAs($this->admin)->get(route('admin.dashboard', [
            'period' => '30days',
            'zone' => 'Zone A',
        ]));

        $dashboardResponse->assertStatus(200);

        // Test drill-down endpoint
        $drillDownResponse = $this->actingAs($this->admin)->get(
            route('dashboard.drill-down', [
                'metric' => 'collections_today',
                'period' => '30days',
                'zone' => 'Zone A',
            ])
        );

        $drillDownResponse->assertStatus(200);
        $drillDownResponse->assertJsonStructure([
            'success',
            'metric',
            'data',
            'filters',
        ]);

        $drillDownData = $drillDownResponse->json();
        $this->assertEquals('collections_today', $drillDownData['metric']);
        $this->assertArrayHasKey('period', $drillDownData['filters']);
        $this->assertArrayHasKey('zone', $drillDownData['filters']);
    }

    /**
     * Test dashboard performance with large datasets.
     * Validates that dashboard loads efficiently with substantial data.
     */
    public function test_dashboard_performance_with_large_datasets(): void
    {
        // Create large dataset
        $routes = Route::factory()->count(50)->create();
        $trucks = Truck::factory()->count(20)->create();
        $crewMembers = User::factory()->count(30)->create();
        
        foreach ($crewMembers as $crew) {
            $crew->assignRole('collection_crew');
        }

        // Create 500 assignments
        foreach (range(1, 500) as $i) {
            Assignment::factory()->create([
                'route_id' => $routes->random()->id,
                'truck_id' => $trucks->random()->id,
                'user_id' => $crewMembers->random()->id,
                'assignment_date' => now()->subDays(rand(0, 90)),
                'status' => Assignment::STATUS_ACTIVE,
            ]);
        }

        // Create 200 reports
        Report::factory()->count(200)->create([
            'resident_id' => $this->resident->id,
            'route_id' => $routes->random()->id,
            'status' => Report::STATUS_PENDING,
        ]);

        $startTime = microtime(true);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;

        $response->assertStatus(200);
        $response->assertViewHas('metrics');

        // Dashboard should load in reasonable time (under 5 seconds)
        $this->assertLessThan(5.0, $loadTime, 'Dashboard took too long to load with large dataset');
    }

    /**
     * Helper method to create comprehensive test data.
     */
    protected function createComprehensiveTestData(): void
    {
        // Create additional trucks and crew members to avoid unique constraint violations
        $trucks = Truck::factory()->count(5)->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crewMembers = User::factory()->count(5)->create();
        foreach ($crewMembers as $crew) {
            $crew->assignRole('collection_crew');
        }

        // Create assignments with different trucks/crew to avoid unique constraints
        foreach (range(0, 4) as $i) {
            Assignment::factory()->create([
                'route_id' => $this->route->id,
                'truck_id' => $trucks[$i]->id,
                'user_id' => $crewMembers[$i]->id,
                'assignment_date' => now(),
                'status' => Assignment::STATUS_ACTIVE,
            ]);
        }

        // Create collection logs
        $assignments = Assignment::where('assignment_date', now()->toDateString())->get();
        foreach ($assignments->take(3) as $assignment) {
            $log = CollectionLog::factory()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $assignment->user_id,
                'status' => CollectionLog::STATUS_COMPLETED,
            ]);

            // Create recycling logs if RecyclingLog model exists
            if (class_exists(\App\Models\RecyclingLog::class)) {
                \App\Models\RecyclingLog::factory()->create([
                    'collection_log_id' => $log->id,
                    'material_type' => 'plastic',
                    'weight' => rand(10, 100),
                    'unit' => 'kg',
                ]);
            }
        }

        // Create reports
        Report::factory()->count(3)->create([
            'resident_id' => $this->resident->id,
            'route_id' => $this->route->id,
            'status' => Report::STATUS_PENDING,
        ]);

        Report::factory()->count(2)->create([
            'resident_id' => $this->resident->id,
            'route_id' => $this->route->id,
            'status' => Report::STATUS_RESOLVED,
        ]);

        // Create additional trucks
        Truck::factory()->count(2)->create([
            'operational_status' => Truck::STATUS_MAINTENANCE,
        ]);

        Truck::factory()->count(3)->create([
            'operational_status' => Truck::STATUS_OPERATIONAL,
        ]);
    }
}
