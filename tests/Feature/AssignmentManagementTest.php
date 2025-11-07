<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // 22.1 Test assignment CRUD operations
    // ========================================

    public function test_administrator_can_create_assignment(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3)->format('Y-m-d'),
            'notes' => 'Test assignment',
        ]);

        $response->assertRedirect(route('admin.assignments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assignments', [
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'status' => Assignment::STATUS_ACTIVE,
            'notes' => 'Test assignment',
        ]);
    }

    public function test_truck_conflict_detection_prevents_double_booking(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');
        $route1 = Route::factory()->create();
        $route2 = Route::factory()->create();

        $assignmentDate = now()->addDays(3)->format('Y-m-d');

        // Create first assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew1->id,
            'route_id' => $route1->id,
            'assignment_date' => $assignmentDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Try to create second assignment with same truck on same date
        $response = $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'truck_id' => $truck->id,
            'user_id' => $crew2->id,
            'route_id' => $route2->id,
            'assignment_date' => $assignmentDate,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('assignment_date');
    }

    public function test_crew_conflict_detection_prevents_double_booking(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck1 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $truck2 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route1 = Route::factory()->create();
        $route2 = Route::factory()->create();

        $assignmentDate = now()->addDays(3)->format('Y-m-d');

        // Create first assignment
        Assignment::factory()->create([
            'truck_id' => $truck1->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => $assignmentDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Try to create second assignment with same crew on same date
        $response = $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'truck_id' => $truck2->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => $assignmentDate,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('assignment_date');
    }

    public function test_truck_operational_status_validation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $maintenanceTruck = Truck::factory()->create(['operational_status' => Truck::STATUS_MAINTENANCE]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'truck_id' => $maintenanceTruck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('truck_id');
    }

    public function test_crew_role_validation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $resident = User::factory()->create();
        $resident->assignRole('resident');
        $route = Route::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'truck_id' => $truck->id,
            'user_id' => $resident->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('user_id');
    }

    public function test_administrator_can_edit_assignment(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck1 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $truck2 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        $assignment = Assignment::factory()->create([
            'truck_id' => $truck1->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.assignments.update', $assignment), [
            'truck_id' => $truck2->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3)->format('Y-m-d'),
            'notes' => 'Updated assignment',
        ]);

        $response->assertRedirect(route('admin.assignments.show', $assignment));
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals($truck2->id, $assignment->truck_id);
        $this->assertEquals('Updated assignment', $assignment->notes);
    }

    public function test_assignment_editing_with_conflict_checking(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');
        $route1 = Route::factory()->create();
        $route2 = Route::factory()->create();

        $assignmentDate = now()->addDays(3)->format('Y-m-d');

        // Create first assignment
        $assignment1 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew1->id,
            'route_id' => $route1->id,
            'assignment_date' => $assignmentDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create second assignment with different truck
        $truck2 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $assignment2 = Assignment::factory()->create([
            'truck_id' => $truck2->id,
            'user_id' => $crew2->id,
            'route_id' => $route2->id,
            'assignment_date' => $assignmentDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Try to update assignment2 to use the same truck as assignment1
        $response = $this->actingAs($admin)->patch(route('admin.assignments.update', $assignment2), [
            'truck_id' => $truck->id,
            'user_id' => $crew2->id,
            'route_id' => $route2->id,
            'assignment_date' => $assignmentDate,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('assignment_date');
    }

    public function test_administrator_can_cancel_assignment(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.assignments.cancel', $assignment), [
            'cancellation_reason' => 'Truck needs maintenance',
        ]);

        $response->assertRedirect(route('admin.assignments.index'));
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals(Assignment::STATUS_CANCELLED, $assignment->status);
        $this->assertEquals('Truck needs maintenance', $assignment->cancellation_reason);
    }

    // ========================================
    // 22.2 Test assignment copying
    // ========================================

    public function test_successful_assignment_copying_to_different_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        $sourceDate = now()->addDays(3)->format('Y-m-d');
        $targetDate = now()->addDays(10)->format('Y-m-d');

        // Create source assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => $sourceDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.assignments.copy'), [
            'source_date' => $sourceDate,
            'target_date' => $targetDate,
        ]);

        $response->assertRedirect(route('admin.assignments.index'));
        $response->assertSessionHas('success');

        // Verify the assignment was copied
        $copiedAssignment = Assignment::where('truck_id', $truck->id)
            ->where('user_id', $crew->id)
            ->where('route_id', $route->id)
            ->whereDate('assignment_date', $targetDate)
            ->where('status', Assignment::STATUS_ACTIVE)
            ->first();
        
        $this->assertNotNull($copiedAssignment);
    }

    public function test_conflict_detection_during_copying(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route1 = Route::factory()->create();
        $route2 = Route::factory()->create();

        $sourceDate = now()->addDays(3)->format('Y-m-d');
        $targetDate = now()->addDays(10)->format('Y-m-d');

        // Create source assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => $sourceDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create conflicting assignment on target date
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => $targetDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.assignments.copy'), [
            'source_date' => $sourceDate,
            'target_date' => $targetDate,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_selective_copying_with_truck_filters(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck1 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $truck2 = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');
        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');
        $route1 = Route::factory()->create();
        $route2 = Route::factory()->create();

        $sourceDate = now()->addDays(3)->format('Y-m-d');
        $targetDate = now()->addDays(10)->format('Y-m-d');

        // Create two source assignments
        Assignment::factory()->create([
            'truck_id' => $truck1->id,
            'user_id' => $crew1->id,
            'route_id' => $route1->id,
            'assignment_date' => $sourceDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        Assignment::factory()->create([
            'truck_id' => $truck2->id,
            'user_id' => $crew2->id,
            'route_id' => $route2->id,
            'assignment_date' => $sourceDate,
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Copy only truck1's assignment
        $response = $this->actingAs($admin)->post(route('admin.assignments.copy'), [
            'source_date' => $sourceDate,
            'target_date' => $targetDate,
            'truck_ids' => [$truck1->id],
        ]);

        $response->assertRedirect(route('admin.assignments.index'));
        $response->assertSessionHas('success');

        // Verify only truck1's assignment was copied
        $truck1Assignment = Assignment::where('truck_id', $truck1->id)
            ->whereDate('assignment_date', $targetDate)
            ->where('status', Assignment::STATUS_ACTIVE)
            ->first();
        
        $this->assertNotNull($truck1Assignment);

        $truck2Assignment = Assignment::where('truck_id', $truck2->id)
            ->whereDate('assignment_date', $targetDate)
            ->where('status', Assignment::STATUS_ACTIVE)
            ->first();
        
        $this->assertNull($truck2Assignment);
    }

    // ========================================
    // 22.3 Test assignment authorization
    // ========================================

    public function test_only_administrators_can_access_assignment_index(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('admin.assignments.index'));
        $response->assertRedirect();

        // Crew cannot access
        $response = $this->actingAs($crew)->get(route('admin.assignments.index'));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_create_assignments(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $crewMember = User::factory()->create();
        $crewMember->assignRole('collection_crew');
        $route = Route::factory()->create();

        $assignmentData = [
            'truck_id' => $truck->id,
            'user_id' => $crewMember->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3)->format('Y-m-d'),
        ];

        // Resident cannot create
        $response = $this->actingAs($resident)->post(route('admin.assignments.store'), $assignmentData);
        $response->assertRedirect();

        // Crew cannot create
        $response = $this->actingAs($crew)->post(route('admin.assignments.store'), $assignmentData);
        $response->assertRedirect();

        // Verify assignment was not created
        $this->assertDatabaseMissing('assignments', [
            'truck_id' => $truck->id,
            'route_id' => $route->id,
        ]);
    }

    public function test_only_administrators_can_view_assignment_details(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $assignment = Assignment::factory()->create();

        // Resident cannot view
        $response = $this->actingAs($resident)->get(route('admin.assignments.show', $assignment));
        $response->assertRedirect();

        // Crew cannot view
        $response = $this->actingAs($crew)->get(route('admin.assignments.show', $assignment));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_edit_assignments(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $assignment = Assignment::factory()->create();

        $updateData = [
            'truck_id' => $assignment->truck_id,
            'user_id' => $assignment->user_id,
            'route_id' => $assignment->route_id,
            'assignment_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        // Resident cannot edit
        $response = $this->actingAs($resident)->patch(route('admin.assignments.update', $assignment), $updateData);
        $response->assertRedirect();

        // Crew cannot edit
        $response = $this->actingAs($crew)->patch(route('admin.assignments.update', $assignment), $updateData);
        $response->assertRedirect();

        // Verify assignment was not updated
        $assignment->refresh();
        $this->assertNotEquals(now()->addDays(5)->format('Y-m-d'), $assignment->assignment_date->format('Y-m-d'));
    }

    public function test_only_administrators_can_cancel_assignments(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $assignment1 = Assignment::factory()->create(['status' => Assignment::STATUS_ACTIVE]);
        $assignment2 = Assignment::factory()->create(['status' => Assignment::STATUS_ACTIVE]);

        // Resident cannot cancel
        $response = $this->actingAs($resident)->patch(route('admin.assignments.cancel', $assignment1));
        $response->assertRedirect();

        // Crew cannot cancel
        $response = $this->actingAs($crew)->patch(route('admin.assignments.cancel', $assignment2));
        $response->assertRedirect();

        // Verify assignments were not cancelled
        $assignment1->refresh();
        $assignment2->refresh();
        $this->assertEquals(Assignment::STATUS_ACTIVE, $assignment1->status);
        $this->assertEquals(Assignment::STATUS_ACTIVE, $assignment2->status);
    }

    public function test_only_administrators_can_copy_assignments(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $sourceDate = now()->addDays(3)->format('Y-m-d');
        $targetDate = now()->addDays(10)->format('Y-m-d');

        $copyData = [
            'source_date' => $sourceDate,
            'target_date' => $targetDate,
        ];

        // Resident cannot copy
        $response = $this->actingAs($resident)->post(route('admin.assignments.copy'), $copyData);
        $response->assertRedirect();

        // Crew cannot copy
        $response = $this->actingAs($crew)->post(route('admin.assignments.copy'), $copyData);
        $response->assertRedirect();
    }

    public function test_unauthenticated_users_cannot_access_assignment_management(): void
    {
        $assignment = Assignment::factory()->create();

        // Test all assignment management endpoints
        $this->get(route('admin.assignments.index'))->assertRedirect(route('login'));
        $this->get(route('admin.assignments.create'))->assertRedirect(route('login'));
        $this->post(route('admin.assignments.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.assignments.show', $assignment))->assertRedirect(route('login'));
        $this->get(route('admin.assignments.edit', $assignment))->assertRedirect(route('login'));
        $this->patch(route('admin.assignments.update', $assignment), [])->assertRedirect(route('login'));
        $this->patch(route('admin.assignments.cancel', $assignment))->assertRedirect(route('login'));
        $this->post(route('admin.assignments.copy'), [])->assertRedirect(route('login'));
    }
}
