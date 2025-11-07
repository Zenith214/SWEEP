<?php

namespace Tests\Feature;

use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // Schedule CRUD Operations Tests (20.1)
    // ========================================

    public function test_administrator_can_create_schedule_with_multiple_days(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.schedules.store'), [
            'route_id' => $route->id,
            'collection_time' => '08:00',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'days_of_week' => [1, 3, 5], // Monday, Wednesday, Friday
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        // Verify schedule was created
        $this->assertDatabaseHas('schedules', [
            'route_id' => $route->id,
            'collection_time' => '08:00',
            'is_active' => true,
        ]);

        // Verify schedule days were created
        $schedule = Schedule::where('route_id', $route->id)->first();
        $this->assertDatabaseHas('schedule_days', [
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);
        $this->assertDatabaseHas('schedule_days', [
            'schedule_id' => $schedule->id,
            'day_of_week' => 3,
        ]);
        $this->assertDatabaseHas('schedule_days', [
            'schedule_id' => $schedule->id,
            'day_of_week' => 5,
        ]);
    }

    public function test_schedule_conflict_detection_prevents_duplicate_schedules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        // Create first schedule
        $schedule1 = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule1->id,
            'day_of_week' => 1, // Monday
        ]);

        // Try to create conflicting schedule
        $response = $this->actingAs($admin)->post(route('admin.schedules.store'), [
            'route_id' => $route->id,
            'collection_time' => '09:00',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'days_of_week' => [1, 2], // Monday (conflicts), Tuesday
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('conflict', session('error'));
    }


    public function test_schedule_conflict_detection_allows_non_overlapping_schedules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        // Create first schedule for Monday
        $schedule1 = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule1->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create non-conflicting schedule for Tuesday
        $response = $this->actingAs($admin)->post(route('admin.schedules.store'), [
            'route_id' => $route->id,
            'collection_time' => '09:00',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->format('Y-m-d'),
            'days_of_week' => [2], // Tuesday (no conflict)
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');
    }

    public function test_administrator_can_view_schedule_list(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.schedules.index'));

        $response->assertOk();
        $response->assertSee('Test Route');
    }

    public function test_administrator_can_edit_schedule(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.schedules.update', $schedule), [
            'route_id' => $route->id,
            'collection_time' => '10:00',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
            'days_of_week' => [1, 3], // Monday, Wednesday
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        $schedule->refresh();
        $this->assertEquals('10:00:00', $schedule->collection_time->format('H:i:s'));

        // Verify days were updated
        $this->assertDatabaseHas('schedule_days', [
            'schedule_id' => $schedule->id,
            'day_of_week' => 3,
        ]);
    }

    public function test_schedule_editing_with_conflict_checking(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        // Create first schedule for Monday
        $schedule1 = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule1->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create second schedule for Tuesday
        $schedule2 = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '09:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule2->id,
            'day_of_week' => 2, // Tuesday
        ]);

        // Try to update schedule2 to conflict with schedule1
        $response = $this->actingAs($admin)->patch(route('admin.schedules.update', $schedule2), [
            'route_id' => $route->id,
            'collection_time' => '10:00',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'days_of_week' => [1], // Monday (conflicts with schedule1)
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('conflict', session('error'));
    }

    public function test_administrator_can_activate_schedule(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => false,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.schedules.toggle', $schedule));

        $response->assertOk();
        $response->assertJson(['success' => true, 'is_active' => true]);

        $schedule->refresh();
        $this->assertTrue($schedule->is_active);
    }

    public function test_administrator_can_deactivate_schedule(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.schedules.toggle', $schedule));

        $response->assertOk();
        $response->assertJson(['success' => true, 'is_active' => false]);

        $schedule->refresh();
        $this->assertFalse($schedule->is_active);
    }

    public function test_administrator_can_delete_schedule(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.schedules.destroy', $schedule));

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        // Schedule should be soft deleted
        $this->assertSoftDeleted('schedules', ['id' => $schedule->id]);
    }


    // ========================================
    // Schedule Duplication Tests (20.2)
    // ========================================

    public function test_administrator_can_duplicate_schedule_to_different_route(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route1 = Route::create([
            'name' => 'Route 1',
            'zone' => 'R1-001',
            'is_active' => true,
        ]);

        $route2 = Route::create([
            'name' => 'Route 2',
            'zone' => 'R2-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route1->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 3, // Wednesday
        ]);

        $response = $this->actingAs($admin)->post(route('admin.schedules.store-duplicate', $schedule), [
            'target_route_id' => $route2->id,
        ]);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        // Verify new schedule was created for route2
        $newSchedule = Schedule::where('route_id', $route2->id)->first();
        $this->assertNotNull($newSchedule);
        $this->assertEquals('08:00:00', $newSchedule->collection_time->format('H:i:s'));
        $this->assertEquals($schedule->start_date->format('Y-m-d'), $newSchedule->start_date->format('Y-m-d'));
        $this->assertEquals($schedule->end_date->format('Y-m-d'), $newSchedule->end_date->format('Y-m-d'));

        // Verify schedule days were copied
        $this->assertDatabaseHas('schedule_days', [
            'schedule_id' => $newSchedule->id,
            'day_of_week' => 1,
        ]);

        $this->assertDatabaseHas('schedule_days', [
            'schedule_id' => $newSchedule->id,
            'day_of_week' => 3,
        ]);
    }

    public function test_schedule_duplication_prevents_conflicts_on_target_route(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route1 = Route::create([
            'name' => 'Route 1',
            'zone' => 'R1-001',
            'is_active' => true,
        ]);

        $route2 = Route::create([
            'name' => 'Route 2',
            'zone' => 'R2-001',
            'is_active' => true,
        ]);

        // Create schedule on route1
        $schedule1 = Schedule::create([
            'route_id' => $route1->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule1->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create existing schedule on route2 with same day
        $schedule2 = Schedule::create([
            'route_id' => $route2->id,
            'collection_time' => '09:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule2->id,
            'day_of_week' => 1, // Monday (will conflict)
        ]);

        // Try to duplicate schedule1 to route2 (should fail due to conflict)
        $response = $this->actingAs($admin)->post(route('admin.schedules.store-duplicate', $schedule1), [
            'target_route_id' => $route2->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('conflict', session('error'));

        // Verify only one schedule exists for route2
        $this->assertEquals(1, Schedule::where('route_id', $route2->id)->count());
    }

    public function test_schedule_duplication_allows_non_conflicting_days(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route1 = Route::create([
            'name' => 'Route 1',
            'zone' => 'R1-001',
            'is_active' => true,
        ]);

        $route2 = Route::create([
            'name' => 'Route 2',
            'zone' => 'R2-001',
            'is_active' => true,
        ]);

        // Create schedule on route1 for Monday
        $schedule1 = Schedule::create([
            'route_id' => $route1->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule1->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create existing schedule on route2 for Tuesday (no conflict)
        $schedule2 = Schedule::create([
            'route_id' => $route2->id,
            'collection_time' => '09:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule2->id,
            'day_of_week' => 2, // Tuesday
        ]);

        // Duplicate schedule1 to route2 (should succeed)
        $response = $this->actingAs($admin)->post(route('admin.schedules.store-duplicate', $schedule1), [
            'target_route_id' => $route2->id,
        ]);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        // Verify two schedules exist for route2
        $this->assertEquals(2, Schedule::where('route_id', $route2->id)->count());
    }

    public function test_schedule_duplication_validates_target_route_exists(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Route 1',
            'zone' => 'R1-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        // Try to duplicate to non-existent route
        $response = $this->actingAs($admin)->post(route('admin.schedules.store-duplicate', $schedule), [
            'target_route_id' => 99999,
        ]);

        $response->assertSessionHasErrors('target_route_id');
    }

    public function test_schedule_duplication_validates_target_route_is_different(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $route = Route::create([
            'name' => 'Route 1',
            'zone' => 'R1-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        // Try to duplicate to same route
        $response = $this->actingAs($admin)->post(route('admin.schedules.store-duplicate', $schedule), [
            'target_route_id' => $route->id,
        ]);

        $response->assertSessionHasErrors('target_route_id');
    }


    // ========================================
    // Schedule Authorization Tests (20.3)
    // ========================================

    public function test_only_administrators_can_access_schedule_index(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('admin.schedules.index'));
        $response->assertRedirect();

        // Crew cannot access
        $response = $this->actingAs($crew)->get(route('admin.schedules.index'));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_create_schedules(): void
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

        $scheduleData = [
            'route_id' => $route->id,
            'collection_time' => '08:00',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'days_of_week' => [1, 3],
            'is_active' => true,
        ];

        // Resident cannot create
        $response = $this->actingAs($resident)->post(route('admin.schedules.store'), $scheduleData);
        $response->assertRedirect();

        // Crew cannot create
        $response = $this->actingAs($crew)->post(route('admin.schedules.store'), $scheduleData);
        $response->assertRedirect();

        // Verify schedule was not created
        $this->assertEquals(0, Schedule::count());
    }

    public function test_only_administrators_can_view_schedule_details(): void
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

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        // Resident cannot view
        $response = $this->actingAs($resident)->get(route('admin.schedules.show', $schedule));
        $response->assertRedirect();

        // Crew cannot view
        $response = $this->actingAs($crew)->get(route('admin.schedules.show', $schedule));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_edit_schedules(): void
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

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1,
        ]);

        $updateData = [
            'route_id' => $route->id,
            'collection_time' => '10:00',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
            'days_of_week' => [1, 3],
            'is_active' => true,
        ];

        // Resident cannot edit
        $response = $this->actingAs($resident)->patch(route('admin.schedules.update', $schedule), $updateData);
        $response->assertRedirect();

        // Crew cannot edit
        $response = $this->actingAs($crew)->patch(route('admin.schedules.update', $schedule), $updateData);
        $response->assertRedirect();

        // Verify schedule was not updated
        $schedule->refresh();
        $this->assertEquals('08:00:00', $schedule->collection_time->format('H:i:s'));
    }

    public function test_only_administrators_can_toggle_schedule_status(): void
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

        $schedule1 = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        $schedule2 = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '09:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create(['schedule_id' => $schedule1->id, 'day_of_week' => 1]);
        ScheduleDay::create(['schedule_id' => $schedule2->id, 'day_of_week' => 2]);

        // Resident cannot toggle
        $response = $this->actingAs($resident)->patch(route('admin.schedules.toggle', $schedule1));
        $response->assertRedirect();

        // Crew cannot toggle
        $response = $this->actingAs($crew)->patch(route('admin.schedules.toggle', $schedule2));
        $response->assertRedirect();

        // Verify schedules were not toggled
        $schedule1->refresh();
        $schedule2->refresh();
        $this->assertTrue($schedule1->is_active);
        $this->assertTrue($schedule2->is_active);
    }

    public function test_only_administrators_can_delete_schedules(): void
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

        $schedule1 = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        $schedule2 = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '09:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create(['schedule_id' => $schedule1->id, 'day_of_week' => 1]);
        ScheduleDay::create(['schedule_id' => $schedule2->id, 'day_of_week' => 2]);

        // Resident cannot delete
        $response = $this->actingAs($resident)->delete(route('admin.schedules.destroy', $schedule1));
        $response->assertRedirect();

        // Crew cannot delete
        $response = $this->actingAs($crew)->delete(route('admin.schedules.destroy', $schedule2));
        $response->assertRedirect();

        // Verify schedules were not deleted
        $this->assertDatabaseHas('schedules', ['id' => $schedule1->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('schedules', ['id' => $schedule2->id, 'deleted_at' => null]);
    }

    public function test_only_administrators_can_duplicate_schedules(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $route1 = Route::create([
            'name' => 'Route 1',
            'zone' => 'R1-001',
            'is_active' => true,
        ]);

        $route2 = Route::create([
            'name' => 'Route 2',
            'zone' => 'R2-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route1->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create(['schedule_id' => $schedule->id, 'day_of_week' => 1]);

        $duplicateData = ['target_route_id' => $route2->id];

        // Resident cannot duplicate
        $response = $this->actingAs($resident)->post(route('admin.schedules.store-duplicate', $schedule), $duplicateData);
        $response->assertRedirect();

        // Crew cannot duplicate
        $response = $this->actingAs($crew)->post(route('admin.schedules.store-duplicate', $schedule), $duplicateData);
        $response->assertRedirect();

        // Verify schedule was not duplicated
        $this->assertEquals(0, Schedule::where('route_id', $route2->id)->count());
    }

    public function test_unauthenticated_users_cannot_access_schedule_management(): void
    {
        $route = Route::create([
            'name' => 'Test Route',
            'zone' => 'T-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        ScheduleDay::create(['schedule_id' => $schedule->id, 'day_of_week' => 1]);

        // Test all schedule management endpoints
        $this->get(route('admin.schedules.index'))->assertRedirect(route('login'));
        $this->get(route('admin.schedules.create'))->assertRedirect(route('login'));
        $this->post(route('admin.schedules.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.schedules.show', $schedule))->assertRedirect(route('login'));
        $this->get(route('admin.schedules.edit', $schedule))->assertRedirect(route('login'));
        $this->patch(route('admin.schedules.update', $schedule), [])->assertRedirect(route('login'));
        $this->patch(route('admin.schedules.toggle', $schedule))->assertRedirect(route('login'));
        $this->delete(route('admin.schedules.destroy', $schedule))->assertRedirect(route('login'));
        $this->get(route('admin.schedules.duplicate', $schedule))->assertRedirect(route('login'));
        $this->post(route('admin.schedules.store-duplicate', $schedule), [])->assertRedirect(route('login'));
    }
}

