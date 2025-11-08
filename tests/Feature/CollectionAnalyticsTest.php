<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\CollectionLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // 23.1 Test completion rate calculations
    // ========================================

    public function test_completion_rate_calculation_for_date_range(): void
    {
        $service = new CollectionLogService();
        
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();

        $startDate = Carbon::now()->subDays(9)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create 10 assignments with logs in the date range (one per day)
        for ($i = 0; $i < 10; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            // 7 completed, 2 incomplete, 1 issue
            if ($i < 7) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew->id,
                ]);
            } elseif ($i < 9) {
                CollectionLog::factory()->incomplete()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew->id,
                ]);
            } else {
                CollectionLog::factory()->withIssue()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew->id,
                ]);
            }
        }

        $completionRate = $service->getCompletionRate($startDate, $endDate);

        // 7 completed out of 10 = 70%
        $this->assertEquals(70.0, $completionRate);
    }

    public function test_completion_rate_with_route_filter(): void
    {
        $service = new CollectionLogService();
        
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $truck1 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $truck2 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route1 = Route::factory()->create();
        $route2 = Route::factory()->create();

        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create logs for route1 (3 completed, 1 incomplete) - use different trucks
        for ($i = 0; $i < 4; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $i < 2 ? $truck1->id : $truck2->id,
                'user_id' => $crew->id,
                'route_id' => $route1->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            if ($i < 3) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew->id,
                ]);
            } else {
                CollectionLog::factory()->incomplete()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew->id,
                ]);
            }
        }

        // Create logs for route2 (all completed) - use different dates
        for ($i = 0; $i < 3; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck1->id,
                'user_id' => $crew->id,
                'route_id' => $route2->id,
                'assignment_date' => $startDate->copy()->addDays($i + 4),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->completed()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
            ]);
        }

        // Test route1 filter: 3 completed out of 4 = 75%
        $completionRate = $service->getCompletionRate($startDate, $endDate, ['route_id' => $route1->id]);
        $this->assertEquals(75.0, $completionRate);

        // Test route2 filter: 3 completed out of 3 = 100%
        $completionRate = $service->getCompletionRate($startDate, $endDate, ['route_id' => $route2->id]);
        $this->assertEquals(100.0, $completionRate);
    }

    public function test_completion_rate_with_user_filter(): void
    {
        $service = new CollectionLogService();
        
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');
        
        $truck1 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $truck2 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();

        $startDate = Carbon::now()->subDays(9)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create logs for crew1 (4 completed, 1 incomplete)
        for ($i = 0; $i < 5; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck1->id,
                'user_id' => $crew1->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            if ($i < 4) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew1->id,
                ]);
            } else {
                CollectionLog::factory()->incomplete()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew1->id,
                ]);
            }
        }

        // Create logs for crew2 (2 completed, 2 incomplete)
        for ($i = 0; $i < 4; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck2->id,
                'user_id' => $crew2->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            if ($i < 2) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew2->id,
                ]);
            } else {
                CollectionLog::factory()->incomplete()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew2->id,
                ]);
            }
        }

        // Test crew1 filter: 4 completed out of 5 = 80%
        $completionRate = $service->getCompletionRate($startDate, $endDate, ['user_id' => $crew1->id]);
        $this->assertEquals(80.0, $completionRate);

        // Test crew2 filter: 2 completed out of 4 = 50%
        $completionRate = $service->getCompletionRate($startDate, $endDate, ['user_id' => $crew2->id]);
        $this->assertEquals(50.0, $completionRate);
    }

    public function test_status_breakdown_calculations(): void
    {
        $service = new CollectionLogService();
        
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();

        $startDate = Carbon::now()->subDays(10)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create 5 completed logs
        for ($i = 0; $i < 5; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->completed()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
            ]);
        }

        // Create 3 incomplete logs
        for ($i = 5; $i < 8; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->incomplete()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
            ]);
        }

        // Create 2 issue logs
        for ($i = 8; $i < 10; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->withIssue()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
            ]);
        }

        $breakdown = $service->getStatusBreakdown($startDate, $endDate);

        $this->assertEquals(5, $breakdown[CollectionLog::STATUS_COMPLETED]);
        $this->assertEquals(3, $breakdown[CollectionLog::STATUS_INCOMPLETE]);
        $this->assertEquals(2, $breakdown[CollectionLog::STATUS_ISSUE_REPORTED]);
        $this->assertEquals(0, $breakdown[CollectionLog::STATUS_PENDING]);
    }

    public function test_completion_rate_returns_zero_for_no_logs(): void
    {
        $service = new CollectionLogService();
        
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        $completionRate = $service->getCompletionRate($startDate, $endDate);

        $this->assertEquals(0.0, $completionRate);
    }

    // ========================================
    // 23.2 Test performance metrics
    // ========================================

    public function test_crew_performance_calculations(): void
    {
        $service = new AnalyticsService();
        
        $crew1 = User::factory()->create(['name' => 'John Doe']);
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create(['name' => 'Jane Smith']);
        $crew2->assignRole('collection_crew');
        
        $truck1 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $truck2 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();

        $startDate = Carbon::now()->subDays(8)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create logs for crew1: 4 completed, 1 incomplete
        for ($i = 0; $i < 5; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck1->id,
                'user_id' => $crew1->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            if ($i < 4) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew1->id,
                    'completion_time' => $assignment->assignment_date->copy()->addHours(2),
                ]);
            } else {
                CollectionLog::factory()->incomplete()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew1->id,
                ]);
            }
        }

        // Create logs for crew2: 2 completed, 1 issue
        for ($i = 0; $i < 3; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck2->id,
                'user_id' => $crew2->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            if ($i < 2) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew2->id,
                    'completion_time' => $assignment->assignment_date->copy()->addHours(3),
                ]);
            } else {
                CollectionLog::factory()->withIssue()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew2->id,
                ]);
            }
        }

        $performance = $service->getCrewPerformance($startDate, $endDate);

        $this->assertCount(2, $performance);
        
        // Find crew1's performance
        $crew1Performance = $performance->firstWhere('user_id', $crew1->id);
        $this->assertNotNull($crew1Performance);
        $this->assertEquals(5, $crew1Performance['total_collections']);
        $this->assertEquals(4, $crew1Performance['completed']);
        $this->assertEquals(1, $crew1Performance['incomplete']);
        $this->assertEquals(0, $crew1Performance['issues_reported']);
        $this->assertEquals(80.0, $crew1Performance['completion_rate']);
        $this->assertEquals(120.0, $crew1Performance['avg_completion_time_minutes']); // 2 hours

        // Find crew2's performance
        $crew2Performance = $performance->firstWhere('user_id', $crew2->id);
        $this->assertNotNull($crew2Performance);
        $this->assertEquals(3, $crew2Performance['total_collections']);
        $this->assertEquals(2, $crew2Performance['completed']);
        $this->assertEquals(0, $crew2Performance['incomplete']);
        $this->assertEquals(1, $crew2Performance['issues_reported']);
        $this->assertEquals(66.67, $crew2Performance['completion_rate']);
        $this->assertEquals(180.0, $crew2Performance['avg_completion_time_minutes']); // 3 hours
    }

    public function test_route_performance_calculations(): void
    {
        $service = new AnalyticsService();
        
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route1 = Route::factory()->create(['name' => 'Downtown Route']);
        $route2 = Route::factory()->create(['name' => 'Suburban Route']);

        $startDate = Carbon::now()->subDays(9)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create logs for route1: 3 completed, 1 issue
        for ($i = 0; $i < 4; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route1->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            if ($i < 3) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew->id,
                    'completion_time' => $assignment->assignment_date->copy()->addHours(2),
                ]);
            } else {
                CollectionLog::factory()->withIssue()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crew->id,
                ]);
            }
        }

        // Create logs for route2: 5 completed, 0 issues
        for ($i = 0; $i < 5; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route2->id,
                'assignment_date' => $startDate->copy()->addDays($i + 4),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->completed()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
                'completion_time' => $assignment->assignment_date->copy()->addHours(1),
            ]);
        }

        $performance = $service->getRoutePerformance($startDate, $endDate);

        $this->assertCount(2, $performance);
        
        // Routes should be sorted by issues_reported descending
        $firstRoute = $performance->first();
        $this->assertEquals($route1->id, $firstRoute['route_id']);
        $this->assertEquals(4, $firstRoute['total_collections']);
        $this->assertEquals(3, $firstRoute['completed']);
        $this->assertEquals(1, $firstRoute['issues_reported']);
        $this->assertEquals(75.0, $firstRoute['completion_rate']);

        $secondRoute = $performance->last();
        $this->assertEquals($route2->id, $secondRoute['route_id']);
        $this->assertEquals(5, $secondRoute['total_collections']);
        $this->assertEquals(5, $secondRoute['completed']);
        $this->assertEquals(0, $secondRoute['issues_reported']);
        $this->assertEquals(100.0, $secondRoute['completion_rate']);
    }

    public function test_average_completion_time_calculation(): void
    {
        $service = new AnalyticsService();
        
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();

        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        // Create 3 completed logs with different completion times
        $assignment1 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $startDate->copy()->addDays(1),
            'status' => Assignment::STATUS_ACTIVE,
        ]);
        CollectionLog::factory()->completed()->create([
            'assignment_id' => $assignment1->id,
            'created_by' => $crew->id,
            'completion_time' => $assignment1->assignment_date->copy()->addHours(2), // 120 minutes
        ]);

        $assignment2 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $startDate->copy()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);
        CollectionLog::factory()->completed()->create([
            'assignment_id' => $assignment2->id,
            'created_by' => $crew->id,
            'completion_time' => $assignment2->assignment_date->copy()->addHours(3), // 180 minutes
        ]);

        $assignment3 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $startDate->copy()->addDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);
        CollectionLog::factory()->completed()->create([
            'assignment_id' => $assignment3->id,
            'created_by' => $crew->id,
            'completion_time' => $assignment3->assignment_date->copy()->addHours(1), // 60 minutes
        ]);

        $avgTime = $service->getAverageCompletionTime($startDate, $endDate);

        // Average: (120 + 180 + 60) / 3 = 120 minutes
        $this->assertEquals(120.0, $avgTime);
    }

    public function test_average_completion_time_returns_zero_for_no_completed_logs(): void
    {
        $service = new AnalyticsService();
        
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        $avgTime = $service->getAverageCompletionTime($startDate, $endDate);

        $this->assertEquals(0.0, $avgTime);
    }


    // ========================================
    // 23.3 Test issue analysis
    // ========================================

    public function test_routes_with_recurring_issues_identification(): void
    {
        $service = new CollectionLogService();
        
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route1 = Route::factory()->create(['name' => 'Problem Route']);
        $route2 = Route::factory()->create(['name' => 'Good Route']);
        $route3 = Route::factory()->create(['name' => 'Another Problem Route']);

        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create 3 issue logs for route1
        for ($i = 0; $i < 3; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route1->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->withIssue()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
            ]);
        }

        // Create 1 issue log for route2 (below threshold)
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => $startDate->copy()->addDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);
        CollectionLog::factory()->withIssue()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
        ]);

        // Create 2 issue logs for route3
        for ($i = 0; $i < 2; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route3->id,
                'assignment_date' => $startDate->copy()->addDays($i + 4),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->withIssue()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
            ]);
        }

        // Get routes with recurring issues (threshold = 2)
        $recurringIssues = $service->getRoutesWithRecurringIssues($startDate, $endDate, 2);

        // Should return route1 (3 issues) and route3 (2 issues), but not route2 (1 issue)
        $this->assertCount(2, $recurringIssues);
        
        // Should be sorted by issue count descending
        $firstRoute = $recurringIssues->first();
        $this->assertEquals($route1->id, $firstRoute['route']->id);
        $this->assertEquals(3, $firstRoute['issue_count']);

        $secondRoute = $recurringIssues->last();
        $this->assertEquals($route3->id, $secondRoute['route']->id);
        $this->assertEquals(2, $secondRoute['issue_count']);
    }

    public function test_issue_type_grouping(): void
    {
        $service = new CollectionLogService();
        
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();

        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create 3 blocked_road issues
        for ($i = 0; $i < 3; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
                'status' => CollectionLog::STATUS_ISSUE_REPORTED,
                'issue_type' => 'blocked_road',
                'issue_description' => 'Road blocked',
            ]);
        }

        // Create 2 truck_problem issues
        for ($i = 3; $i < 5; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
                'status' => CollectionLog::STATUS_ISSUE_REPORTED,
                'issue_type' => 'truck_problem',
                'issue_description' => 'Truck issue',
            ]);
        }

        // Create 1 weather issue
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $startDate->copy()->addDays(5),
            'status' => Assignment::STATUS_ACTIVE,
        ]);
        CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_type' => 'weather',
            'issue_description' => 'Bad weather',
        ]);

        $issuesByType = $service->getIssuesByType($startDate, $endDate);

        // Check that all issue types are present
        $this->assertArrayHasKey('blocked_road', $issuesByType);
        $this->assertArrayHasKey('truck_problem', $issuesByType);
        $this->assertArrayHasKey('weather', $issuesByType);
        $this->assertArrayHasKey('no_access', $issuesByType);
        $this->assertArrayHasKey('safety_concern', $issuesByType);
        $this->assertArrayHasKey('other', $issuesByType);

        // Check counts
        $this->assertEquals(3, $issuesByType['blocked_road']['count']);
        $this->assertEquals(2, $issuesByType['truck_problem']['count']);
        $this->assertEquals(1, $issuesByType['weather']['count']);
        $this->assertEquals(0, $issuesByType['no_access']['count']);
        $this->assertEquals(0, $issuesByType['safety_concern']['count']);
        $this->assertEquals(0, $issuesByType['other']['count']);

        // Check labels
        $this->assertEquals('Blocked Road', $issuesByType['blocked_road']['label']);
        $this->assertEquals('Truck Problem', $issuesByType['truck_problem']['label']);
    }

    public function test_issue_hotspot_identification(): void
    {
        $service = new AnalyticsService();
        
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route1 = Route::factory()->create(['name' => 'Hotspot Route']);
        $route2 = Route::factory()->create(['name' => 'Normal Route']);

        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Create 4 issues for route1 with different types
        $issueTypes = ['blocked_road', 'blocked_road', 'truck_problem', 'weather'];
        $dayOffset = 0;
        foreach ($issueTypes as $issueType) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route1->id,
                'assignment_date' => $startDate->copy()->addDays($dayOffset++),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
                'status' => CollectionLog::STATUS_ISSUE_REPORTED,
                'issue_type' => $issueType,
                'issue_description' => 'Issue description',
            ]);
        }

        // Create 1 issue for route2
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => $startDate->copy()->addDays(4),
            'status' => Assignment::STATUS_ACTIVE,
        ]);
        CollectionLog::factory()->withIssue()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
        ]);

        $hotspots = $service->getIssueHotspots($startDate, $endDate);

        $this->assertCount(2, $hotspots);
        
        // Should be sorted by total_issues descending
        $firstHotspot = $hotspots[0];
        $this->assertEquals($route1->id, $firstHotspot['route_id']);
        $this->assertEquals('Hotspot Route', $firstHotspot['route_name']);
        $this->assertEquals(4, $firstHotspot['total_issues']);
        
        // Check issues_by_type structure
        $this->assertIsArray($firstHotspot['issues_by_type']);
        $this->assertGreaterThan(0, count($firstHotspot['issues_by_type']));
        
        // Most common issue should be blocked_road (2 occurrences)
        $this->assertNotNull($firstHotspot['most_common_issue']);
        $this->assertEquals('blocked_road', $firstHotspot['most_common_issue']['type']);
        $this->assertEquals(2, $firstHotspot['most_common_issue']['count']);

        $secondHotspot = $hotspots[1];
        $this->assertEquals($route2->id, $secondHotspot['route_id']);
        $this->assertEquals(1, $secondHotspot['total_issues']);
    }

    public function test_completion_trend_calculation(): void
    {
        $service = new AnalyticsService();
        
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');
        $crew3 = User::factory()->create();
        $crew3->assignRole('collection_crew');
        
        $truck1 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $truck2 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $truck3 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();

        $startDate = Carbon::now()->subDays(3)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // Day 1: 2 completed, 1 incomplete (66.67% completion rate) - use different crews and trucks
        $crews = [$crew1, $crew2, $crew3];
        $trucks = [$truck1, $truck2, $truck3];
        for ($i = 0; $i < 3; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $trucks[$i]->id,
                'user_id' => $crews[$i]->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy(),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            if ($i < 2) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crews[$i]->id,
                ]);
            } else {
                CollectionLog::factory()->incomplete()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crews[$i]->id,
                ]);
            }
        }

        // Day 2: 3 completed, 0 incomplete (100% completion rate)
        for ($i = 0; $i < 3; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $trucks[$i]->id,
                'user_id' => $crews[$i]->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDay(),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->completed()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crews[$i]->id,
            ]);
        }

        // Day 3: 1 completed, 1 incomplete (50% completion rate)
        for ($i = 0; $i < 2; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $trucks[$i]->id,
                'user_id' => $crews[$i]->id,
                'route_id' => $route->id,
                'assignment_date' => $startDate->copy()->addDays(2),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            if ($i < 1) {
                CollectionLog::factory()->completed()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crews[$i]->id,
                ]);
            } else {
                CollectionLog::factory()->incomplete()->create([
                    'assignment_id' => $assignment->id,
                    'created_by' => $crews[$i]->id,
                ]);
            }
        }

        // Day 4 (today): No logs (0% completion rate)

        $trend = $service->getCompletionTrend($startDate, $endDate);

        $this->assertCount(4, $trend); // 4 days in range
        
        // Check day 1
        $this->assertEquals($startDate->format('Y-m-d'), $trend[0]['date']);
        $this->assertEquals(3, $trend[0]['total']);
        $this->assertEquals(2, $trend[0]['completed']);
        $this->assertEquals(66.67, $trend[0]['completion_rate']);

        // Check day 2
        $this->assertEquals($startDate->copy()->addDay()->format('Y-m-d'), $trend[1]['date']);
        $this->assertEquals(3, $trend[1]['total']);
        $this->assertEquals(3, $trend[1]['completed']);
        $this->assertEquals(100.0, $trend[1]['completion_rate']);

        // Check day 3
        $this->assertEquals($startDate->copy()->addDays(2)->format('Y-m-d'), $trend[2]['date']);
        $this->assertEquals(2, $trend[2]['total']);
        $this->assertEquals(1, $trend[2]['completed']);
        $this->assertEquals(50.0, $trend[2]['completion_rate']);

        // Check day 4 (no logs)
        $this->assertEquals($endDate->format('Y-m-d'), $trend[3]['date']);
        $this->assertEquals(0, $trend[3]['total']);
        $this->assertEquals(0, $trend[3]['completed']);
        $this->assertEquals(0.0, $trend[3]['completion_rate']);
    }

    public function test_analytics_with_empty_date_range(): void
    {
        $service = new AnalyticsService();
        
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        // No data created
        $crewPerformance = $service->getCrewPerformance($startDate, $endDate);
        $routePerformance = $service->getRoutePerformance($startDate, $endDate);
        $hotspots = $service->getIssueHotspots($startDate, $endDate);
        $trend = $service->getCompletionTrend($startDate, $endDate);

        $this->assertCount(0, $crewPerformance);
        $this->assertCount(0, $routePerformance);
        $this->assertCount(0, $hotspots);
        $this->assertGreaterThan(0, count($trend)); // Should still have dates
    }
}
