<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrewDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /**
     * Test that crew member can view their dashboard.
     */
    public function test_crew_member_can_view_dashboard(): void
    {
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');

        $response = $this->actingAs($crewMember)->get(route('crew.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        $response->assertViewHas('user');
    }

    /**
     * Test that crew dashboard displays today's assignment prominently.
     */
    public function test_crew_dashboard_displays_todays_assignment(): void
    {
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');

        $route = Route::factory()->create(['name' => 'Test Route', 'zone' => 'Zone A']);
        $truck = Truck::factory()->create(['truck_number' => 'T-001']);
        
        $assignment = Assignment::factory()->create([
            'user_id' => $crewMember->id,
            'route_id' => $route->id,
            'truck_id' => $truck->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crewMember)->get(route('crew.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertNotNull($metrics['today_assignment']);
        $this->assertEquals('Test Route', $metrics['today_assignment']['route_name']);
        $this->assertEquals('Zone A', $metrics['today_assignment']['route_zone']);
        $this->assertEquals('T-001', $metrics['today_assignment']['truck_number']);
    }

    /**
     * Test that crew dashboard displays performance metrics.
     */
    public function test_crew_dashboard_displays_performance_metrics(): void
    {
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');

        $route = Route::factory()->create();
        $truck = Truck::factory()->create();
        
        // Create assignments for the last 30 days
        $assignments = Assignment::factory()->count(5)->create([
            'user_id' => $crewMember->id,
            'route_id' => $route->id,
            'truck_id' => $truck->id,
            'assignment_date' => now()->subDays(rand(1, 30)),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crewMember)->get(route('crew.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertArrayHasKey('performance', $metrics);
        $this->assertArrayHasKey('total_collections', $metrics['performance']);
        $this->assertArrayHasKey('completed', $metrics['performance']);
        $this->assertArrayHasKey('completion_rate', $metrics['performance']);
    }

    /**
     * Test that crew dashboard displays recent collection logs.
     */
    public function test_crew_dashboard_displays_recent_collection_logs(): void
    {
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');

        $route = Route::factory()->create(['name' => 'Test Route']);
        $truck = Truck::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'user_id' => $crewMember->id,
            'route_id' => $route->id,
            'truck_id' => $truck->id,
            'assignment_date' => now()->subDays(1),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $collectionLog = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crewMember->id,
            'status' => CollectionLog::STATUS_COMPLETED,
            'crew_notes' => 'Test notes',
        ]);

        $response = $this->actingAs($crewMember)->get(route('crew.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertArrayHasKey('recent_logs', $metrics);
        $this->assertNotEmpty($metrics['recent_logs']);
        $this->assertEquals('Test Route', $metrics['recent_logs'][0]['route_name']);
        $this->assertEquals('completed', $metrics['recent_logs'][0]['status']);
    }

    /**
     * Test that crew dashboard displays upcoming assignments.
     */
    public function test_crew_dashboard_displays_upcoming_assignments(): void
    {
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');

        $route = Route::factory()->create(['name' => 'Future Route']);
        $truck = Truck::factory()->create(['truck_number' => 'T-002']);
        
        $upcomingAssignment = Assignment::factory()->create([
            'user_id' => $crewMember->id,
            'route_id' => $route->id,
            'truck_id' => $truck->id,
            'assignment_date' => now()->addDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crewMember)->get(route('crew.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertArrayHasKey('upcoming_assignments', $metrics);
        $this->assertNotEmpty($metrics['upcoming_assignments']);
        $this->assertEquals('Future Route', $metrics['upcoming_assignments'][0]['route_name']);
        $this->assertEquals('T-002', $metrics['upcoming_assignments'][0]['truck_number']);
    }

    /**
     * Test that crew dashboard shows no assignment message when no assignment exists.
     */
    public function test_crew_dashboard_shows_no_assignment_message(): void
    {
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');

        $response = $this->actingAs($crewMember)->get(route('crew.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertNull($metrics['today_assignment']);
        $response->assertSee('No assignment scheduled for today');
    }

    /**
     * Test that non-crew members cannot access crew dashboard.
     */
    public function test_non_crew_members_cannot_access_crew_dashboard(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('crew.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Test that crew dashboard is mobile-responsive.
     */
    public function test_crew_dashboard_has_mobile_responsive_styles(): void
    {
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');

        $response = $this->actingAs($crewMember)->get(route('crew.dashboard'));

        $response->assertStatus(200);
        // Check for mobile-specific CSS
        $response->assertSee('@media (max-width: 768px)');
        $response->assertSee('min-height: 44px'); // Touch-friendly tap targets
    }
}
