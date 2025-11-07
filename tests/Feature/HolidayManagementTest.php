<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    // ========================================
    // Holiday CRUD Operations Tests (23.1)
    // ========================================

    public function test_administrator_can_create_holiday(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.holidays.store'), [
            'name' => 'New Year\'s Day',
            'date' => '2026-01-01',
            'is_collection_skipped' => true,
            'reschedule_date' => null,
        ]);

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('holidays', [
            'name' => 'New Year\'s Day',
            'date' => '2026-01-01 00:00:00',
            'is_collection_skipped' => 1,
        ]);
    }

    public function test_administrator_can_create_holiday_with_reschedule_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.holidays.store'), [
            'name' => 'Christmas',
            'date' => '2025-12-25',
            'is_collection_skipped' => false,
            'reschedule_date' => '2025-12-26',
        ]);

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('holidays', [
            'name' => 'Christmas',
            'date' => '2025-12-25 00:00:00',
            'is_collection_skipped' => 0,
            'reschedule_date' => '2025-12-26 00:00:00',
        ]);
    }

    public function test_duplicate_date_validation_prevents_duplicate_holidays(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Create existing holiday
        Holiday::create([
            'name' => 'Existing Holiday',
            'date' => '2026-07-04',
            'is_collection_skipped' => true,
        ]);

        // Try to create holiday with duplicate date
        $response = $this->actingAs($admin)->post(route('admin.holidays.store'), [
            'name' => 'Another Holiday',
            'date' => '2026-07-04',
            'is_collection_skipped' => true,
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertStringContainsString('already exists', session('errors')->first('date'));
    }

    public function test_reschedule_date_must_be_different_from_holiday_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.holidays.store'), [
            'name' => 'Invalid Holiday',
            'date' => '2026-05-01',
            'is_collection_skipped' => false,
            'reschedule_date' => '2026-05-01', // Same as holiday date
        ]);

        $response->assertSessionHasErrors('reschedule_date');
        $this->assertStringContainsString('different', session('errors')->first('reschedule_date'));
    }

    public function test_administrator_can_view_holiday_list(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        Holiday::create([
            'name' => 'Holiday 1',
            'date' => '2026-01-01',
            'is_collection_skipped' => true,
        ]);

        Holiday::create([
            'name' => 'Holiday 2',
            'date' => '2026-07-04',
            'is_collection_skipped' => false,
            'reschedule_date' => '2026-07-05',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.holidays.index'));

        $response->assertOk();
        $response->assertSee('Holiday 1');
        $response->assertSee('Holiday 2');
    }

    public function test_administrator_can_view_holiday_details(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $holiday = Holiday::create([
            'name' => 'Test Holiday',
            'date' => '2026-03-15',
            'is_collection_skipped' => false,
            'reschedule_date' => '2026-03-16',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.holidays.show', $holiday));

        $response->assertOk();
        $response->assertSee('Test Holiday');
        $response->assertSee('2026-03-15');
        $response->assertSee('2026-03-16');
    }

    public function test_administrator_can_edit_holiday(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $holiday = Holiday::create([
            'name' => 'Old Name',
            'date' => '2026-06-01',
            'is_collection_skipped' => true,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.holidays.update', $holiday), [
            'name' => 'New Name',
            'date' => '2026-06-02',
            'is_collection_skipped' => false,
            'reschedule_date' => '2026-06-03',
        ]);

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');

        $holiday->refresh();
        $this->assertEquals('New Name', $holiday->name);
        $this->assertEquals('2026-06-02', $holiday->date->format('Y-m-d'));
        $this->assertFalse($holiday->is_collection_skipped);
        $this->assertEquals('2026-06-03', $holiday->reschedule_date->format('Y-m-d'));
    }

    public function test_holiday_date_uniqueness_validation_on_update(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $holiday1 = Holiday::create([
            'name' => 'Holiday 1',
            'date' => '2026-08-01',
            'is_collection_skipped' => true,
        ]);

        $holiday2 = Holiday::create([
            'name' => 'Holiday 2',
            'date' => '2026-08-02',
            'is_collection_skipped' => true,
        ]);

        // Try to update holiday2 with holiday1's date
        $response = $this->actingAs($admin)->patch(route('admin.holidays.update', $holiday2), [
            'name' => 'Holiday 2',
            'date' => '2026-08-01', // Duplicate date
            'is_collection_skipped' => true,
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_administrator_can_update_holiday_to_same_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $holiday = Holiday::create([
            'name' => 'Original Name',
            'date' => '2026-09-01',
            'is_collection_skipped' => true,
        ]);

        // Update name but keep same date
        $response = $this->actingAs($admin)->patch(route('admin.holidays.update', $holiday), [
            'name' => 'Updated Name',
            'date' => '2026-09-01', // Same date
            'is_collection_skipped' => true,
        ]);

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');

        $holiday->refresh();
        $this->assertEquals('Updated Name', $holiday->name);
    }

    public function test_administrator_can_delete_holiday(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $holiday = Holiday::create([
            'name' => 'Deletable Holiday',
            'date' => '2026-10-01',
            'is_collection_skipped' => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.holidays.destroy', $holiday));

        $response->assertRedirect(route('admin.holidays.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('holidays', [
            'id' => $holiday->id,
        ]);
    }

    public function test_holiday_validation_requires_name(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.holidays.store'), [
            'date' => '2026-11-01',
            'is_collection_skipped' => true,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_holiday_validation_requires_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $response = $this->actingAs($admin)->post(route('admin.holidays.store'), [
            'name' => 'Test Holiday',
            'is_collection_skipped' => true,
        ]);

        $response->assertSessionHasErrors('date');
    }

    // ========================================
    // Holiday Authorization Tests
    // ========================================

    public function test_only_administrators_can_access_holiday_index(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        // Resident cannot access
        $response = $this->actingAs($resident)->get(route('admin.holidays.index'));
        $response->assertRedirect();

        // Crew cannot access
        $response = $this->actingAs($crew)->get(route('admin.holidays.index'));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_create_holidays(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $holidayData = [
            'name' => 'Test Holiday',
            'date' => '2026-12-01',
            'is_collection_skipped' => true,
        ];

        // Resident cannot create
        $response = $this->actingAs($resident)->post(route('admin.holidays.store'), $holidayData);
        $response->assertRedirect();

        // Crew cannot create
        $response = $this->actingAs($crew)->post(route('admin.holidays.store'), $holidayData);
        $response->assertRedirect();

        // Verify holiday was not created
        $this->assertDatabaseMissing('holidays', ['name' => 'Test Holiday']);
    }

    public function test_only_administrators_can_view_holiday_details(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $holiday = Holiday::create([
            'name' => 'Test Holiday',
            'date' => '2026-12-15',
            'is_collection_skipped' => true,
        ]);

        // Resident cannot view
        $response = $this->actingAs($resident)->get(route('admin.holidays.show', $holiday));
        $response->assertRedirect();

        // Crew cannot view
        $response = $this->actingAs($crew)->get(route('admin.holidays.show', $holiday));
        $response->assertRedirect();
    }

    public function test_only_administrators_can_edit_holidays(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $holiday = Holiday::create([
            'name' => 'Original Name',
            'date' => '2026-12-20',
            'is_collection_skipped' => true,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'date' => '2026-12-21',
            'is_collection_skipped' => false,
        ];

        // Resident cannot edit
        $response = $this->actingAs($resident)->patch(route('admin.holidays.update', $holiday), $updateData);
        $response->assertRedirect();

        // Crew cannot edit
        $response = $this->actingAs($crew)->patch(route('admin.holidays.update', $holiday), $updateData);
        $response->assertRedirect();

        // Verify holiday was not updated
        $holiday->refresh();
        $this->assertEquals('Original Name', $holiday->name);
    }

    public function test_only_administrators_can_delete_holidays(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        $crew = User::factory()->create();
        $crew->assignRole('collection_crew');

        $holiday1 = Holiday::create([
            'name' => 'Holiday for Resident Test',
            'date' => '2026-12-25',
            'is_collection_skipped' => true,
        ]);

        $holiday2 = Holiday::create([
            'name' => 'Holiday for Crew Test',
            'date' => '2026-12-26',
            'is_collection_skipped' => true,
        ]);

        // Resident cannot delete
        $response = $this->actingAs($resident)->delete(route('admin.holidays.destroy', $holiday1));
        $response->assertRedirect();

        // Crew cannot delete
        $response = $this->actingAs($crew)->delete(route('admin.holidays.destroy', $holiday2));
        $response->assertRedirect();

        // Verify holidays were not deleted
        $this->assertDatabaseHas('holidays', ['id' => $holiday1->id]);
        $this->assertDatabaseHas('holidays', ['id' => $holiday2->id]);
    }

    public function test_unauthenticated_users_cannot_access_holiday_management(): void
    {
        $holiday = Holiday::create([
            'name' => 'Test Holiday',
            'date' => '2026-12-31',
            'is_collection_skipped' => true,
        ]);

        // Test all holiday management endpoints
        $this->get(route('admin.holidays.index'))->assertRedirect(route('login'));
        $this->get(route('admin.holidays.create'))->assertRedirect(route('login'));
        $this->post(route('admin.holidays.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.holidays.show', $holiday))->assertRedirect(route('login'));
        $this->get(route('admin.holidays.edit', $holiday))->assertRedirect(route('login'));
        $this->patch(route('admin.holidays.update', $holiday), [])->assertRedirect(route('login'));
        $this->delete(route('admin.holidays.destroy', $holiday))->assertRedirect(route('login'));
    }


    // ========================================
    // Holiday Application Tests (23.2)
    // ========================================

    public function test_holidays_affect_calendar_display_by_skipping_collection(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule for Mondays
        $route = Route::create([
            'name' => 'Holiday Test Route',
            'zone' => 'HOL-TEST-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create a holiday on a Monday that skips collection
        $holidayDate = now()->next(Carbon::MONDAY);
        
        Holiday::create([
            'name' => 'Test Holiday',
            'date' => $holidayDate,
            'is_collection_skipped' => true,
            'reschedule_date' => null,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'HOL-TEST-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        
        // Verify the holiday date is not in the collection dates
        $collectionDates = collect($data)->pluck('start')->toArray();
        $this->assertNotContains($holidayDate->format('Y-m-d'), $collectionDates);
    }

    public function test_holidays_affect_calendar_display_by_rescheduling_collection(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule for Mondays
        $route = Route::create([
            'name' => 'Reschedule Test Route',
            'zone' => 'RESC-TEST-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create a holiday on a Monday that reschedules to Tuesday
        $holidayDate = now()->next(Carbon::MONDAY);
        $rescheduleDate = $holidayDate->copy()->addDay();
        
        Holiday::create([
            'name' => 'Rescheduled Holiday',
            'date' => $holidayDate,
            'is_collection_skipped' => false,
            'reschedule_date' => $rescheduleDate,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'RESC-TEST-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        
        // Verify the original holiday date is not in the collection dates
        $collectionDates = collect($data)->pluck('start')->toArray();
        $this->assertNotContains($holidayDate->format('Y-m-d'), $collectionDates);
        
        // Verify the rescheduled date is in the collection dates
        $this->assertContains($rescheduleDate->format('Y-m-d'), $collectionDates);
    }

    public function test_rescheduled_date_displays_with_special_styling(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule for Mondays
        $route = Route::create([
            'name' => 'Styling Test Route',
            'zone' => 'STYLE-TEST-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create a holiday with reschedule date
        $holidayDate = now()->next(Carbon::MONDAY);
        $rescheduleDate = $holidayDate->copy()->addDay();
        
        Holiday::create([
            'name' => 'Styled Holiday',
            'date' => $holidayDate,
            'is_collection_skipped' => false,
            'reschedule_date' => $rescheduleDate,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'STYLE-TEST-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        
        // Find the rescheduled event
        $rescheduledEvent = collect($data)->firstWhere('extendedProps.is_rescheduled', true);
        
        if ($rescheduledEvent) {
            // Verify it has the amber color for rescheduled holidays
            $this->assertEquals('#F59E0B', $rescheduledEvent['backgroundColor']);
            $this->assertEquals('#F59E0B', $rescheduledEvent['borderColor']);
            
            // Verify it has the original date in extended props
            $this->assertEquals($holidayDate->format('Y-m-d'), $rescheduledEvent['extendedProps']['original_date']);
            
            // Verify it's marked as rescheduled
            $this->assertTrue($rescheduledEvent['extendedProps']['is_rescheduled']);
        }
    }

    public function test_multiple_holidays_in_month_are_all_applied(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule for Mondays and Wednesdays
        $route = Route::create([
            'name' => 'Multiple Holidays Route',
            'zone' => 'MULTI-HOL-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
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

        // Create first holiday on a Monday (skipped)
        $holiday1Date = now()->next(Carbon::MONDAY);
        Holiday::create([
            'name' => 'First Holiday',
            'date' => $holiday1Date,
            'is_collection_skipped' => true,
        ]);

        // Create second holiday on a Wednesday (rescheduled)
        $holiday2Date = now()->next(Carbon::WEDNESDAY);
        $reschedule2Date = $holiday2Date->copy()->addDay();
        Holiday::create([
            'name' => 'Second Holiday',
            'date' => $holiday2Date,
            'is_collection_skipped' => false,
            'reschedule_date' => $reschedule2Date,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'MULTI-HOL-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        $collectionDates = collect($data)->pluck('start')->toArray();
        
        // Verify first holiday date is not in collection dates
        $this->assertNotContains($holiday1Date->format('Y-m-d'), $collectionDates);
        
        // Verify second holiday date is not in collection dates
        $this->assertNotContains($holiday2Date->format('Y-m-d'), $collectionDates);
        
        // Verify rescheduled date is in collection dates
        $this->assertContains($reschedule2Date->format('Y-m-d'), $collectionDates);
    }

    public function test_holiday_model_isHoliday_method_works_correctly(): void
    {
        // Create a holiday
        $holidayDate = Carbon::parse('2026-07-04');
        Holiday::create([
            'name' => 'Independence Day',
            'date' => $holidayDate,
            'is_collection_skipped' => true,
        ]);

        // Test that the date is recognized as a holiday
        $this->assertTrue(Holiday::isHoliday($holidayDate));

        // Test that a non-holiday date is not recognized
        $nonHolidayDate = Carbon::parse('2026-07-05');
        $this->assertFalse(Holiday::isHoliday($nonHolidayDate));
    }

    public function test_holiday_model_getRescheduledDate_method_returns_correct_date(): void
    {
        $holidayDate = Carbon::parse('2026-12-25');
        $rescheduleDate = Carbon::parse('2026-12-26');
        
        // Create a holiday with reschedule date
        Holiday::create([
            'name' => 'Christmas',
            'date' => $holidayDate,
            'is_collection_skipped' => false,
            'reschedule_date' => $rescheduleDate,
        ]);

        // Test that the rescheduled date is returned
        $result = Holiday::getRescheduledDate($holidayDate);
        $this->assertNotNull($result);
        $this->assertEquals($rescheduleDate->format('Y-m-d'), $result->format('Y-m-d'));
    }

    public function test_holiday_model_getRescheduledDate_returns_null_for_skipped_collection(): void
    {
        $holidayDate = Carbon::parse('2026-01-01');
        
        // Create a holiday that skips collection
        Holiday::create([
            'name' => 'New Year',
            'date' => $holidayDate,
            'is_collection_skipped' => true,
            'reschedule_date' => null,
        ]);

        // Test that null is returned for skipped collection
        $result = Holiday::getRescheduledDate($holidayDate);
        $this->assertNull($result);
    }

    public function test_holiday_model_getHolidaysInRange_returns_correct_holidays(): void
    {
        // Create holidays in different months
        Holiday::create([
            'name' => 'Holiday 1',
            'date' => '2026-06-01',
            'is_collection_skipped' => true,
        ]);

        Holiday::create([
            'name' => 'Holiday 2',
            'date' => '2026-06-15',
            'is_collection_skipped' => true,
        ]);

        Holiday::create([
            'name' => 'Holiday 3',
            'date' => '2026-07-01',
            'is_collection_skipped' => true,
        ]);

        // Get holidays in June
        $start = Carbon::parse('2026-06-01');
        $end = Carbon::parse('2026-06-30');
        
        $holidays = Holiday::getHolidaysInRange($start, $end);
        
        // Should return 2 holidays
        $this->assertCount(2, $holidays);
        $this->assertTrue($holidays->contains('name', 'Holiday 1'));
        $this->assertTrue($holidays->contains('name', 'Holiday 2'));
        $this->assertFalse($holidays->contains('name', 'Holiday 3'));
    }

    public function test_calendar_displays_holiday_indicator_for_skipped_collections(): void
    {
        $resident = User::factory()->create();
        $resident->assignRole('resident');

        // Create route with schedule
        $route = Route::create([
            'name' => 'Indicator Test Route',
            'zone' => 'IND-TEST-001',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'route_id' => $route->id,
            'collection_time' => '08:00:00',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        ScheduleDay::create([
            'schedule_id' => $schedule->id,
            'day_of_week' => 1, // Monday
        ]);

        // Create a holiday
        $holidayDate = now()->next(Carbon::MONDAY);
        Holiday::create([
            'name' => 'Indicator Holiday',
            'date' => $holidayDate,
            'is_collection_skipped' => true,
        ]);

        $response = $this->actingAs($resident)->get(route('resident.schedules.calendar.data', [
            'zone' => 'IND-TEST-001',
            'start' => now()->startOfMonth()->format('Y-m-d'),
            'end' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        
        $data = $response->json();
        
        // Verify the holiday date is excluded from collection dates
        $collectionDates = collect($data)->pluck('start')->toArray();
        $this->assertNotContains($holidayDate->format('Y-m-d'), $collectionDates);
    }
}

