<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Route;
use App\Models\Truck;
use App\Models\TruckStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TruckManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // 21.1 Test truck CRUD operations
    // ========================================

    public function test_administrator_can_register_truck(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.trucks.store'), [
            'truck_number' => 'T-1001',
            'license_plate' => 'ABC-1234',
            'capacity' => 15.5,
            'operational_status' => Truck::STATUS_OPERATIONAL,
            'notes' => 'New truck for north district',
        ]);

        $response->assertRedirect(route('admin.trucks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trucks', [
            'truck_number' => 'T-1001',
            'license_plate' => 'ABC-1234',
            'capacity' => 15.5,
            'operational_status' => Truck::STATUS_OPERATIONAL,
            'notes' => 'New truck for north district',
        ]);
    }

    public function test_duplicate_truck_number_validation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create existing truck
        Truck::factory()->create(['truck_number' => 'T-1001']);

        // Try to create truck with duplicate number
        $response = $this->actingAs($admin)->post(route('admin.trucks.store'), [
            'truck_number' => 'T-1001',
            'license_plate' => 'XYZ-5678',
            'capacity' => 12.0,
            'operational_status' => Truck::STATUS_OPERATIONAL,
        ]);

        $response->assertSessionHasErrors('truck_number');
    }

    public function test_truck_listing_displays_all_trucks(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck1 = Truck::factory()->create(['truck_number' => 'T-1001']);
        $truck2 = Truck::factory()->create(['truck_number' => 'T-1002']);
        $truck3 = Truck::factory()->maintenance()->create(['truck_number' => 'T-1003']);

        $response = $this->actingAs($admin)->get(route('admin.trucks.index'));

        $response->assertOk();
        $response->assertSee('T-1001');
        $response->assertSee('T-1002');
        $response->assertSee('T-1003');
    }

    public function test_truck_search_by_truck_number(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Truck::factory()->create(['truck_number' => 'T-1001']);
        Truck::factory()->create(['truck_number' => 'T-2002']);

        $response = $this->actingAs($admin)->get(route('admin.trucks.index', ['search' => 'T-1001']));

        $response->assertOk();
        $response->assertSee('T-1001');
        $response->assertDontSee('T-2002');
    }

    public function test_truck_search_by_license_plate(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Truck::factory()->create([
            'truck_number' => 'T-1001',
            'license_plate' => 'ABC-1234'
        ]);
        Truck::factory()->create([
            'truck_number' => 'T-1002',
            'license_plate' => 'XYZ-5678'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.index', ['search' => 'ABC']));

        $response->assertOk();
        $response->assertSee('ABC-1234');
        $response->assertDontSee('XYZ-5678');
    }

    public function test_truck_filter_by_operational_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $operational = Truck::factory()->create([
            'truck_number' => 'T-OP-001',
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);
        $maintenance = Truck::factory()->create([
            'truck_number' => 'T-MT-001',
            'operational_status' => Truck::STATUS_MAINTENANCE
        ]);

        // Filter for operational trucks
        $response = $this->actingAs($admin)->get(route('admin.trucks.index', ['status' => 'operational']));
        $response->assertOk();
        $response->assertSee('T-OP-001');
        $response->assertDontSee('T-MT-001');

        // Filter for maintenance trucks
        $response = $this->actingAs($admin)->get(route('admin.trucks.index', ['status' => 'maintenance']));
        $response->assertOk();
        $response->assertSee('T-MT-001');
        $response->assertDontSee('T-OP-001');
    }

    public function test_administrator_can_view_truck_details(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create([
            'truck_number' => 'T-1001',
            'license_plate' => 'ABC-1234',
            'capacity' => 15.5,
            'notes' => 'Test notes',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.trucks.show', $truck));

        $response->assertOk();
        $response->assertSee('T-1001');
        $response->assertSee('ABC-1234');
        $response->assertSee('15.5');
        $response->assertSee('Test notes');
    }

    public function test_administrator_can_edit_truck(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create([
            'truck_number' => 'T-1001',
            'license_plate' => 'OLD-1234',
            'capacity' => 10.0,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.trucks.update', $truck), [
            'truck_number' => 'T-1001',
            'license_plate' => 'NEW-5678',
            'capacity' => 15.5,
            'operational_status' => Truck::STATUS_OPERATIONAL,
            'notes' => 'Updated notes',
        ]);

        $response->assertRedirect(route('admin.trucks.index'));
        $response->assertSessionHas('success');

        $truck->refresh();
        $this->assertEquals('NEW-5678', $truck->license_plate);
        $this->assertEquals(15.5, $truck->capacity);
        $this->assertEquals('Updated notes', $truck->notes);
    }

    public function test_truck_number_uniqueness_validation_on_update(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck1 = Truck::factory()->create(['truck_number' => 'T-1001']);
        $truck2 = Truck::factory()->create(['truck_number' => 'T-1002']);

        // Try to update truck2 with truck1's number
        $response = $this->actingAs($admin)->patch(route('admin.trucks.update', $truck2), [
            'truck_number' => 'T-1001',
            'license_plate' => 'ABC-1234',
            'capacity' => 15.0,
        ]);

        $response->assertSessionHasErrors('truck_number');
    }

    public function test_administrator_can_delete_truck_without_future_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);

        $response = $this->actingAs($admin)->delete(route('admin.trucks.destroy', $truck));

        $response->assertRedirect(route('admin.trucks.index'));
        $response->assertSessionHas('success');

        // Truck should be soft deleted
        $this->assertSoftDeleted('trucks', ['id' => $truck->id]);
    }

    public function test_cannot_delete_truck_with_future_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create a future assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(5),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.trucks.destroy', $truck));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Truck should NOT be deleted
        $this->assertDatabaseHas('trucks', [
            'id' => $truck->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_delete_truck_with_past_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create a past assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(5),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.trucks.destroy', $truck));

        $response->assertRedirect(route('admin.trucks.index'));
        $response->assertSessionHas('success');

        // Truck should be soft deleted
        $this->assertSoftDeleted('trucks', ['id' => $truck->id]);
    }

    public function test_can_delete_truck_with_cancelled_future_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create(['truck_number' => 'T-1001']);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create a cancelled future assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(5),
            'status' => Assignment::STATUS_CANCELLED,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.trucks.destroy', $truck));

        $response->assertRedirect(route('admin.trucks.index'));
        $response->assertSessionHas('success');

        // Truck should be soft deleted
        $this->assertSoftDeleted('trucks', ['id' => $truck->id]);
    }

    // ========================================
    // 21.2 Test truck status management
    // ========================================

    public function test_administrator_can_update_truck_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create([
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.trucks.update-status', $truck), [
            'operational_status' => Truck::STATUS_MAINTENANCE,
            'notes' => 'Scheduled maintenance',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $truck->refresh();
        $this->assertEquals(Truck::STATUS_MAINTENANCE, $truck->operational_status);
    }

    public function test_status_change_is_logged_in_history(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create([
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        $this->actingAs($admin)->patch(route('admin.trucks.update-status', $truck), [
            'operational_status' => Truck::STATUS_MAINTENANCE,
            'notes' => 'Scheduled maintenance',
        ]);

        $this->assertDatabaseHas('truck_status_history', [
            'truck_id' => $truck->id,
            'old_status' => Truck::STATUS_OPERATIONAL,
            'new_status' => Truck::STATUS_MAINTENANCE,
            'changed_by' => $admin->id,
            'notes' => 'Scheduled maintenance',
        ]);
    }

    public function test_status_history_includes_timestamp(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create([
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        $this->actingAs($admin)->patch(route('admin.trucks.update-status', $truck), [
            'operational_status' => Truck::STATUS_OUT_OF_SERVICE,
            'notes' => 'Major repairs needed',
        ]);

        $history = TruckStatusHistory::where('truck_id', $truck->id)->first();
        $this->assertNotNull($history);
        $this->assertNotNull($history->created_at);
    }

    public function test_warning_displayed_when_changing_status_with_future_assignments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create([
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        $route = Route::factory()->create();

        // Create future assignment
        Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->addDays(3),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.trucks.update-status', $truck), [
            'operational_status' => Truck::STATUS_MAINTENANCE,
            'notes' => 'Emergency maintenance',
        ]);

        // Status should still be updated, but with a warning
        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $truck->refresh();
        $this->assertEquals(Truck::STATUS_MAINTENANCE, $truck->operational_status);
    }

    public function test_multiple_status_changes_create_history_trail(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $truck = Truck::factory()->create([
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        // First status change
        $this->actingAs($admin)->patch(route('admin.trucks.update-status', $truck), [
            'operational_status' => Truck::STATUS_MAINTENANCE,
            'notes' => 'Routine maintenance',
        ]);

        // Second status change
        $truck->refresh();
        $this->actingAs($admin)->patch(route('admin.trucks.update-status', $truck), [
            'operational_status' => Truck::STATUS_OPERATIONAL,
            'notes' => 'Maintenance completed',
        ]);

        // Third status change
        $truck->refresh();
        $this->actingAs($admin)->patch(route('admin.trucks.update-status', $truck), [
            'operational_status' => Truck::STATUS_OUT_OF_SERVICE,
            'notes' => 'Failed inspection',
        ]);

        $historyCount = TruckStatusHistory::where('truck_id', $truck->id)->count();
        $this->assertEquals(3, $historyCount);

        // Verify the sequence
        $history = TruckStatusHistory::where('truck_id', $truck->id)
            ->orderBy('created_at')
            ->get();

        $this->assertEquals(Truck::STATUS_OPERATIONAL, $history[0]->old_status);
        $this->assertEquals(Truck::STATUS_MAINTENANCE, $history[0]->new_status);

        $this->assertEquals(Truck::STATUS_MAINTENANCE, $history[1]->old_status);
        $this->assertEquals(Truck::STATUS_OPERATIONAL, $history[1]->new_status);

        $this->assertEquals(Truck::STATUS_OPERATIONAL, $history[2]->old_status);
        $this->assertEquals(Truck::STATUS_OUT_OF_SERVICE, $history[2]->new_status);
    }

    // ========================================
    // 21.3 Test truck authorization
    // ========================================

    public function test_only_administrators_can_access_truck_index(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('admin.trucks.index'));
        $response->assertRedirect();

        // Crew cannot access
        $response = $this->actingAs($crew)->get(route('admin.trucks.index'));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_create_trucks(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truckData = [
            'truck_number' => 'T-1001',
            'license_plate' => 'ABC-1234',
            'capacity' => 15.0,
            'operational_status' => Truck::STATUS_OPERATIONAL,
        ];

        // Resident cannot create
        $response = $this->actingAs($resident)->post(route('admin.trucks.store'), $truckData);
        $response->assertRedirect();

        // Crew cannot create
        $response = $this->actingAs($crew)->post(route('admin.trucks.store'), $truckData);
        $response->assertRedirect();

        // Verify truck was not created
        $this->assertDatabaseMissing('trucks', ['truck_number' => 'T-1001']);
    }

    public function test_only_administrators_can_view_truck_details(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create();

        // Resident cannot view
        $response = $this->actingAs($resident)->get(route('admin.trucks.show', $truck));
        $response->assertRedirect();

        // Crew cannot view
        $response = $this->actingAs($crew)->get(route('admin.trucks.show', $truck));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_edit_trucks(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create([
            'license_plate' => 'ORIGINAL-123'
        ]);

        $updateData = [
            'truck_number' => $truck->truck_number,
            'license_plate' => 'UPDATED-456',
            'capacity' => 15.0,
        ];

        // Resident cannot edit
        $response = $this->actingAs($resident)->patch(route('admin.trucks.update', $truck), $updateData);
        $response->assertRedirect();

        // Crew cannot edit
        $response = $this->actingAs($crew)->patch(route('admin.trucks.update', $truck), $updateData);
        $response->assertRedirect();

        // Verify truck was not updated
        $truck->refresh();
        $this->assertEquals('ORIGINAL-123', $truck->license_plate);
    }

    public function test_only_administrators_can_delete_trucks(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck1 = Truck::factory()->create();
        $truck2 = Truck::factory()->create();

        // Resident cannot delete
        $response = $this->actingAs($resident)->delete(route('admin.trucks.destroy', $truck1));
        $response->assertRedirect();

        // Crew cannot delete
        $response = $this->actingAs($crew)->delete(route('admin.trucks.destroy', $truck2));
        $response->assertRedirect();

        // Verify trucks were not deleted
        $this->assertDatabaseHas('trucks', ['id' => $truck1->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('trucks', ['id' => $truck2->id, 'deleted_at' => null]);
    }

    public function test_only_administrators_can_update_truck_status(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create([
            'operational_status' => Truck::STATUS_OPERATIONAL
        ]);

        $statusData = [
            'operational_status' => Truck::STATUS_MAINTENANCE,
            'notes' => 'Maintenance needed',
        ];

        // Resident cannot update status
        $response = $this->actingAs($resident)->patch(route('admin.trucks.update-status', $truck), $statusData);
        $response->assertRedirect();

        // Crew cannot update status
        $response = $this->actingAs($crew)->patch(route('admin.trucks.update-status', $truck), $statusData);
        $response->assertRedirect();

        // Verify status was not changed
        $truck->refresh();
        $this->assertEquals(Truck::STATUS_OPERATIONAL, $truck->operational_status);
    }

    public function test_unauthenticated_users_cannot_access_truck_management(): void
    {
        $truck = Truck::factory()->create();

        // Test all truck management endpoints
        $this->get(route('admin.trucks.index'))->assertRedirect(route('login'));
        $this->get(route('admin.trucks.create'))->assertRedirect(route('login'));
        $this->post(route('admin.trucks.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.trucks.show', $truck))->assertRedirect(route('login'));
        $this->get(route('admin.trucks.edit', $truck))->assertRedirect(route('login'));
        $this->patch(route('admin.trucks.update', $truck), [])->assertRedirect(route('login'));
        $this->delete(route('admin.trucks.destroy', $truck))->assertRedirect(route('login'));
        $this->patch(route('admin.trucks.update-status', $truck), [])->assertRedirect(route('login'));
    }
}
