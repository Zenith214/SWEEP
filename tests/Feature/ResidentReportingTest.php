<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportPhoto;
use App\Models\ReportResponse;
use App\Models\ReportStatusHistory;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResidentReportingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        // Fake storage for photo uploads
        Storage::fake('report_photos');
    }

    // Test report submission by resident with photos
    public function test_resident_can_submit_report_with_photos(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $photo1 = UploadedFile::fake()->image('photo1.jpg', 800, 600);
        $photo2 = UploadedFile::fake()->image('photo2.jpg', 800, 600);

        $response = $this->actingAs($resident)->post(route('resident.reports.store'), [
            'report_type' => Report::TYPE_MISSED_PICKUP,
            'location' => '123 Main Street',
            'description' => 'Garbage was not collected on scheduled day',
            'photos' => [$photo1, $photo2]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'resident_id' => $resident->id,
            'report_type' => Report::TYPE_MISSED_PICKUP,
            'location' => '123 Main Street',
            'description' => 'Garbage was not collected on scheduled day',
            'status' => Report::STATUS_PENDING
        ]);

        $report = Report::where('resident_id', $resident->id)->first();
        $this->assertNotNull($report->reference_number);
        $this->assertStringStartsWith('REP-', $report->reference_number);
        $this->assertCount(2, $report->photos);
    }

    public function test_resident_can_submit_report_without_photos(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->post(route('resident.reports.store'), [
            'report_type' => Report::TYPE_UNCOLLECTED_WASTE,
            'location' => '456 Oak Avenue',
            'description' => 'Waste bins left on curb after collection'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'resident_id' => $resident->id,
            'report_type' => Report::TYPE_UNCOLLECTED_WASTE,
            'location' => '456 Oak Avenue'
        ]);
    }

    public function test_report_submission_validates_required_fields(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->post(route('resident.reports.store'), []);

        $response->assertSessionHasErrors(['report_type', 'location', 'description']);
    }

    public function test_report_submission_enforces_photo_limit(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $photos = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg'),
            UploadedFile::fake()->image('photo3.jpg'),
            UploadedFile::fake()->image('photo4.jpg')
        ];

        $response = $this->actingAs($resident)->post(route('resident.reports.store'), [
            'report_type' => Report::TYPE_OTHER,
            'location' => '789 Pine Road',
            'description' => 'Test description',
            'photos' => $photos
        ]);

        $response->assertSessionHasErrors('photos');
    }

    // Test report listing and filtering for resident
    public function test_resident_can_view_their_reports(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        Report::factory()->count(3)->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($resident)->get(route('resident.reports'));

        $response->assertOk();
        $response->assertViewHas('reports');
        $this->assertCount(3, $response->viewData('reports'));
    }

    public function test_resident_can_filter_reports_by_status(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING
        ]);
        Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_RESOLVED
        ]);

        $response = $this->actingAs($resident)->get(route('resident.reports', ['status' => Report::STATUS_PENDING]));

        $response->assertOk();
        $reports = $response->viewData('reports');
        $this->assertCount(1, $reports);
        $this->assertEquals(Report::STATUS_PENDING, $reports->first()->status);
    }

    public function test_resident_only_sees_their_own_reports(): void
    {
        $resident1 = User::factory()->create();
        $resident1->assignRole('resident');
        
        $resident2 = User::factory()->create();
        $resident2->assignRole('resident');

        Report::factory()->count(2)->create(['resident_id' => $resident1->id]);
        Report::factory()->count(3)->create(['resident_id' => $resident2->id]);

        $response = $this->actingAs($resident1)->get(route('resident.reports'));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('reports'));
    }

    // Test report search by reference number
    public function test_resident_can_search_reports_by_reference_number(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $report = Report::factory()->create([
            'resident_id' => $resident->id,
            'reference_number' => 'REP-20251108-0001'
        ]);

        $response = $this->actingAs($resident)->get(route('resident.reports.search', [
            'reference_number' => 'REP-20251108-0001'
        ]));

        $response->assertOk();
        $response->assertSee('REP-20251108-0001');
    }

    public function test_resident_search_returns_not_found_for_invalid_reference(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $response = $this->actingAs($resident)->get(route('resident.reports.search', [
            'reference_number' => 'REP-99999999-9999'
        ]));

        $response->assertOk();
        $response->assertSee('not found');
    }

    // Test report viewing authorization (own reports only)
    public function test_resident_can_view_own_report_details(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($resident)->get(route('resident.reports.show', $report));

        $response->assertOk();
        $response->assertSee($report->reference_number);
        $response->assertSee($report->location);
    }

    public function test_resident_cannot_view_other_residents_reports(): void
    {
        $resident1 = User::factory()->create();
        $resident1->assignRole('resident');
        
        $resident2 = User::factory()->create();
        $resident2->assignRole('resident');

        $report = Report::factory()->create(['resident_id' => $resident2->id]);

        $response = $this->actingAs($resident1)->get(route('resident.reports.show', $report));

        $response->assertForbidden();
    }

    // Test admin report listing with filters
    public function test_admin_can_view_all_reports(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident1 = User::factory()->create();
        $resident1->assignRole('resident');
        $resident2 = User::factory()->create();
        $resident2->assignRole('resident');

        Report::factory()->count(2)->create(['resident_id' => $resident1->id]);
        Report::factory()->count(3)->create(['resident_id' => $resident2->id]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $this->assertCount(5, $response->viewData('reports'));
    }

    public function test_admin_can_filter_reports_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING
        ]);
        Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_IN_PROGRESS
        ]);
        Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_RESOLVED
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'status' => Report::STATUS_PENDING
        ]));

        $response->assertOk();
        $reports = $response->viewData('reports');
        $this->assertCount(1, $reports);
        $this->assertEquals(Report::STATUS_PENDING, $reports->first()->status);
    }

    public function test_admin_can_filter_reports_by_type(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        Report::factory()->create([
            'resident_id' => $resident->id,
            'report_type' => Report::TYPE_MISSED_PICKUP
        ]);
        Report::factory()->create([
            'resident_id' => $resident->id,
            'report_type' => Report::TYPE_ILLEGAL_DUMPING
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'report_type' => Report::TYPE_MISSED_PICKUP
        ]));

        $response->assertOk();
        $reports = $response->viewData('reports');
        $this->assertCount(1, $reports);
        $this->assertEquals(Report::TYPE_MISSED_PICKUP, $reports->first()->report_type);
    }

    public function test_admin_can_search_reports(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create(['name' => 'John Doe']);
        $resident->assignRole('resident');

        Report::factory()->create([
            'resident_id' => $resident->id,
            'reference_number' => 'REP-20251108-0001',
            'location' => 'Main Street'
        ]);
        Report::factory()->create([
            'resident_id' => $resident->id,
            'reference_number' => 'REP-20251108-0002',
            'location' => 'Oak Avenue'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'search' => 'Main'
        ]));

        $response->assertOk();
        $reports = $response->viewData('reports');
        $this->assertCount(1, $reports);
        $this->assertStringContainsString('Main', $reports->first()->location);
    }

    // Test status updates with history recording
    public function test_admin_can_update_report_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $report = Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.reports.update-status', $report), [
            'status' => Report::STATUS_IN_PROGRESS,
            'note' => 'Crew assigned to investigate'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals(Report::STATUS_IN_PROGRESS, $report->status);
    }

    public function test_status_update_creates_history_record(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $report = Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING
        ]);

        $this->actingAs($admin)->patch(route('admin.reports.update-status', $report), [
            'status' => Report::STATUS_IN_PROGRESS,
            'note' => 'Investigation started'
        ]);

        $this->assertDatabaseHas('report_status_history', [
            'report_id' => $report->id,
            'old_status' => Report::STATUS_PENDING,
            'new_status' => Report::STATUS_IN_PROGRESS,
            'changed_by' => $admin->id,
            'note' => 'Investigation started'
        ]);
    }

    public function test_status_update_sets_resolved_at_timestamp(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $report = Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_IN_PROGRESS,
            'resolved_at' => null
        ]);

        $this->actingAs($admin)->patch(route('admin.reports.update-status', $report), [
            'status' => Report::STATUS_RESOLVED,
            'note' => 'Issue resolved'
        ]);

        $report->refresh();
        $this->assertNotNull($report->resolved_at);
    }

    // Test response addition
    public function test_admin_can_add_response_to_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($admin)->post(route('admin.reports.add-response', $report), [
            'response' => 'We have dispatched a crew to investigate this issue.'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('report_responses', [
            'report_id' => $report->id,
            'admin_id' => $admin->id,
            'response' => 'We have dispatched a crew to investigate this issue.'
        ]);
    }

    public function test_admin_can_add_multiple_responses(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $this->actingAs($admin)->post(route('admin.reports.add-response', $report), [
            'response' => 'First response'
        ]);

        $this->actingAs($admin)->post(route('admin.reports.add-response', $report), [
            'response' => 'Second response'
        ]);

        $report->refresh();
        $this->assertCount(2, $report->responses);
    }

    // Test report assignment to route/crew
    public function test_admin_can_assign_report_to_route(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::factory()->create();
        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($admin)->patch(route('admin.reports.assign', $report), [
            'route_id' => $route->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals($route->id, $report->route_id);
    }

    public function test_admin_can_assign_report_to_crew_member(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($admin)->patch(route('admin.reports.assign', $report), [
            'assigned_to' => $crew->id
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals($crew->id, $report->assigned_to);
    }

    public function test_admin_can_assign_report_to_both_route_and_crew(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $response = $this->actingAs($admin)->patch(route('admin.reports.assign', $report), [
            'route_id' => $route->id,
            'assigned_to' => $crew->id
        ]);

        $response->assertRedirect();

        $report->refresh();
        $this->assertEquals($route->id, $report->route_id);
        $this->assertEquals($crew->id, $report->assigned_to);
    }

    public function test_admin_can_unassign_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $route = Route::factory()->create();
        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $report = Report::factory()->create([
            'resident_id' => $resident->id,
            'route_id' => $route->id,
            'assigned_to' => $crew->id
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.reports.unassign', $report));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertNull($report->route_id);
        $this->assertNull($report->assigned_to);
    }

    // Test analytics calculations
    public function test_analytics_dashboard_displays_metrics(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        Report::factory()->count(5)->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING
        ]);
        Report::factory()->count(3)->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_RESOLVED,
            'resolved_at' => now()
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.reports.index'));

        $response->assertOk();
        $response->assertViewHas('metrics');
        
        $metrics = $response->viewData('metrics');
        $this->assertEquals(8, $metrics['total_reports']);
        $this->assertEquals(5, $metrics['pending_reports']);
    }

    public function test_location_analysis_groups_reports_by_location(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        Report::factory()->count(3)->create([
            'resident_id' => $resident->id,
            'location' => 'Main Street'
        ]);
        Report::factory()->count(2)->create([
            'resident_id' => $resident->id,
            'location' => 'Oak Avenue'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.reports.location'));

        $response->assertOk();
        $response->assertViewHas('locationData');
        
        $locationData = $response->viewData('locationData');
        $this->assertCount(2, $locationData);
    }

    public function test_type_analysis_shows_distribution(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        Report::factory()->count(4)->create([
            'resident_id' => $resident->id,
            'report_type' => Report::TYPE_MISSED_PICKUP
        ]);
        Report::factory()->count(2)->create([
            'resident_id' => $resident->id,
            'report_type' => Report::TYPE_ILLEGAL_DUMPING
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.reports.type'));

        $response->assertOk();
        $response->assertViewHas('typeData');
        
        $typeData = $response->viewData('typeData');
        $this->assertCount(2, $typeData);
    }

    public function test_resolution_time_calculation(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $report = Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_RESOLVED,
            'created_at' => now()->subHours(24),
            'resolved_at' => now()
        ]);

        $resolutionTime = $report->getResolutionTime();
        $this->assertEquals(24, $resolutionTime);
    }

    public function test_overdue_reports_identification(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create old pending report (overdue)
        Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING,
            'created_at' => now()->subHours(72)
        ]);

        // Create recent pending report (not overdue)
        Report::factory()->create([
            'resident_id' => $resident->id,
            'status' => Report::STATUS_PENDING,
            'created_at' => now()->subHours(12)
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.reports.index'));

        $response->assertOk();
        $response->assertViewHas('overdueReports');
        
        $overdueReports = $response->viewData('overdueReports');
        $this->assertCount(1, $overdueReports);
    }

    // Authorization tests
    public function test_only_residents_can_submit_reports(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $reportData = [
            'report_type' => Report::TYPE_MISSED_PICKUP,
            'location' => 'Test Location',
            'description' => 'Test description'
        ];

        // Admin cannot submit
        $response = $this->actingAs($admin)->post(route('resident.reports.store'), $reportData);
        $response->assertRedirect();

        // Crew cannot submit
        $response = $this->actingAs($crew)->post(route('resident.reports.store'), $reportData);
        $response->assertRedirect();
    }

    public function test_only_administrators_can_update_status(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $updateData = [
            'status' => Report::STATUS_IN_PROGRESS,
            'note' => 'Test note'
        ];

        // Resident cannot update
        $response = $this->actingAs($resident)->patch(route('admin.reports.update-status', $report), $updateData);
        $response->assertRedirect();

        // Crew cannot update
        $response = $this->actingAs($crew)->patch(route('admin.reports.update-status', $report), $updateData);
        $response->assertRedirect();

        $report->refresh();
        $this->assertEquals(Report::STATUS_PENDING, $report->status);
    }

    public function test_only_administrators_can_add_responses(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $responseData = ['response' => 'Test response'];

        // Resident cannot add response
        $response = $this->actingAs($resident)->post(route('admin.reports.add-response', $report), $responseData);
        $response->assertRedirect();

        // Crew cannot add response
        $response = $this->actingAs($crew)->post(route('admin.reports.add-response', $report), $responseData);
        $response->assertRedirect();

        $this->assertCount(0, $report->responses);
    }

    public function test_only_administrators_can_assign_reports(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $route = Route::factory()->create();
        $report = Report::factory()->create(['resident_id' => $resident->id]);

        $assignData = ['route_id' => $route->id];

        // Resident cannot assign
        $response = $this->actingAs($resident)->patch(route('admin.reports.assign', $report), $assignData);
        $response->assertRedirect();

        // Crew cannot assign
        $response = $this->actingAs($crew)->patch(route('admin.reports.assign', $report), $assignData);
        $response->assertRedirect();

        $report->refresh();
        $this->assertNull($report->route_id);
    }

    public function test_only_administrators_can_view_analytics(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Resident cannot view analytics
        $response = $this->actingAs($resident)->get(route('admin.analytics.reports.index'));
        $response->assertRedirect();

        // Crew cannot view analytics
        $response = $this->actingAs($crew)->get(route('admin.analytics.reports.index'));
        $response->assertRedirect();
    }

    public function test_unauthenticated_users_cannot_access_report_features(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');
        
        $report = Report::factory()->create(['resident_id' => $resident->id]);

        // Test resident routes
        $this->get(route('resident.reports'))->assertRedirect(route('login'));
        $this->get(route('resident.reports.create'))->assertRedirect(route('login'));
        $this->post(route('resident.reports.store'), [])->assertRedirect(route('login'));
        $this->get(route('resident.reports.show', $report))->assertRedirect(route('login'));

        // Test admin routes
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
        $this->get(route('admin.reports.show', $report))->assertRedirect(route('login'));
        $this->patch(route('admin.reports.update-status', $report), [])->assertRedirect(route('login'));
        $this->post(route('admin.reports.add-response', $report), [])->assertRedirect(route('login'));
        $this->patch(route('admin.reports.assign', $report), [])->assertRedirect(route('login'));
        $this->get(route('admin.analytics.reports.index'))->assertRedirect(route('login'));
    }
}
