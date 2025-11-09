<?php

namespace Database\Seeders;

use App\Models\RecyclingLog;
use App\Models\RecyclingLogMaterial;
use App\Models\User;
use App\Models\Route;
use App\Models\Assignment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecyclingLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get collection crew members
        $crewMembers = User::role('collection_crew')->get();
        
        if ($crewMembers->isEmpty()) {
            $this->command->warn('No collection crew members found. Please run AdminUserSeeder and AssignmentSeeder first.');
            return;
        }

        // Get active routes
        $routes = Route::where('is_active', true)->get();
        
        if ($routes->isEmpty()) {
            $this->command->warn('No active routes found. Please run RouteSeeder first.');
            return;
        }

        // Get assignments from the past 3 months
        $threeMonthsAgo = Carbon::today()->subMonths(3);
        $assignments = Assignment::where('assignment_date', '>=', $threeMonthsAgo)
            ->where('status', Assignment::STATUS_ACTIVE)
            ->get();

        // Generate at least 50 logs across 3 months
        $logsToCreate = max(50, $assignments->count());
        $createdLogs = 0;

        // Create logs for existing assignments first
        foreach ($assignments as $assignment) {
            if ($createdLogs >= $logsToCreate) {
                break;
            }

            // 80% chance to create a log for this assignment
            if (rand(1, 100) <= 80) {
                $this->createRecyclingLog(
                    $assignment->user,
                    $assignment->route,
                    $assignment,
                    $assignment->assignment_date
                );
                $createdLogs++;
            }
        }

        // Create additional logs without assignments to reach target
        while ($createdLogs < $logsToCreate) {
            $crew = $crewMembers->random();
            $route = $routes->random();
            $date = Carbon::today()->subDays(rand(0, 90)); // Random date in last 3 months

            $this->createRecyclingLog($crew, $route, null, $date);
            $createdLogs++;
        }

        $this->command->info("Created {$createdLogs} recycling logs with materials");
    }

    /**
     * Create a single recycling log with materials.
     */
    private function createRecyclingLog(User $user, Route $route, ?Assignment $assignment, Carbon $date): void
    {
        // Determine if this log should have quality issues (10-15%)
        $hasQualityIssue = rand(1, 100) <= 12;

        // Create the recycling log
        $log = RecyclingLog::create([
            'user_id' => $user->id,
            'assignment_id' => $assignment?->id,
            'route_id' => $route->id,
            'collection_date' => $date,
            'notes' => $this->getRandomNotes($hasQualityIssue),
            'quality_issue' => $hasQualityIssue,
        ]);

        // Adjust created_at to match collection date for realistic edit window testing
        // Most logs created on same day, some created later
        $createdAt = $date->copy()->addHours(rand(8, 16))->addMinutes(rand(0, 59));
        $log->created_at = $createdAt;
        $log->updated_at = $createdAt;
        $log->save();

        // Add 1-6 different material types
        $materialCount = rand(1, 6);
        $availableMaterials = RecyclingLogMaterial::getMaterialTypes();
        $selectedMaterials = array_rand(array_flip($availableMaterials), $materialCount);
        
        // Ensure it's always an array
        if (!is_array($selectedMaterials)) {
            $selectedMaterials = [$selectedMaterials];
        }

        foreach ($selectedMaterials as $materialType) {
            RecyclingLogMaterial::create([
                'recycling_log_id' => $log->id,
                'material_type' => $materialType,
                'weight' => $this->getRandomWeight($materialType),
            ]);
        }
    }

    /**
     * Get random weight for a material type (in kg).
     */
    private function getRandomWeight(string $materialType): float
    {
        // Different material types have different typical weight ranges
        $ranges = [
            RecyclingLogMaterial::TYPE_PLASTIC => [5, 50],
            RecyclingLogMaterial::TYPE_PAPER => [10, 80],
            RecyclingLogMaterial::TYPE_GLASS => [15, 100],
            RecyclingLogMaterial::TYPE_METAL => [8, 60],
            RecyclingLogMaterial::TYPE_CARDBOARD => [12, 90],
            RecyclingLogMaterial::TYPE_ORGANIC => [20, 150],
        ];

        $range = $ranges[$materialType] ?? [5, 50];
        
        // Generate random weight with 2 decimal places
        return round(rand($range[0] * 100, $range[1] * 100) / 100, 2);
    }

    /**
     * Get random notes for a recycling log.
     */
    private function getRandomNotes(bool $hasQualityIssue): ?string
    {
        if ($hasQualityIssue) {
            $qualityNotes = [
                'Contamination found - food waste mixed with recyclables',
                'Non-recyclable items included in collection',
                'Materials not properly sorted',
                'Excessive contamination - needs resident education',
                'Plastic bags mixed with paper products',
                'Wet cardboard - reduced quality',
                'Glass broken and mixed with other materials',
            ];
            return $qualityNotes[array_rand($qualityNotes)];
        }

        $regularNotes = [
            null,
            null,
            null,
            null, // More likely to have no notes
            'Good quality materials collected',
            'Large volume collection today',
            'Resident requested extra pickup',
            'Well-sorted materials',
            'New recycling bins in use',
            'Holiday collection - increased volume',
            'Commercial area - mostly cardboard',
            'Residential area - mixed materials',
        ];

        return $regularNotes[array_rand($regularNotes)];
    }
}
