<?php

namespace Tests\Feature;

use App\Models\AdminNote;
use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCollectionLogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // 22.1 Test admin log viewing
    // ========================================

    public function test_admin_can_view_collection_logs_listing(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Create multiple assignments and logs
        for ($i = 0; $i < 5; $i++) {
            $assignment = Assignment::factory()->create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => now()->subDays($i),
                'status' => Assignment::STATUS_ACTIVE,
            ]);

            CollectionLog::factory()->create([
                'assignment_id' => $assignment->id,
                'created_by' => $crew->id,
                'status' => CollectionLog::STATUS_COMPLETED,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.collection-logs.index'));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
        
        $logs = $response->viewData('logs');
        $this->assertGreaterThanOrEqual(5, $logs->total());
    }

    public function test_admin_can_filter_logs_by_date_range(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Create logs for different dates
        $oldAssignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(60),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        CollectionLog::factory()->create([
            'assignment_id' => $oldAssignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        $recentAssignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDays(5),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        CollectionLog::factory()->create([
            'assignment_id' => $recentAssignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Filter to last 30 days
        $response = $this->actingAs($admin)->get(route('admin.collection-logs.index', [
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Should only include the recent log
        $this->assertEquals(1, $logs->total());
    }

    public function test_admin_can_filter_logs_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Create completed log
        $completedAssignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        CollectionLog::factory()->create([
            'assignment_id' => $completedAssignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Create issue log
        $issueAssignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDay(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        CollectionLog::factory()->create([
            'assignment_id' => $issueAssignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_type' => 'blocked_road',
            'issue_description' => 'Road blocked',
        ]);

        // Filter by completed status
        $response = $this->actingAs($admin)->get(route('admin.collection-logs.index', [
            'status' => CollectionLog::STATUS_COMPLETED,
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Should only include completed logs
        foreach ($logs as $log) {
            $this->assertEquals(CollectionLog::STATUS_COMPLETED, $log->status);
        }
    }

    public function test_admin_can_filter_logs_by_route(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route1 = Route::factory()->create(['name' => 'Route A']);
        $route2 = Route::factory()->create(['name' => 'Route B']);
        
        // Create log for route1
        $assignment1 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route1->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        CollectionLog::factory()->create([
            'assignment_id' => $assignment1->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Create log for route2
        $assignment2 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route2->id,
            'assignment_date' => now()->subDay(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        CollectionLog::factory()->create([
            'assignment_id' => $assignment2->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Filter by route1
        $response = $this->actingAs($admin)->get(route('admin.collection-logs.index', [
            'route_id' => $route1->id,
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Should only include logs for route1
        foreach ($logs as $log) {
            $this->assertEquals($route1->id, $log->assignment->route_id);
        }
    }

    public function test_admin_can_filter_logs_by_crew_member(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew1 = User::factory()->create(['name' => 'Crew Member 1']);
        $crew1->assignRole('collection_crew');

        $crew2 = User::factory()->create(['name' => 'Crew Member 2']);
        $crew2->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Create log for crew1
        $assignment1 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew1->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        CollectionLog::factory()->create([
            'assignment_id' => $assignment1->id,
            'created_by' => $crew1->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Create log for crew2
        $assignment2 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew2->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDay(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        CollectionLog::factory()->create([
            'assignment_id' => $assignment2->id,
            'created_by' => $crew2->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Filter by crew1
        $response = $this->actingAs($admin)->get(route('admin.collection-logs.index', [
            'user_id' => $crew1->id,
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Should only include logs for crew1
        foreach ($logs as $log) {
            $this->assertEquals($crew1->id, $log->created_by);
        }
    }

    public function test_admin_can_view_detailed_collection_log(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
            'crew_notes' => 'Test notes',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.collection-logs.show', $log));

        $response->assertStatus(200);
        $response->assertViewHas('collectionLog');
        
        $viewLog = $response->viewData('collectionLog');
        $this->assertEquals($log->id, $viewLog->id);
        $this->assertNotNull($viewLog->assignment);
        $this->assertNotNull($viewLog->assignment->truck);
        $this->assertNotNull($viewLog->assignment->route);
        $this->assertNotNull($viewLog->assignment->user);
    }

    // ========================================
    // 22.2 Test admin note functionality
    // ========================================

    public function test_admin_can_add_note_to_collection_log(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.collection-logs.notes.add', $log), [
            'note' => 'This is an admin note for review.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('admin_notes', [
            'collection_log_id' => $log->id,
            'admin_id' => $admin->id,
            'note' => 'This is an admin note for review.',
        ]);
    }

    public function test_admin_notes_are_displayed_in_log_view(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Add admin note
        $adminNote = AdminNote::create([
            'collection_log_id' => $log->id,
            'admin_id' => $admin->id,
            'note' => 'Admin review note',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.collection-logs.show', $log));

        $response->assertStatus(200);
        
        $viewLog = $response->viewData('collectionLog');
        $this->assertCount(1, $viewLog->adminNotes);
        $this->assertEquals('Admin review note', $viewLog->adminNotes->first()->note);
    }

    public function test_multiple_admin_notes_can_be_added_to_log(): void
    {
        $admin1 = User::factory()->create(['name' => 'Admin One']);
        $admin1->assignRole('administrator');

        $admin2 = User::factory()->create(['name' => 'Admin Two']);
        $admin2->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_type' => 'blocked_road',
            'issue_description' => 'Road blocked',
        ]);

        // Admin1 adds a note
        $this->actingAs($admin1)->post(route('admin.collection-logs.notes.add', $log), [
            'note' => 'First admin note',
        ]);

        // Admin2 adds a note
        $this->actingAs($admin2)->post(route('admin.collection-logs.notes.add', $log), [
            'note' => 'Second admin note',
        ]);

        $log->refresh();
        $this->assertEquals(2, $log->adminNotes()->count());
        
        $notes = $log->adminNotes()->with('admin')->get();
        $this->assertEquals($admin1->id, $notes[0]->admin_id);
        $this->assertEquals($admin2->id, $notes[1]->admin_id);
    }

    public function test_admin_identification_is_stored_with_notes(): void
    {
        $admin = User::factory()->create(['name' => 'Test Administrator']);
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        $this->actingAs($admin)->post(route('admin.collection-logs.notes.add', $log), [
            'note' => 'Admin note with identification',
        ]);

        $adminNote = AdminNote::where('collection_log_id', $log->id)->first();
        $this->assertNotNull($adminNote);
        $this->assertEquals($admin->id, $adminNote->admin_id);
        $this->assertEquals('Test Administrator', $adminNote->admin->name);
    }

    // ========================================
    // 22.3 Test admin authorization
    // ========================================

    public function test_only_administrators_can_view_all_logs(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Crew member cannot access admin logs index
        $response = $this->actingAs($crew)->get(route('admin.collection-logs.index'));
        $response->assertRedirect();

        // Resident cannot access admin logs index
        $response = $this->actingAs($resident)->get(route('admin.collection-logs.index'));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_add_admin_notes(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Crew member cannot add admin notes
        $response = $this->actingAs($crew)->post(route('admin.collection-logs.notes.add', $log), [
            'note' => 'Unauthorized note',
        ]);
        $response->assertRedirect();

        // Resident cannot add admin notes
        $response = $this->actingAs($resident)->post(route('admin.collection-logs.notes.add', $log), [
            'note' => 'Unauthorized note',
        ]);
        $response->assertRedirect();

        // Verify no notes were added
        $this->assertEquals(0, $log->adminNotes()->count());
    }

    public function test_unauthenticated_users_cannot_access_admin_log_management(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Test all admin endpoints
        $this->get(route('admin.collection-logs.index'))->assertRedirect(route('login'));
        $this->get(route('admin.collection-logs.show', $log))->assertRedirect(route('login'));
        $this->post(route('admin.collection-logs.notes.add', $log), [])->assertRedirect(route('login'));
        $this->get(route('admin.collection-logs.issues.analysis'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_logs_from_all_crew_members(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew1 = User::factory()->create(['name' => 'Crew 1']);
        $crew1->assignRole('collection_crew');

        $crew2 = User::factory()->create(['name' => 'Crew 2']);
        $crew2->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Create logs for both crew members
        $assignment1 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew1->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log1 = CollectionLog::factory()->create([
            'assignment_id' => $assignment1->id,
            'created_by' => $crew1->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        $assignment2 = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew2->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDay(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log2 = CollectionLog::factory()->create([
            'assignment_id' => $assignment2->id,
            'created_by' => $crew2->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.collection-logs.index'));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Admin should see logs from both crew members
        $this->assertGreaterThanOrEqual(2, $logs->total());
        
        $logIds = $logs->pluck('id')->toArray();
        $this->assertContains($log1->id, $logIds);
        $this->assertContains($log2->id, $logIds);
    }
}
