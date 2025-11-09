<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\RecyclingLog;
use App\Models\RecyclingLogMaterial;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecyclingCrewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /**
     * Test: Login as collection_crew user
     * Test: Create new recycling log with multiple materials
     * Test: Verify total weight calculation
     */
    public function test_crew_can_create_recycling_log_with_multiple_materials(): void
    {
        // Create crew user
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Create supporting data
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Login as crew
        $this->actingAs($crew);

        // Create recycling log with multiple materials
        $response = $this->post(route('crew.recycling-logs.store'), [
            'collection_date' => now()->format('Y-m-d'),
            'notes' => 'Test collection with multiple materials',
            'quality_issue' => false,
            'materials' => [
                [
                    'material_type' => 'plastic',
                    'weight' => 25.50,
                ],
                [
                    'material_type' => 'paper',
                    'weight' => 30.75,
                ],
                [
                    'material_type' => 'glass',
                    'weight' => 15.25,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify log was created
        $this->assertDatabaseHas('recycling_logs', [
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'notes' => 'Test collection with multiple materials',
            'quality_issue' => false,
        ]);

        // Verify materials were created
        $log = RecyclingLog::where('user_id', $crew->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(3, $log->materials()->count());

        // Verify total weight calculation
        $expectedTotal = 25.50 + 30.75 + 15.25; // 71.50
        $this->assertEquals($expectedTotal, $log->getTotalWeight());

        // Verify individual materials
        $this->assertDatabaseHas('recycling_log_materials', [
            'recycling_log_id' => $log->id,
            'material_type' => 'plastic',
            'weight' => 25.50,
        ]);
        $this->assertDatabaseHas('recycling_log_materials', [
            'recycling_log_id' => $log->id,
            'material_type' => 'paper',
            'weight' => 30.75,
        ]);
        $this->assertDatabaseHas('recycling_log_materials', [
            'recycling_log_id' => $log->id,
            'material_type' => 'glass',
            'weight' => 15.25,
        ]);
    }

    /**
     * Test: Edit log within 2-hour window
     */
    public function test_crew_can_edit_log_within_two_hour_window(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $route = Route::factory()->create();
        
        // Create a log that was created 30 minutes ago (within 2-hour window)
        $log = RecyclingLog::factory()->create([
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'collection_date' => now(),
            'notes' => 'Original notes',
            'quality_issue' => false,
            'created_at' => now()->subMinutes(30),
        ]);

        // Create original materials
        RecyclingLogMaterial::factory()->create([
            'recycling_log_id' => $log->id,
            'material_type' => 'plastic',
            'weight' => 20.00,
        ]);

        $this->actingAs($crew);

        // Edit the log
        $response = $this->put(route('crew.recycling-logs.update', $log), [
            'collection_date' => now()->format('Y-m-d'),
            'notes' => 'Updated notes within window',
            'quality_issue' => true,
            'materials' => [
                [
                    'material_type' => 'plastic',
                    'weight' => 25.00, // Updated weight
                ],
                [
                    'material_type' => 'paper',
                    'weight' => 10.00, // New material
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify log was updated
        $log->refresh();
        $this->assertEquals('Updated notes within window', $log->notes);
        $this->assertTrue($log->quality_issue);

        // Verify materials were updated
        $this->assertEquals(2, $log->materials()->count());
        $this->assertEquals(35.00, $log->getTotalWeight());
    }

    /**
     * Test: Verify edit disabled after 2-hour window
     */
    public function test_crew_cannot_edit_log_after_two_hour_window(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $route = Route::factory()->create();
        
        // Create a log that was created 3 hours ago (outside 2-hour window)
        $log = RecyclingLog::factory()->create([
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'collection_date' => now(),
            'notes' => 'Original notes',
            'quality_issue' => false,
            'created_at' => now()->subHours(3),
        ]);

        RecyclingLogMaterial::factory()->create([
            'recycling_log_id' => $log->id,
            'material_type' => 'plastic',
            'weight' => 20.00,
        ]);

        $this->actingAs($crew);

        // Try to edit the log
        $response = $this->put(route('crew.recycling-logs.update', $log), [
            'collection_date' => now()->format('Y-m-d'),
            'notes' => 'Attempted update after window',
            'quality_issue' => true,
            'materials' => [
                [
                    'material_type' => 'plastic',
                    'weight' => 25.00,
                ],
            ],
        ]);

        // Should be forbidden
        $response->assertStatus(403);

        // Verify log was NOT updated
        $log->refresh();
        $this->assertEquals('Original notes', $log->notes);
        $this->assertFalse($log->quality_issue);
    }

    /**
     * Test: Filter logs by date range
     */
    public function test_crew_can_filter_logs_by_date_range(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $route = Route::factory()->create();

        // Create logs on different dates
        $log1 = RecyclingLog::factory()->create([
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'collection_date' => now()->subDays(10),
        ]);
        RecyclingLogMaterial::factory()->create([
            'recycling_log_id' => $log1->id,
            'material_type' => 'plastic',
            'weight' => 10.00,
        ]);

        $log2 = RecyclingLog::factory()->create([
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'collection_date' => now()->subDays(5),
        ]);
        RecyclingLogMaterial::factory()->create([
            'recycling_log_id' => $log2->id,
            'material_type' => 'paper',
            'weight' => 15.00,
        ]);

        $log3 = RecyclingLog::factory()->create([
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'collection_date' => now()->subDays(1),
        ]);
        RecyclingLogMaterial::factory()->create([
            'recycling_log_id' => $log3->id,
            'material_type' => 'glass',
            'weight' => 20.00,
        ]);

        $this->actingAs($crew);

        // Filter logs from 7 days ago to today
        $response = $this->get(route('crew.recycling-logs.index', [
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        
        // Should see log2 and log3, but not log1
        $response->assertSee($log2->collection_date->format('M d, Y'));
        $response->assertSee($log3->collection_date->format('M d, Y'));
        $response->assertDontSee($log1->collection_date->format('M d, Y'));
    }

    /**
     * Test: Verify only own logs are visible
     */
    public function test_crew_can_only_see_own_logs(): void
    {
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');

        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');

        $route = Route::factory()->create();

        // Create log for crew1
        $log1 = RecyclingLog::factory()->create([
            'user_id' => $crew1->id,
            'route_id' => $route->id,
            'collection_date' => now(),
            'notes' => 'Crew 1 log',
        ]);
        RecyclingLogMaterial::factory()->create([
            'recycling_log_id' => $log1->id,
            'material_type' => 'plastic',
            'weight' => 10.00,
        ]);

        // Create log for crew2
        $log2 = RecyclingLog::factory()->create([
            'user_id' => $crew2->id,
            'route_id' => $route->id,
            'collection_date' => now(),
            'notes' => 'Crew 2 log',
        ]);
        RecyclingLogMaterial::factory()->create([
            'recycling_log_id' => $log2->id,
            'material_type' => 'paper',
            'weight' => 15.00,
        ]);

        // Login as crew1
        $this->actingAs($crew1);

        $response = $this->get(route('crew.recycling-logs.index'));

        $response->assertStatus(200);
        
        // Should see own log
        $response->assertSee('Crew 1 log');
        
        // Should NOT see other crew's log
        $response->assertDontSee('Crew 2 log');
    }

    /**
     * Test: Crew cannot edit another crew's log
     */
    public function test_crew_cannot_edit_another_crews_log(): void
    {
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');

        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');

        $route = Route::factory()->create();

        // Create log for crew1
        $log = RecyclingLog::factory()->create([
            'user_id' => $crew1->id,
            'route_id' => $route->id,
            'collection_date' => now(),
            'notes' => 'Crew 1 original notes',
            'created_at' => now()->subMinutes(30), // Within edit window
        ]);
        RecyclingLogMaterial::factory()->create([
            'recycling_log_id' => $log->id,
            'material_type' => 'plastic',
            'weight' => 10.00,
        ]);

        // Login as crew2 and try to edit crew1's log
        $this->actingAs($crew2);

        $response = $this->put(route('crew.recycling-logs.update', $log), [
            'collection_date' => now()->format('Y-m-d'),
            'notes' => 'Crew 2 trying to hack',
            'quality_issue' => false,
            'materials' => [
                [
                    'material_type' => 'plastic',
                    'weight' => 99.00,
                ],
            ],
        ]);

        // Should be forbidden
        $response->assertStatus(403);

        // Verify log was NOT updated
        $log->refresh();
        $this->assertEquals('Crew 1 original notes', $log->notes);
    }
}
