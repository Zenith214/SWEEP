<?php

namespace Database\Seeders;

use App\Models\AdminNote;
use App\Models\CollectionLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all collection logs
        $collectionLogs = CollectionLog::all();

        if ($collectionLogs->isEmpty()) {
            $this->command->warn('No collection logs found. Run CollectionLogSeeder first.');
            return;
        }

        // Get administrators
        $admins = User::role('administrator')->get();

        if ($admins->isEmpty()) {
            $this->command->warn('No administrators found. Admin notes will not be created.');
            return;
        }

        $this->command->info('Creating admin notes for collection logs...');

        $totalNotes = 0;

        // Add notes to logs with issues (80% chance)
        $issueReportedLogs = $collectionLogs->where('status', CollectionLog::STATUS_ISSUE_REPORTED);
        foreach ($issueReportedLogs as $log) {
            if (rand(1, 100) <= 80) {
                $noteCount = rand(1, 3); // 1-3 notes for issue reports
                $totalNotes += $this->createNotesForLog($log, $admins, $noteCount);
            }
        }

        // Add notes to incomplete logs (40% chance)
        $incompleteLogs = $collectionLogs->where('status', CollectionLog::STATUS_INCOMPLETE);
        foreach ($incompleteLogs as $log) {
            if (rand(1, 100) <= 40) {
                $noteCount = rand(1, 2); // 1-2 notes for incomplete
                $totalNotes += $this->createNotesForLog($log, $admins, $noteCount);
            }
        }

        // Add notes to some completed logs (10% chance) - for quality checks
        $completedLogs = $collectionLogs->where('status', CollectionLog::STATUS_COMPLETED);
        foreach ($completedLogs as $log) {
            if (rand(1, 100) <= 10) {
                $noteCount = 1; // Usually just 1 note for completed
                $totalNotes += $this->createNotesForLog($log, $admins, $noteCount);
            }
        }

        $this->command->info("Created {$totalNotes} admin notes!");
    }

    /**
     * Create notes for a collection log.
     */
    private function createNotesForLog(CollectionLog $log, $admins, int $count): int
    {
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            $admin = $admins->random();
            $note = $this->getNoteForStatus($log->status, $i);
            
            // Create note with timestamp after the log was created
            $noteCreatedAt = $log->created_at->copy()->addHours(rand(2, 48));
            
            $adminNote = AdminNote::create([
                'collection_log_id' => $log->id,
                'admin_id' => $admin->id,
                'note' => $note,
            ]);

            // Backdate the created_at timestamp
            $adminNote->created_at = $noteCreatedAt;
            $adminNote->updated_at = $noteCreatedAt;
            $adminNote->save();

            $created++;
        }

        return $created;
    }

    /**
     * Get appropriate note content based on status and note number.
     */
    private function getNoteForStatus(string $status, int $noteIndex): string
    {
        $notesByStatus = [
            CollectionLog::STATUS_ISSUE_REPORTED => [
                // First notes - acknowledgment
                [
                    'Issue acknowledged. Maintenance team notified.',
                    'Reviewed issue report. Will follow up with crew.',
                    'Issue logged. Investigating root cause.',
                    'Received issue report. Taking appropriate action.',
                    'Issue documented. Coordinating with relevant departments.',
                ],
                // Follow-up notes
                [
                    'Spoke with crew member. Additional details obtained.',
                    'Issue has been escalated to operations manager.',
                    'Maintenance completed. Issue should be resolved.',
                    'Route adjustment scheduled for next collection.',
                    'Follow-up collection arranged for affected areas.',
                ],
                // Resolution notes
                [
                    'Issue resolved. No further action needed.',
                    'Preventive measures implemented.',
                    'Crew provided additional training on similar situations.',
                    'Route permanently modified to avoid this issue.',
                    'Equipment upgraded to prevent recurrence.',
                ],
            ],
            CollectionLog::STATUS_INCOMPLETE => [
                // First notes
                [
                    'Incomplete collection noted. Follow-up scheduled.',
                    'Reviewed incomplete report. Arranging completion.',
                    'Partial completion acknowledged. Planning return visit.',
                    'Incomplete status reviewed. Coordinating with dispatch.',
                    'Follow-up collection being arranged.',
                ],
                // Follow-up notes
                [
                    'Follow-up collection completed successfully.',
                    'Remaining areas serviced on next scheduled route.',
                    'Crew returned and completed collection.',
                    'Issue preventing completion has been addressed.',
                    'Route completed with additional resources.',
                ],
            ],
            CollectionLog::STATUS_COMPLETED => [
                // Quality check notes
                [
                    'Quality check completed. No issues found.',
                    'Excellent work. Route completed efficiently.',
                    'Reviewed completion. All standards met.',
                    'Random quality audit - passed.',
                    'Completion verified. Good job by crew.',
                ],
            ],
        ];

        $statusNotes = $notesByStatus[$status] ?? [['Note added by administrator.']];
        
        // Get the appropriate note set based on index
        $noteSet = $statusNotes[min($noteIndex, count($statusNotes) - 1)];
        
        return $noteSet[array_rand($noteSet)];
    }
}
