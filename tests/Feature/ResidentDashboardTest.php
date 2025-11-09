<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Report;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /**
     * Test that resident can view their dashboard.
     */
    public function test_resident_can_view_dashboard(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        $response->assertViewHas('user');
    }

    /**
     * Test that resident dashboard displays next scheduled collection date.
     */
    public function test_resident_dashboard_displays_next_collection_date(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::factory()->create(['name' => 'Zone A Route', 'zone' => 'Zone A']);
        $truck = Truck::factory()->create();
        
        // Create a report to establish the resident's zone
        $report = Report::factory()->create([
            'resident_id' => $resident->id,
            'route_id' => $route->id,
            'status' => Report::STATUS_RESOLVED,
        ]);

        // Create next collection assignment
        $nextAssignment = Assignment::factory()->create([
            'route_id' => $route->id,
            'truck_id' => $truck->id,
            'assignment_date' => now()->addDays(2),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertNotNull($metrics['next_collection']);
        $this->assertEquals('Zone A Route', $metrics['next_collection']['route_name']);
        $this->assertEquals(2, $metrics['next_collection']['days_until']);
        $response->assertSee('Next Scheduled Collection');
        $response->assertSee('Zone A Route');
    }

    /**
     * Test that resident dashboard displays recent reports with status.
     */
    public function test_resident_dashboard_displays_recent_reports(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create multiple reports
        $reports = Report::factory()->count(3)->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING,
            'report_type' => Report::TYPE_MISSED_PICKUP,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertArrayHasKey('recent_reports', $metrics);
        $this->assertCount(3, $metrics['recent_reports']);
        $this->assertEquals('pending', $metrics['recent_reports'][0]['status']);
        $response->assertSee('My Recent Reports');
        $response->assertSee($reports[0]->reference_number);
    }

    /**
     * Test that resident dashboard has quick access button to submit new report.
     */
    public function test_resident_dashboard_has_submit_report_button(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Submit Report');
        $response->assertSee(route('resident.reports.create'));
    }

    /**
     * Test that resident dashboard displays collection schedule information prominently.
     */
    public function test_resident_dashboard_displays_collection_schedule(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::factory()->create(['name' => 'Zone B Route', 'zone' => 'Zone B']);
        $truck = Truck::factory()->create();
        
        // Create a report to establish the resident's zone
        $report = Report::factory()->create([
            'resident_id' => $resident->id,
            'route_id' => $route->id,
            'status' => Report::STATUS_RESOLVED,
        ]);

        // Create multiple upcoming collections
        for ($i = 1; $i <= 5; $i++) {
            Assignment::factory()->create([
                'route_id' => $route->id,
                'truck_id' => $truck->id,
                'assignment_date' => now()->addDays($i * 3),
                'status' => Assignment::STATUS_ACTIVE,
            ]);
        }

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertArrayHasKey('collection_schedule', $metrics);
        $this->assertNotEmpty($metrics['collection_schedule']);
        $response->assertSee('Upcoming Collections');
        $response->assertSee('Zone B Route');
    }

    /**
     * Test that resident dashboard displays recycling tips.
     */
    public function test_resident_dashboard_displays_recycling_tips(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recycling Tips');
        $response->assertSee('Rinse containers');
        $response->assertSee('Remove caps');
        $response->assertSee('Flatten cardboard boxes');
        $response->assertSee('Keep recyclables dry');
    }

    /**
     * Test that resident dashboard displays important information cards.
     */
    public function test_resident_dashboard_displays_important_information(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Important Information');
        $response->assertSee('Bin Placement');
        $response->assertSee('Holiday Schedule');
        $response->assertSee('Need Help?');
    }

    /**
     * Test that resident dashboard displays report statistics.
     */
    public function test_resident_dashboard_displays_report_statistics(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create reports with different statuses
        Report::factory()->count(3)->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING,
        ]);

        Report::factory()->count(2)->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_RESOLVED,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertArrayHasKey('report_statistics', $metrics);
        $this->assertEquals(5, $metrics['report_statistics']['total_reports']);
        $this->assertEquals(3, $metrics['report_statistics']['pending_reports']);
        $this->assertEquals(2, $metrics['report_statistics']['resolved_reports']);
    }

    /**
     * Test that resident dashboard shows zone information.
     */
    public function test_resident_dashboard_shows_zone_information(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::factory()->create(['zone' => 'Zone C']);
        
        // Create a report to establish the resident's zone
        Report::factory()->create([
            'resident_id' => $resident->id,
            'route_id' => $route->id,
            'status' => Report::STATUS_RESOLVED,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertEquals('Zone C', $metrics['zone']);
        $response->assertSee('Your Zone:', false);
        $response->assertSee('Zone C', false);
    }

    /**
     * Test that resident dashboard shows message when no zone is identified.
     */
    public function test_resident_dashboard_shows_no_zone_message(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertNull($metrics['zone']);
        $this->assertNull($metrics['next_collection']);
        $response->assertSee('Search My Zone');
    }

    /**
     * Test that non-residents cannot access resident dashboard.
     */
    public function test_non_residents_cannot_access_resident_dashboard(): void
    {
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');

        $response = $this->actingAs($crewMember)->get(route('resident.dashboard'));

        // Middleware redirects unauthorized users instead of returning 403
        $response->assertStatus(302);
    }

    /**
     * Test that resident dashboard displays today's collection prominently.
     */
    public function test_resident_dashboard_highlights_todays_collection(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::factory()->create(['zone' => 'Zone D']);
        $truck = Truck::factory()->create();
        
        // Create a report to establish the resident's zone
        Report::factory()->create([
            'resident_id' => $resident->id,
            'route_id' => $route->id,
            'status' => Report::STATUS_RESOLVED,
        ]);

        // Create today's collection
        Assignment::factory()->create([
            'route_id' => $route->id,
            'truck_id' => $truck->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertEquals(0, $metrics['next_collection']['days_until']);
        $response->assertSee('Collection is today!');
    }

    /**
     * Test that resident dashboard displays tomorrow's collection.
     */
    public function test_resident_dashboard_highlights_tomorrows_collection(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::factory()->create(['zone' => 'Zone E']);
        $truck = Truck::factory()->create();
        
        // Create a report to establish the resident's zone
        Report::factory()->create([
            'resident_id' => $resident->id,
            'route_id' => $route->id,
            'status' => Report::STATUS_RESOLVED,
        ]);

        // Create tomorrow's collection
        Assignment::factory()->create([
            'route_id' => $route->id,
            'truck_id' => $truck->id,
            'assignment_date' => now()->addDay(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $metrics = $response->viewData('metrics');
        
        $this->assertEquals(1, $metrics['next_collection']['days_until']);
        $response->assertSee('Collection is tomorrow');
    }
}
