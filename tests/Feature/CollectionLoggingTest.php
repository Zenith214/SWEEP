<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\CollectionPhoto;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CollectionLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        // Fake storage for photo uploads
        Storage::fake('public');
    }

    // ========================================
    // 21.1 Test collection log creation
    // ========================================

    public function test_crew_can_create_completed_collection_log(): void
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

        $completionTime = now()->subHours(1);

        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => $completionTime->format('Y-m-d H:i:s'),
            'crew_notes' => 'Collection completed successfully',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('collection_logs', [
            'assignment_id' => $assignment->id,
            'status' => CollectionLog::STATUS_COMPLETED,
            'created_by' => $crew->id,
            'crew_notes' => 'Collection completed successfully',
        ]);
    }

    public function test_crew_can_create_incomplete_collection_log(): void
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

        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_INCOMPLETE,
            'completion_percentage' => 75,
            'crew_notes' => 'Road blocked, could not complete entire route',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('collection_logs', [
            'assignment_id' => $assignment->id,
            'status' => CollectionLog::STATUS_INCOMPLETE,
            'completion_percentage' => 75,
            'created_by' => $crew->id,
        ]);
    }

    public function test_crew_can_report_issue_during_collection(): void
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

        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_type' => 'blocked_road',
            'issue_description' => 'Main street blocked due to construction',
            'crew_notes' => 'Unable to access several collection points',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('collection_logs', [
            'assignment_id' => $assignment->id,
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_type' => 'blocked_road',
            'issue_description' => 'Main street blocked due to construction',
            'created_by' => $crew->id,
        ]);
    }

    public function test_crew_can_upload_photos_during_log_creation(): void
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

        $photo1 = UploadedFile::fake()->image('photo1.jpg', 800, 600)->size(1024);
        $photo2 = UploadedFile::fake()->image('photo2.jpg', 800, 600)->size(2048);

        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => now()->format('Y-m-d H:i:s'),
            'crew_notes' => 'Collection completed',
            'photos' => [$photo1, $photo2],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $log = CollectionLog::where('assignment_id', $assignment->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(2, $log->photos()->count());
    }

    public function test_collection_log_creation_validates_required_fields(): void
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

        // Missing status
        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'crew_notes' => 'Test',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_completed_status_requires_completion_time(): void
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

        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'crew_notes' => 'Completed',
        ]);

        $response->assertSessionHasErrors('completion_time');
    }

    public function test_issue_reported_status_requires_issue_type_and_description(): void
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

        // Missing issue_type
        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_description' => 'Some issue',
        ]);

        $response->assertSessionHasErrors('issue_type');

        // Missing issue_description
        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_type' => 'blocked_road',
        ]);

        $response->assertSessionHasErrors('issue_description');
    }

    public function test_cannot_create_duplicate_log_for_same_assignment(): void
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

        // Create first log
        CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Try to create second log
        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ========================================
    // 21.2 Test photo management
    // ========================================

    public function test_crew_can_upload_single_photo_to_existing_log(): void
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
            'created_at' => now(),
        ]);

        $photo = UploadedFile::fake()->image('photo.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($crew)->post(route('crew.collections.uploadPhoto', $log), [
            'photo' => $photo,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $log->photos()->count());
    }

    public function test_crew_can_upload_multiple_photos(): void
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

        $photo1 = UploadedFile::fake()->image('photo1.jpg', 800, 600)->size(1024);
        $photo2 = UploadedFile::fake()->image('photo2.jpg', 800, 600)->size(1024);
        $photo3 = UploadedFile::fake()->image('photo3.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($crew)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => now()->format('Y-m-d H:i:s'),
            'photos' => [$photo1, $photo2, $photo3],
        ]);

        $response->assertRedirect();
        
        $log = CollectionLog::where('assignment_id', $assignment->id)->first();
        $this->assertEquals(3, $log->photos()->count());
    }

    public function test_photo_upload_validates_image_format(): void
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
            'created_at' => now(),
        ]);

        // Try to upload a non-image file
        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($crew)->post(route('crew.collections.uploadPhoto', $log), [
            'photo' => $file,
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_photo_upload_validates_file_size(): void
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
            'created_at' => now(),
        ]);

        // Try to upload a file larger than 5MB
        $photo = UploadedFile::fake()->image('large.jpg', 4000, 3000)->size(6000);

        $response = $this->actingAs($crew)->post(route('crew.collections.uploadPhoto', $log), [
            'photo' => $photo,
        ]);

        $response->assertSessionHasErrors('photo');
    }

    public function test_photo_count_limit_enforced(): void
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
            'created_at' => now(),
        ]);

        // Create 5 photos
        CollectionPhoto::factory()->count(5)->create([
            'collection_log_id' => $log->id,
        ]);

        // Try to upload a 6th photo
        $photo = UploadedFile::fake()->image('photo6.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($crew)->post(route('crew.collections.uploadPhoto', $log), [
            'photo' => $photo,
        ]);

        $response->assertStatus(403);
    }

    public function test_crew_can_delete_photo_within_edit_window(): void
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
            'created_at' => now(),
        ]);

        $photo = CollectionPhoto::factory()->create([
            'collection_log_id' => $log->id,
        ]);

        $response = $this->actingAs($crew)->delete(route('crew.collections.deletePhoto', $photo));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('collection_photos', ['id' => $photo->id]);
    }

    public function test_thumbnail_generation_on_photo_upload(): void
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
            'created_at' => now(),
        ]);

        $photo = UploadedFile::fake()->image('photo.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($crew)->post(route('crew.collections.uploadPhoto', $log), [
            'photo' => $photo,
        ]);

        $response->assertStatus(200);
        
        $uploadedPhoto = $log->photos()->first();
        $this->assertNotNull($uploadedPhoto);
        
        // Check that both original and thumbnail exist in storage
        $pathInfo = pathinfo($uploadedPhoto->file_path);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
        Storage::disk('public')->assertExists($uploadedPhoto->file_path);
        Storage::disk('public')->assertExists($thumbnailPath);
    }

    // ========================================
    // 21.3 Test collection log editing
    // ========================================

    public function test_crew_can_edit_log_within_two_hour_window(): void
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
            'crew_notes' => 'Original notes',
            'created_at' => now()->subMinutes(30), // Created 30 minutes ago
        ]);

        $response = $this->actingAs($crew)->patch(route('crew.collections.update', $log), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => now()->format('Y-m-d H:i:s'),
            'crew_notes' => 'Updated notes',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $log->refresh();
        $this->assertEquals('Updated notes', $log->crew_notes);
        $this->assertNotNull($log->edited_at);
    }

    public function test_crew_cannot_edit_log_after_two_hour_window(): void
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
            'crew_notes' => 'Original notes',
            'created_at' => now()->subHours(3), // Created 3 hours ago
        ]);

        $response = $this->actingAs($crew)->patch(route('crew.collections.update', $log), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => now()->format('Y-m-d H:i:s'),
            'crew_notes' => 'Updated notes',
        ]);

        $response->assertStatus(403);

        $log->refresh();
        $this->assertEquals('Original notes', $log->crew_notes);
    }

    public function test_crew_cannot_edit_other_crew_logs(): void
    {
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');

        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew1->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew1->id,
            'status' => CollectionLog::STATUS_COMPLETED,
            'crew_notes' => 'Original notes',
            'created_at' => now()->subMinutes(30),
        ]);

        // Crew2 tries to edit crew1's log
        $response = $this->actingAs($crew2)->patch(route('crew.collections.update', $log), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => now()->format('Y-m-d H:i:s'),
            'crew_notes' => 'Hacked notes',
        ]);

        $response->assertStatus(403);

        $log->refresh();
        $this->assertEquals('Original notes', $log->crew_notes);
    }

    public function test_crew_can_update_status_during_edit(): void
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
            'created_at' => now()->subMinutes(30),
        ]);

        $response = $this->actingAs($crew)->patch(route('crew.collections.update', $log), [
            'status' => CollectionLog::STATUS_ISSUE_REPORTED,
            'issue_type' => 'truck_problem',
            'issue_description' => 'Truck broke down after initial completion',
            'crew_notes' => 'Updated to issue status',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $log->refresh();
        $this->assertEquals(CollectionLog::STATUS_ISSUE_REPORTED, $log->status);
        $this->assertEquals('truck_problem', $log->issue_type);
    }

    public function test_crew_can_add_photos_during_edit(): void
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
            'created_at' => now()->subMinutes(30),
        ]);

        // Initially no photos
        $this->assertEquals(0, $log->photos()->count());

        // Add a photo via AJAX upload
        $photo = UploadedFile::fake()->image('additional.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($crew)->post(route('crew.collections.uploadPhoto', $log), [
            'photo' => $photo,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $log->photos()->count());
    }

    public function test_edit_window_check_prevents_photo_upload_after_expiry(): void
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
            'created_at' => now()->subHours(3), // Created 3 hours ago
        ]);

        $photo = UploadedFile::fake()->image('late.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($crew)->post(route('crew.collections.uploadPhoto', $log), [
            'photo' => $photo,
        ]);

        $response->assertStatus(403);
        $this->assertEquals(0, $log->photos()->count());
    }

    public function test_edit_window_check_prevents_photo_deletion_after_expiry(): void
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
            'created_at' => now()->subHours(3), // Created 3 hours ago
        ]);

        $photo = CollectionPhoto::factory()->create([
            'collection_log_id' => $log->id,
        ]);

        $response = $this->actingAs($crew)->delete(route('crew.collections.deletePhoto', $photo));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('collection_photos', ['id' => $photo->id]);
    }

    // ========================================
    // 21.4 Test crew authorization
    // ========================================

    public function test_only_assigned_crew_can_log_collections(): void
    {
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');

        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Assignment for crew1
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew1->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Crew2 tries to log crew1's assignment
        $response = $this->actingAs($crew2)->post(route('crew.collections.store', $assignment), [
            'status' => CollectionLog::STATUS_COMPLETED,
            'completion_time' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('collection_logs', [
            'assignment_id' => $assignment->id,
        ]);
    }

    public function test_crew_can_view_their_own_collection_history(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Create multiple assignments and logs for this crew
        for ($i = 0; $i < 3; $i++) {
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

        $response = $this->actingAs($crew)->get(route('crew.collections.history'));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
        
        $logs = $response->viewData('logs');
        $this->assertCount(3, $logs);
    }

    public function test_crew_cannot_view_other_crew_history(): void
    {
        $crew1 = User::factory()->create();
        $crew1->assignRole('collection_crew');

        $crew2 = User::factory()->create();
        $crew2->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Create assignment and log for crew1
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew1->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $log = CollectionLog::factory()->create([
            'assignment_id' => $assignment->id,
            'created_by' => $crew1->id,
            'status' => CollectionLog::STATUS_COMPLETED,
        ]);

        // Crew2 tries to view crew1's log
        $response = $this->actingAs($crew2)->get(route('crew.collections.show', $log));

        $response->assertStatus(403);
    }

    public function test_non_crew_users_cannot_access_collection_logging(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        
        $assignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Admin cannot access crew logging endpoints
        $response = $this->actingAs($admin)->get(route('crew.collections.index'));
        $response->assertRedirect();

        // Resident cannot access crew logging endpoints
        $response = $this->actingAs($resident)->get(route('crew.collections.index'));
        $response->assertRedirect();
    }

    public function test_unauthenticated_users_cannot_access_collection_logging(): void
    {
        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');
        
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

        // Test all crew collection endpoints
        $this->get(route('crew.collections.index'))->assertRedirect(route('login'));
        $this->get(route('crew.collections.history'))->assertRedirect(route('login'));
        $this->get(route('crew.collections.create', $assignment))->assertRedirect(route('login'));
        $this->post(route('crew.collections.store', $assignment), [])->assertRedirect(route('login'));
        $this->get(route('crew.collections.show', $log))->assertRedirect(route('login'));
        $this->get(route('crew.collections.edit', $log))->assertRedirect(route('login'));
        $this->patch(route('crew.collections.update', $log), [])->assertRedirect(route('login'));
    }

    public function test_crew_can_only_view_todays_assignment_on_index(): void
    {
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $truck = Truck::factory()->create(['operational_status' => Truck::STATUS_OPERATIONAL]);
        $route = Route::factory()->create();
        
        // Create today's assignment
        $todayAssignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        // Create yesterday's assignment
        $yesterdayAssignment = Assignment::factory()->create([
            'truck_id' => $truck->id,
            'user_id' => $crew->id,
            'route_id' => $route->id,
            'assignment_date' => now()->subDay(),
            'status' => Assignment::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($crew)->get(route('crew.collections.index'));

        $response->assertStatus(200);
        $response->assertViewHas('assignment');
        
        $assignment = $response->viewData('assignment');
        $this->assertNotNull($assignment);
        $this->assertEquals($todayAssignment->id, $assignment->id);
    }
}
