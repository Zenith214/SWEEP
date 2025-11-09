<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportPhoto;
use App\Models\ReportResponse;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Models\Route;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample resident users if they don't exist
        $residents = $this->createResidents();
        
        // Get administrators and collection crew for assignments
        $admins = User::role('administrator')->get();
        $crews = User::role('collection_crew')->get();
        
        // Get routes for assignments
        $routes = Route::all();
        
        if ($admins->isEmpty()) {
            $this->command->warn('No administrators found. Some report features will be limited.');
        }
        
        if ($routes->isEmpty()) {
            $this->command->warn('No routes found. Reports will not be assigned to routes.');
        }

        // Create sample reports with various statuses and types
        $this->createSampleReports($residents, $admins, $crews, $routes);
        
        $this->command->info('Sample reports created successfully!');
    }

    /**
     * Create sample resident users.
     */
    private function createResidents(): array
    {
        $residentData = [
            ['name' => 'John Doe', 'email' => 'john.doe@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com'],
            ['name' => 'Michael Johnson', 'email' => 'michael.johnson@example.com'],
            ['name' => 'Sarah Williams', 'email' => 'sarah.williams@example.com'],
        ];

        $residents = [];
        foreach ($residentData as $data) {
            $resident = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$resident->hasRole('resident')) {
                $resident->assignRole('resident');
            }
            
            $residents[] = $resident;
        }

        return $residents;
    }

    /**
     * Create sample reports with various scenarios.
     */
    private function createSampleReports($residents, $admins, $crews, $routes): void
    {
        $reportScenarios = [
            // Scenario 1: Recent pending report
            [
                'resident' => $residents[0],
                'type' => Report::TYPE_MISSED_PICKUP,
                'location' => '123 Main Street, Zone A',
                'description' => 'My scheduled pickup on Monday was missed. The truck did not come to my street.',
                'status' => Report::STATUS_PENDING,
                'days_ago' => 1,
                'has_photos' => true,
                'photo_count' => 2,
            ],
            
            // Scenario 2: In-progress report with response
            [
                'resident' => $residents[1],
                'type' => Report::TYPE_UNCOLLECTED_WASTE,
                'location' => '456 Oak Avenue, Zone B',
                'description' => 'Several bags of waste were left behind after collection. The crew only took half of the waste.',
                'status' => Report::STATUS_IN_PROGRESS,
                'days_ago' => 3,
                'has_photos' => true,
                'photo_count' => 3,
                'has_response' => true,
                'assigned_route' => true,
                'assigned_crew' => true,
            ],
            
            // Scenario 3: Resolved report with full history
            [
                'resident' => $residents[0],
                'type' => Report::TYPE_ILLEGAL_DUMPING,
                'location' => '789 Pine Road, Zone A',
                'description' => 'Someone dumped construction debris on the corner of Pine Road and 5th Street.',
                'status' => Report::STATUS_RESOLVED,
                'days_ago' => 7,
                'has_photos' => true,
                'photo_count' => 3,
                'has_response' => true,
                'assigned_route' => true,
                'status_progression' => true,
            ],
            
            // Scenario 4: Closed report
            [
                'resident' => $residents[2],
                'type' => Report::TYPE_MISSED_PICKUP,
                'location' => '321 Elm Street, Zone C',
                'description' => 'Missed pickup on Thursday. No truck came by.',
                'status' => Report::STATUS_CLOSED,
                'days_ago' => 14,
                'has_photos' => false,
                'has_response' => true,
                'status_progression' => true,
            ],
            
            // Scenario 5: Another pending report (different location)
            [
                'resident' => $residents[3],
                'type' => Report::TYPE_UNCOLLECTED_WASTE,
                'location' => '555 Maple Drive, Zone D',
                'description' => 'Recycling bins were not emptied during last collection.',
                'status' => Report::STATUS_PENDING,
                'days_ago' => 0,
                'has_photos' => true,
                'photo_count' => 1,
            ],
            
            // Scenario 6: In-progress report (different type)
            [
                'resident' => $residents[1],
                'type' => Report::TYPE_OTHER,
                'location' => '888 Cedar Lane, Zone B',
                'description' => 'Damaged waste bin needs replacement. The lid is broken and cannot close properly.',
                'status' => Report::STATUS_IN_PROGRESS,
                'days_ago' => 5,
                'has_photos' => true,
                'photo_count' => 2,
                'has_response' => true,
            ],
            
            // Scenario 7: Older resolved report
            [
                'resident' => $residents[2],
                'type' => Report::TYPE_ILLEGAL_DUMPING,
                'location' => '999 Birch Court, Zone C',
                'description' => 'Large furniture items dumped near the park entrance.',
                'status' => Report::STATUS_RESOLVED,
                'days_ago' => 21,
                'has_photos' => true,
                'photo_count' => 2,
                'has_response' => true,
                'assigned_route' => true,
                'status_progression' => true,
            ],
            
            // Scenario 8: Recent report with assignment
            [
                'resident' => $residents[3],
                'type' => Report::TYPE_MISSED_PICKUP,
                'location' => '147 Willow Way, Zone D',
                'description' => 'Scheduled collection did not occur. Bins are still full.',
                'status' => Report::STATUS_IN_PROGRESS,
                'days_ago' => 2,
                'has_photos' => true,
                'photo_count' => 1,
                'assigned_route' => true,
                'assigned_crew' => true,
            ],
        ];

        foreach ($reportScenarios as $scenario) {
            $this->createReport($scenario, $admins, $crews, $routes);
        }
    }

    /**
     * Create a single report with all related data.
     */
    private function createReport(array $scenario, $admins, $crews, $routes): void
    {
        $createdAt = Carbon::now()->subDays($scenario['days_ago']);
        
        // Generate reference number
        $referenceNumber = $this->generateReferenceNumber($createdAt);
        
        // Determine route and crew assignment
        $routeId = null;
        $assignedTo = null;
        
        if (!empty($scenario['assigned_route']) && $routes->isNotEmpty()) {
            $routeId = $routes->random()->id;
        }
        
        if (!empty($scenario['assigned_crew']) && $crews->isNotEmpty()) {
            $assignedTo = $crews->random()->id;
        }
        
        // Determine resolved_at timestamp
        $resolvedAt = null;
        if (in_array($scenario['status'], [Report::STATUS_RESOLVED, Report::STATUS_CLOSED])) {
            $resolvedAt = $createdAt->copy()->addHours(rand(24, 72));
        }
        
        // Create the report
        $report = Report::create([
            'reference_number' => $referenceNumber,
            'resident_id' => $scenario['resident']->id,
            'report_type' => $scenario['type'],
            'location' => $scenario['location'],
            'description' => $scenario['description'],
            'status' => $scenario['status'],
            'route_id' => $routeId,
            'assigned_to' => $assignedTo,
            'resolved_at' => $resolvedAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        
        // Create photos if specified
        if (!empty($scenario['has_photos'])) {
            $photoCount = $scenario['photo_count'] ?? 1;
            $this->createPhotos($report, $photoCount, $createdAt);
        }
        
        // Create status history
        $this->createStatusHistory($report, $scenario, $admins, $createdAt);
        
        // Create responses if specified
        if (!empty($scenario['has_response']) && $admins->isNotEmpty()) {
            $this->createResponses($report, $admins, $createdAt);
        }
    }

    /**
     * Generate a reference number for a specific date.
     */
    private function generateReferenceNumber(Carbon $date): string
    {
        $dateStr = $date->format('Ymd');
        $prefix = "REP-{$dateStr}-";
        
        // Get the last report created on this date
        $lastReport = Report::where('reference_number', 'like', $prefix . '%')
            ->orderBy('reference_number', 'desc')
            ->first();
        
        if ($lastReport) {
            $lastSequence = (int) substr($lastReport->reference_number, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        return $prefix . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create sample photos for a report.
     */
    private function createPhotos(Report $report, int $count, Carbon $baseDate): void
    {
        $samplePhotos = [
            ['name' => 'waste_pile_1.jpg', 'size' => 2458624],
            ['name' => 'missed_pickup_1.jpg', 'size' => 1856432],
            ['name' => 'illegal_dump_1.jpg', 'size' => 3245678],
            ['name' => 'uncollected_waste_1.jpg', 'size' => 2134567],
            ['name' => 'damaged_bin_1.jpg', 'size' => 1567890],
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $photo = $samplePhotos[$i % count($samplePhotos)];
            $photoNumber = $i + 1;
            
            ReportPhoto::create([
                'report_id' => $report->id,
                'file_path' => "reports/{$report->id}/photo_{$photoNumber}.jpg",
                'file_name' => $photo['name'],
                'file_size' => $photo['size'],
                'uploaded_at' => $baseDate->copy()->addMinutes($i * 2),
                'created_at' => $baseDate->copy()->addMinutes($i * 2),
                'updated_at' => $baseDate->copy()->addMinutes($i * 2),
            ]);
        }
    }

    /**
     * Create status history for a report.
     */
    private function createStatusHistory(Report $report, array $scenario, $admins, Carbon $baseDate): void
    {
        if ($admins->isEmpty()) {
            return;
        }
        
        $admin = $admins->random();
        
        // Initial status (pending)
        ReportStatusHistory::create([
            'report_id' => $report->id,
            'old_status' => null,
            'new_status' => Report::STATUS_PENDING,
            'changed_by' => $scenario['resident']->id,
            'note' => 'Report submitted by resident',
            'created_at' => $baseDate,
        ]);
        
        // If status progression is enabled, create full history
        if (!empty($scenario['status_progression'])) {
            if (in_array($report->status, [Report::STATUS_IN_PROGRESS, Report::STATUS_RESOLVED, Report::STATUS_CLOSED])) {
                ReportStatusHistory::create([
                    'report_id' => $report->id,
                    'old_status' => Report::STATUS_PENDING,
                    'new_status' => Report::STATUS_IN_PROGRESS,
                    'changed_by' => $admin->id,
                    'note' => 'Report assigned to collection crew for investigation',
                    'created_at' => $baseDate->copy()->addHours(rand(2, 12)),
                ]);
            }
            
            if (in_array($report->status, [Report::STATUS_RESOLVED, Report::STATUS_CLOSED])) {
                ReportStatusHistory::create([
                    'report_id' => $report->id,
                    'old_status' => Report::STATUS_IN_PROGRESS,
                    'new_status' => Report::STATUS_RESOLVED,
                    'changed_by' => $admin->id,
                    'note' => 'Issue has been resolved. Collection completed.',
                    'created_at' => $baseDate->copy()->addHours(rand(24, 48)),
                ]);
            }
            
            if ($report->status === Report::STATUS_CLOSED) {
                ReportStatusHistory::create([
                    'report_id' => $report->id,
                    'old_status' => Report::STATUS_RESOLVED,
                    'new_status' => Report::STATUS_CLOSED,
                    'changed_by' => $admin->id,
                    'note' => 'Report closed after verification',
                    'created_at' => $baseDate->copy()->addHours(rand(72, 96)),
                ]);
            }
        } elseif ($report->status !== Report::STATUS_PENDING) {
            // Single status change for non-pending reports without full progression
            ReportStatusHistory::create([
                'report_id' => $report->id,
                'old_status' => Report::STATUS_PENDING,
                'new_status' => $report->status,
                'changed_by' => $admin->id,
                'note' => 'Status updated by administrator',
                'created_at' => $baseDate->copy()->addHours(rand(2, 12)),
            ]);
        }
    }

    /**
     * Create responses for a report.
     */
    private function createResponses(Report $report, $admins, Carbon $baseDate): void
    {
        $responses = [
            'Thank you for reporting this issue. We are investigating and will update you soon.',
            'Our collection crew has been notified and will address this issue on their next route.',
            'We have assigned this report to the appropriate team for resolution.',
            'The issue has been resolved. Thank you for your patience.',
            'We apologize for the inconvenience. This has been escalated to our operations team.',
        ];
        
        $responseCount = rand(1, 2);
        $admin = $admins->random();
        
        for ($i = 0; $i < $responseCount; $i++) {
            ReportResponse::create([
                'report_id' => $report->id,
                'admin_id' => $admin->id,
                'response' => $responses[$i % count($responses)],
                'created_at' => $baseDate->copy()->addHours(rand(6, 24) * ($i + 1)),
                'updated_at' => $baseDate->copy()->addHours(rand(6, 24) * ($i + 1)),
            ]);
        }
    }
}
