<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CollectionLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get past assignments (from the last 7 days)
        $pastAssignments = Assignment::where('status', Assignment::STATUS_ACTIVE)
            ->where('assignment_date', '<', Carbon::today())
            ->where('assignment_date', '>=', Carbon::today()->subDays(7))
            ->with(['user', 'route'])
            ->get();

        if ($pastAssignments->isEmpty()) {
            $this->command->warn('No past assignments found. Run AssignmentSeeder first.');
            return;
        }

        $this->command->info('Creating collection logs for ' . $pastAssignments->count() . ' past assignments...');

        // Create logs for most past assignments (80-90% completion rate)
        $logsToCreate = (int) ($pastAssignments->count() * 0.85);
        $selectedAssignments = $pastAssignments->random(min($logsToCreate, $pastAssignments->count()));

        foreach ($selectedAssignments as $assignment) {
            $this->createCollectionLog($assignment);
        }

        $this->command->info('Collection logs created successfully!');
    }

    /**
     * Create a collection log for an assignment.
     */
    private function createCollectionLog(Assignment $assignment): void
    {
        // Determine status with weighted distribution
        $statusDistribution = [
            CollectionLog::STATUS_COMPLETED => 70,      // 70% completed
            CollectionLog::STATUS_INCOMPLETE => 15,     // 15% incomplete
            CollectionLog::STATUS_ISSUE_REPORTED => 15, // 15% issue reported
        ];

        $status = $this->getWeightedRandomStatus($statusDistribution);

        // Base log data
        $logData = [
            'assignment_id' => $assignment->id,
            'status' => $status,
            'created_by' => $assignment->user_id,
            'crew_notes' => $this->getRandomCrewNote($status),
        ];

        // Add status-specific data
        switch ($status) {
            case CollectionLog::STATUS_COMPLETED:
                $logData = array_merge($logData, $this->getCompletedLogData($assignment));
                break;

            case CollectionLog::STATUS_INCOMPLETE:
                $logData = array_merge($logData, $this->getIncompleteLogData($assignment));
                break;

            case CollectionLog::STATUS_ISSUE_REPORTED:
                $logData = array_merge($logData, $this->getIssueLogData($assignment));
                break;
        }

        // Create the log with a timestamp from the assignment date
        $log = CollectionLog::create($logData);

        // Backdate the created_at to match the assignment date
        $createdAt = Carbon::parse($assignment->assignment_date)
            ->setTime(rand(8, 16), rand(0, 59), rand(0, 59));
        
        $log->created_at = $createdAt;
        $log->updated_at = $createdAt;
        
        // Randomly add edited_at for some logs (20% chance)
        if (rand(1, 100) <= 20) {
            $log->edited_at = $createdAt->copy()->addMinutes(rand(5, 90));
            $log->updated_at = $log->edited_at;
        }
        
        $log->save();
    }

    /**
     * Get data for a completed collection log.
     */
    private function getCompletedLogData(Assignment $assignment): array
    {
        // Completion time should be during work hours
        $completionTime = Carbon::parse($assignment->assignment_date)
            ->setTime(rand(10, 15), rand(0, 59));

        return [
            'completion_time' => $completionTime,
            'completion_percentage' => 100,
            'issue_type' => null,
            'issue_description' => null,
        ];
    }

    /**
     * Get data for an incomplete collection log.
     */
    private function getIncompleteLogData(Assignment $assignment): array
    {
        $completionPercentage = rand(30, 90);
        
        // Sometimes include completion time for partial completions
        $completionTime = rand(1, 100) <= 60 
            ? Carbon::parse($assignment->assignment_date)->setTime(rand(10, 16), rand(0, 59))
            : null;

        return [
            'completion_time' => $completionTime,
            'completion_percentage' => $completionPercentage,
            'issue_type' => null,
            'issue_description' => null,
        ];
    }

    /**
     * Get data for an issue-reported collection log.
     */
    private function getIssueLogData(Assignment $assignment): array
    {
        $issueTypes = array_keys(CollectionLog::ISSUE_TYPES);
        $issueType = $issueTypes[array_rand($issueTypes)];

        // Sometimes issues are reported with partial completion
        $hasPartialCompletion = rand(1, 100) <= 40;
        
        $completionPercentage = $hasPartialCompletion ? rand(20, 80) : null;
        $completionTime = $hasPartialCompletion 
            ? Carbon::parse($assignment->assignment_date)->setTime(rand(10, 16), rand(0, 59))
            : null;

        return [
            'completion_time' => $completionTime,
            'completion_percentage' => $completionPercentage,
            'issue_type' => $issueType,
            'issue_description' => $this->getIssueDescription($issueType),
        ];
    }

    /**
     * Get a weighted random status.
     */
    private function getWeightedRandomStatus(array $weights): string
    {
        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $status => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return CollectionLog::STATUS_COMPLETED;
    }

    /**
     * Get a random crew note based on status.
     */
    private function getRandomCrewNote(string $status): ?string
    {
        // 40% chance of no notes
        if (rand(1, 100) <= 40) {
            return null;
        }

        $notesByStatus = [
            CollectionLog::STATUS_COMPLETED => [
                'All bins collected without issues.',
                'Route completed on schedule.',
                'Some residents had extra bags today.',
                'Smooth collection, no problems.',
                'Completed all pickups successfully.',
                'Good weather, efficient collection.',
            ],
            CollectionLog::STATUS_INCOMPLETE => [
                'Had to stop early due to truck capacity.',
                'Ran out of time, will need to return.',
                'Some areas were inaccessible.',
                'Partial completion due to circumstances.',
                'Will need follow-up collection.',
            ],
            CollectionLog::STATUS_ISSUE_REPORTED => [
                'Issue prevented full completion.',
                'Problem encountered during collection.',
                'Unable to complete due to issue.',
                'Reported issue to supervisor.',
                'Issue requires attention.',
            ],
        ];

        $notes = $notesByStatus[$status] ?? ['Collection logged.'];
        return $notes[array_rand($notes)];
    }

    /**
     * Get an issue description based on issue type.
     */
    private function getIssueDescription(string $issueType): string
    {
        $descriptions = [
            'blocked_road' => [
                'Main road blocked by construction work. Unable to access several streets.',
                'Parked vehicles blocking access to collection points.',
                'Road closure due to utility work prevented access to half the route.',
                'Street festival blocked main collection route.',
            ],
            'truck_problem' => [
                'Hydraulic system malfunction during collection.',
                'Truck engine overheating, had to stop collection.',
                'Compactor not working properly, limited capacity.',
                'Brake warning light came on, returned to depot.',
            ],
            'weather' => [
                'Heavy rain made some areas unsafe to access.',
                'Flooding on low-lying streets prevented collection.',
                'Strong winds made collection dangerous.',
                'Ice on roads made driving hazardous.',
            ],
            'no_access' => [
                'Gate locked, unable to access private road section.',
                'New construction blocking usual route.',
                'Fallen tree blocking access to several streets.',
                'Bridge closed for maintenance, no alternate route.',
            ],
            'safety_concern' => [
                'Aggressive dog loose in collection area.',
                'Unstable ground conditions near collection point.',
                'Hazardous materials improperly disposed, area cordoned off.',
                'Suspicious package reported, area evacuated.',
            ],
            'other' => [
                'Unexpected delay due to traffic accident.',
                'Equipment malfunction not related to truck.',
                'Crew member injury, had to stop collection.',
                'Communication equipment failure.',
            ],
        ];

        $typeDescriptions = $descriptions[$issueType] ?? ['Issue encountered during collection.'];
        return $typeDescriptions[array_rand($typeDescriptions)];
    }
}
