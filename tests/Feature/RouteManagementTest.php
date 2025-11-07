<?php

namespace Tests\Feature;

use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_administrator_can_create_route(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.routes.store'), [
            'name' => 'North District Route',
            'zone' => 'ND-001',
            'description' => 'Covers northern residential area',
            'notes' => 'Watch for narrow streets',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('routes', [
            'name' => 'North District Route',
            'zone' => 'ND-001',
            'description' => 'Covers northern residential area',
            'notes' => 'Watch for narrow streets',
            'is_active' => true,
        ]);
    }

    public function test_duplicate_route_name_validation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create existing route
        Route::create([
            'name' => 'Existing Route',
            'zone' => 'EX-001',
            'is_active' => true,
        ]);

        // Try to create route with duplicate name
        $response = $this->actingAs($admin)->post(route('admin.routes.store'), [
            'name' => 'Existing Route',
            'zone' => 'EX-002',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_route_listing_displays_all_routes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Route::create(['name' => 'Route A', 'zone' => 'ZA-001', 'is_active' => true]);
        Route::create(['name' => 'Route B', 'zone' => 'ZB-001', 'is_active' => true]);
        Route::create(['name' => 'Route C', 'zone' => 'ZC-001', 'is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.routes.index'));

        $response->assertOk();
        $response->assertSee('Route A');
        $response->assertSee('Route B');
        $response->assertSee('Route C');
    }

    public function test_route_search_by_name(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Route::create(['name' => 'North Route', 'zone' => 'N-001', 'is_active' => true]);
        Route::create(['name' => 'South Route', 'zone' => 'S-001', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.routes.index', ['search' => 'North']));

        $response->assertOk();
        $response->assertSee('North Route');
        $response->assertDontSee('South Route');
    }

    public function test_route_search_by_zone(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Route::create(['name' => 'Route A', 'zone' => 'ALPHA-001', 'is_active' => true]);
        Route::create(['name' => 'Route B', 'zone' => 'BETA-001', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.routes.index', ['search' => 'ALPHA']));

        $response->assertOk();
        $response->assertSee('Route A');
        $response->assertDontSee('Route B');
    }

    public function test_route_filter_by_active_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Route::create(['name' => 'Active Route', 'zone' => 'A-001', 'is_active' => true]);
        Route::create(['name' => 'Inactive Route', 'zone' => 'I-001', 'is_active' => false]);

        // Filter for active routes
        $response = $this->actingAs($admin)->get(route('admin.routes.index', ['status' => 'active']));
        $response->assertOk();
        $response->assertSee('Active Route');
        $response->assertDontSee('Inactive Route');

        // Filter for inactive routes
        $response = $this->actingAs($admin)->get(route('admin.routes.index', ['status' => 'inactive']));
        $response->assertOk();
        $response->assertSee('Inactive Route');
        $response->assertDontSee('Active Route');
    }

    public function test_administrator_can_view_route_details(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'description' => 'Test description',
            'notes' => 'Test notes',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.routes.show', $route));

        $response->assertOk();
        $response->assertSee('Test Route');
        $response->assertSee('T-001');
        $response->assertSee('Test description');
        $response->assertSee('Test notes');
    }

    public function test_administrator_can_edit_route(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Old Name',
            'zone' => 'OLD-001',
            'description' => 'Old description',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.routes.update', $route), [
            'name' => 'New Name',
            'zone' => 'NEW-001',
            'description' => 'New description',
            'notes' => 'New notes',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success');

        $route->refresh();
        $this->assertEquals('New Name', $route->name);
        $this->assertEquals('NEW-001', $route->zone);
        $this->assertEquals('New description', $route->description);
        $this->assertEquals('New notes', $route->notes);
        $this->assertFalse($route->is_active);
    }

    public function test_route_name_uniqueness_validation_on_update(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route1 = Route::create(['name' => 'Route One', 'zone' => 'R1-001', 'is_active' => true]);
        $route2 = Route::create(['name' => 'Route Two', 'zone' => 'R2-001', 'is_active' => true]);

        // Try to update route2 with route1's name
        $response = $this->actingAs($admin)->patch(route('admin.routes.update', $route2), [
            'name' => 'Route One',
            'zone' => 'R2-001',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_administrator_can_delete_route_without_active_schedules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Deletable Route',
            'zone' => 'D-001',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.routes.destroy', $route));

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success');

        // Route should be soft deleted
        $this->assertSoftDeleted('routes', ['id' => $route->id]);
    }

    public function test_cannot_delete_route_with_active_schedules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Protected Route',
            'zone' => 'P-001',
            'is_active' => true,
        ]);

        // Create an active schedule for the route
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        // Add schedule days
        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.routes.destroy', $route));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Route should NOT be deleted
        $this->assertDatabaseHas('routes', [
            'id' => $route->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_delete_route_with_inactive_schedules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Route with Inactive Schedule',
            'zone' => 'RIS-001',
            'is_active' => true,
        ]);

        // Create an inactive schedule
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => false, // Inactive
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.routes.destroy', $route));

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success');

        // Route should be soft deleted
        $this->assertSoftDeleted('routes', ['id' => $route->id]);
    }

    public function test_can_delete_route_with_expired_schedules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Route with Expired Schedule',
            'zone' => 'RES-001',
            'is_active' => true,
        ]);

        // Create an expired schedule
        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->subMonths(3),
            'end_date' => now()->subDays(1), // Expired
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.routes.destroy', $route));

        $response->assertRedirect(route('admin.routes.index'));
        $response->assertSessionHas('success');

        // Route should be soft deleted
        $this->assertSoftDeleted('routes', ['id' => $route->id]);
    }

    // Authorization Tests

    public function test_only_administrators_can_access_route_index(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('admin.routes.index'));
        $response->assertRedirect();

        // Crew cannot access
        $response = $this->actingAs($crew)->get(route('admin.routes.index'));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_create_routes(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $routeData = [
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ];

        // Resident cannot create
        $response = $this->actingAs($resident)->post(route('admin.routes.store'), $routeData);
        $response->assertRedirect();

        // Crew cannot create
        $response = $this->actingAs($crew)->post(route('admin.routes.store'), $routeData);
        $response->assertRedirect();

        // Verify route was not created
        $this->assertDatabaseMissing('routes', ['name' => 'Test Route']);
    }

    public function test_only_administrators_can_view_route_details(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        // Resident cannot view
        $response = $this->actingAs($resident)->get(route('admin.routes.show', $route));
        $response->assertRedirect();

        // Crew cannot view
        $response = $this->actingAs($crew)->get(route('admin.routes.show', $route));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_edit_routes(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $route = Route::create([
            'name' => 'Original Name',
            'zone' => 'O-001',
            'is_active' => true,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'zone' => 'U-001',
            'is_active' => true,
        ];

        // Resident cannot edit
        $response = $this->actingAs($resident)->patch(route('admin.routes.update', $route), $updateData);
        $response->assertRedirect();

        // Crew cannot edit
        $response = $this->actingAs($crew)->patch(route('admin.routes.update', $route), $updateData);
        $response->assertRedirect();

        // Verify route was not updated
        $route->refresh();
        $this->assertEquals('Original Name', $route->name);
    }

    public function test_only_administrators_can_delete_routes(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $route1 = Route::create([
            'name' => 'Route for Resident Test',
            'zone' => 'RRT-001',
            'is_active' => true,
        ]);

        $route2 = Route::create([
            'name' => 'Route for Crew Test',
            'zone' => 'RCT-001',
            'is_active' => true,
        ]);

        // Resident cannot delete
        $response = $this->actingAs($resident)->delete(route('admin.routes.destroy', $route1));
        $response->assertRedirect();

        // Crew cannot delete
        $response = $this->actingAs($crew)->delete(route('admin.routes.destroy', $route2));
        $response->assertRedirect();

        // Verify routes were not deleted
        $this->assertDatabaseHas('routes', ['id' => $route1->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('routes', ['id' => $route2->id, 'deleted_at' => null]);
    }

    public function test_unauthenticated_users_cannot_access_route_management(): void
    {
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        // Test all route management endpoints
        $this->get(route('admin.routes.index'))->assertRedirect(route('login'));
        $this->get(route('admin.routes.create'))->assertRedirect(route('login'));
        $this->post(route('admin.routes.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.routes.show', $route))->assertRedirect(route('login'));
        $this->get(route('admin.routes.edit', $route))->assertRedirect(route('login'));
        $this->patch(route('admin.routes.update', $route), [])->assertRedirect(route('login'));
        $this->delete(route('admin.routes.destroy', $route))->assertRedirect(route('login'));
    }
}
