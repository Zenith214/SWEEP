<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ScheduledReport;
use App\Models\GeneratedReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScheduledReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'administrator']);
        Role::create(['name' => 'collection_crew']);
        Role::create(['name' => 'resident']);
    }

    public function test_administrator_can_view_scheduled_reports_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('admin.scheduled-reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('scheduled-reports.index');
    }

    public function test_administrator_can_create_scheduled_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->get(route('admin.scheduled-reports.create'));

        $response->assertStatus(200);
        $response->assertViewIs('scheduled-reports.create');
    }

    public function test_administrator_can_store_scheduled_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $data = [
            'name' => 'Weekly Collection Report',
            'frequency' => 'weekly',
            'metrics' => ['collection_status', 'collection_trends'],
            'format' => 'pdf',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('admin.scheduled-reports.store'), $data);

        $response->assertRedirect(route('admin.scheduled-reports.index'));
        $this->assertDatabaseHas('scheduled_reports', [
            'name' => 'Weekly Collection Report',
            'frequency' => 'weekly',
            'format' => 'pdf',
            'is_active' => true,
        ]);
    }

    public function test_scheduled_report_requires_at_least_one_metric(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $data = [
            'name' => 'Test Report',
            'frequency' => 'daily',
            'metrics' => [],
            'format' => 'csv',
        ];

        $response = $this->actingAs($admin)->post(route('admin.scheduled-reports.store'), $data);

        $response->assertSessionHasErrors('metrics');
    }

    public function test_administrator_can_toggle_scheduled_report_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $report = ScheduledReport::create([
            'user_id' => $admin->id,
            'name' => 'Test Report',
            'frequency' => 'daily',
            'metrics' => ['collection_status'],
            'format' => 'pdf',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.scheduled-reports.toggle', $report));

        $response->assertRedirect();
        $this->assertDatabaseHas('scheduled_reports', [
            'id' => $report->id,
            'is_active' => false,
        ]);
    }

    public function test_administrator_can_view_scheduled_report_details(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $report = ScheduledReport::create([
            'user_id' => $admin->id,
            'name' => 'Test Report',
            'frequency' => 'monthly',
            'metrics' => ['collection_status', 'recycling_metrics'],
            'format' => 'pdf',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.scheduled-reports.show', $report));

        $response->assertStatus(200);
        $response->assertViewIs('scheduled-reports.show');
        $response->assertSee('Test Report');
    }

    public function test_administrator_can_update_scheduled_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $report = ScheduledReport::create([
            'user_id' => $admin->id,
            'name' => 'Original Name',
            'frequency' => 'daily',
            'metrics' => ['collection_status'],
            'format' => 'pdf',
            'is_active' => true,
        ]);

        $data = [
            'name' => 'Updated Name',
            'frequency' => 'weekly',
            'metrics' => ['collection_status', 'fleet_utilization'],
            'format' => 'csv',
            'is_active' => false,
        ];

        $response = $this->actingAs($admin)->patch(route('admin.scheduled-reports.update', $report), $data);

        $response->assertRedirect(route('admin.scheduled-reports.index'));
        $this->assertDatabaseHas('scheduled_reports', [
            'id' => $report->id,
            'name' => 'Updated Name',
            'frequency' => 'weekly',
            'format' => 'csv',
            'is_active' => false,
        ]);
    }

    public function test_administrator_can_delete_scheduled_report(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $report = ScheduledReport::create([
            'user_id' => $admin->id,
            'name' => 'Test Report',
            'frequency' => 'daily',
            'metrics' => ['collection_status'],
            'format' => 'pdf',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.scheduled-reports.destroy', $report));

        $response->assertRedirect(route('admin.scheduled-reports.index'));
        $this->assertDatabaseMissing('scheduled_reports', [
            'id' => $report->id,
        ]);
    }

    public function test_non_administrator_cannot_access_scheduled_reports(): void
    {
        $user = User::factory()->create();
        $user->assignRole('resident');

        $response = $this->actingAs($user)->get(route('admin.scheduled-reports.index'));

        $response->assertStatus(403);
    }

    public function test_scheduled_report_calculates_next_generation_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $report = ScheduledReport::create([
            'user_id' => $admin->id,
            'name' => 'Test Report',
            'frequency' => 'daily',
            'metrics' => ['collection_status'],
            'format' => 'pdf',
            'is_active' => true,
        ]);

        $report->updateNextGenerationDate();

        $this->assertNotNull($report->next_generation_at);
        $this->assertTrue($report->next_generation_at->isFuture());
    }
}
