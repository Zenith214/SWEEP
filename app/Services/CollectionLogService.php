<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CollectionLogService
{
    /**
     * Create a new collection log with validation.
     *
     * @param Assignment $assignment
     * @param array $data
     * @param User $user
     * @return CollectionLog
     * @throws \Exception
     */
    public function createLog(Assignment $assignment, array $data, User $user): CollectionLog
    {
        // Validate assignment is active
        if (!$assignment->isActive()) {
            throw new \Exception("Cannot log collection for a cancelled assignment");
        }

        // Check if log already exists for this assignment
        $existingLog = CollectionLog::where('assignment_id', $assignment->id)->first();
        if ($existingLog) {
            throw new \Exception("A collection log already exists for this assignment");
        }

        // Validate user is the assigned crew member
        if ($assignment->user_id !== $user->id) {
            throw new \Exception("Only the assigned crew member can log this collection");
        }

        // Validate status-specific requirements
        $this->validateStatusRequirements($data);

        // Create the collection log
        return CollectionLog::create([
            'assignment_id' => $assignment->id,
            'completion_time' => $data['completion_time'] ?? null,
            'status' => $data['status'],
            'issue_type' => $data['issue_type'] ?? null,
            'issue_description' => $data['issue_description'] ?? null,
            'completion_percentage' => $data['completion_percentage'] ?? null,
            'crew_notes' => $data['crew_notes'] ?? null,
            'created_by' => $user->id,
        ]);
    }

    /**
     * Update a collection log with edit window check.
     *
     * @param CollectionLog $log
     * @param array $data
     * @return CollectionLog
     * @throws \Exception
     */
    public function updateLog(CollectionLog $log, array $data): CollectionLog
    {
        // Edit window check is handled by middleware/authorization
        // but we double-check here for safety
        if (!$log->isEditable()) {
            throw new \Exception("This log can no longer be edited (2-hour window expired)");
        }

        // Validate status-specific requirements
        $this->validateStatusRequirements($data);

        // Update the log
        $log->update([
            'completion_time' => $data['completion_time'] ?? $log->completion_time,
            'status' => $data['status'],
            'issue_type' => $data['issue_type'] ?? null,
            'issue_description' => $data['issue_description'] ?? null,
            'completion_percentage' => $data['completion_percentage'] ?? null,
            'crew_notes' => $data['crew_notes'] ?? $log->crew_notes,
            'edited_at' => now(),
        ]);

        return $log->fresh();
    }

    /**
     * Check if a log can be edited by a user.
     *
     * @param CollectionLog $log
     * @param User $user
     * @return bool
     */
    public function canEdit(CollectionLog $log, User $user): bool
    {
        return $log->canBeEditedBy($user);
    }

    /**
     * Get collection history for a crew member with date filtering.
     *
     * @param User $user
     * @param Carbon|null $start
     * @param Carbon|null $end
     * @return Collection
     */
    public function getCrewHistory(User $user, ?Carbon $start = null, ?Carbon $end = null): Collection
    {
        $query = CollectionLog::where('created_by', $user->id)
            ->with(['assignment.truck', 'assignment.route', 'photos']);

        if ($start && $end) {
            $query->forDateRange($start, $end);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get collection logs with filters for admin.
     *
     * @param array $filters
     * @return Collection
     */
    public function getLogsWithFilters(array $filters): Collection
    {
        $query = CollectionLog::with([
            'assignment.truck',
            'assignment.route',
            'assignment.user',
            'creator',
            'photos'
        ]);

        // Filter by date range
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $start = Carbon::parse($filters['start_date']);
            $end = Carbon::parse($filters['end_date']);
            $query->forDateRange($start, $end);
        }

        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        // Filter by route
        if (isset($filters['route_id']) && $filters['route_id'] !== '') {
            $query->whereHas('assignment', function ($q) use ($filters) {
                $q->where('route_id', $filters['route_id']);
            });
        }

        // Filter by crew member
        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $query->where('created_by', $filters['user_id']);
        }

        // Search functionality
        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('crew_notes', 'like', "%{$search}%")
                    ->orWhere('issue_description', 'like', "%{$search}%")
                    ->orWhereHas('assignment.route', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Calculate completion rate for a date range.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array|null $filters
     * @return float
     */
    public function getCompletionRate(Carbon $start, Carbon $end, ?array $filters = null): float
    {
        $query = CollectionLog::forDateRange($start, $end);

        // Apply additional filters if provided
        if ($filters) {
            if (isset($filters['route_id']) && $filters['route_id'] !== '') {
                $query->whereHas('assignment', function ($q) use ($filters) {
                    $q->where('route_id', $filters['route_id']);
                });
            }

            if (isset($filters['user_id']) && $filters['user_id'] !== '') {
                $query->where('created_by', $filters['user_id']);
            }
        }

        $totalLogs = $query->count();
        
        if ($totalLogs === 0) {
            return 0.0;
        }

        $completedLogs = (clone $query)->completed()->count();

        return round(($completedLogs / $totalLogs) * 100, 2);
    }

    /**
     * Get status breakdown for a date range.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array|null $filters
     * @return array
     */
    public function getStatusBreakdown(Carbon $start, Carbon $end, ?array $filters = null): array
    {
        $query = CollectionLog::forDateRange($start, $end);

        // Apply additional filters if provided
        if ($filters) {
            if (isset($filters['route_id']) && $filters['route_id'] !== '') {
                $query->whereHas('assignment', function ($q) use ($filters) {
                    $q->where('route_id', $filters['route_id']);
                });
            }

            if (isset($filters['user_id']) && $filters['user_id'] !== '') {
                $query->where('created_by', $filters['user_id']);
            }
        }

        $breakdown = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present in the result
        return [
            CollectionLog::STATUS_COMPLETED => $breakdown[CollectionLog::STATUS_COMPLETED] ?? 0,
            CollectionLog::STATUS_INCOMPLETE => $breakdown[CollectionLog::STATUS_INCOMPLETE] ?? 0,
            CollectionLog::STATUS_ISSUE_REPORTED => $breakdown[CollectionLog::STATUS_ISSUE_REPORTED] ?? 0,
            CollectionLog::STATUS_PENDING => $breakdown[CollectionLog::STATUS_PENDING] ?? 0,
        ];
    }

    /**
     * Get routes with recurring issues.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param int $threshold
     * @return Collection
     */
    public function getRoutesWithRecurringIssues(Carbon $start, Carbon $end, int $threshold = 2): Collection
    {
        $routesWithIssues = CollectionLog::withIssues()
            ->forDateRange($start, $end)
            ->with(['assignment.route'])
            ->get()
            ->groupBy('assignment.route_id')
            ->map(function ($logs, $routeId) {
                return [
                    'route' => $logs->first()->assignment->route,
                    'issue_count' => $logs->count(),
                    'logs' => $logs,
                ];
            })
            ->filter(function ($routeData) use ($threshold) {
                return $routeData['issue_count'] >= $threshold;
            })
            ->sortByDesc('issue_count')
            ->values();

        return $routesWithIssues;
    }

    /**
     * Get issues grouped by type for a date range.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    public function getIssuesByType(Carbon $start, Carbon $end): array
    {
        $issuesByType = CollectionLog::withIssues()
            ->forDateRange($start, $end)
            ->select('issue_type', DB::raw('count(*) as count'))
            ->groupBy('issue_type')
            ->pluck('count', 'issue_type')
            ->toArray();

        // Ensure all issue types are present in the result
        $result = [];
        foreach (CollectionLog::ISSUE_TYPES as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'count' => $issuesByType[$key] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Validate status-specific requirements.
     *
     * @param array $data
     * @return void
     * @throws \Exception
     */
    protected function validateStatusRequirements(array $data): void
    {
        $status = $data['status'];

        // Validate completed status
        if ($status === CollectionLog::STATUS_COMPLETED) {
            if (empty($data['completion_time'])) {
                throw new \Exception("Completion time is required when marking as completed");
            }
        }

        // Validate issue reported status
        if ($status === CollectionLog::STATUS_ISSUE_REPORTED) {
            if (empty($data['issue_type'])) {
                throw new \Exception("Issue type is required when reporting an issue");
            }
            if (empty($data['issue_description'])) {
                throw new \Exception("Issue description is required when reporting an issue");
            }
        }

        // Validate status is valid
        $validStatuses = [
            CollectionLog::STATUS_COMPLETED,
            CollectionLog::STATUS_INCOMPLETE,
            CollectionLog::STATUS_ISSUE_REPORTED,
        ];

        if (!in_array($status, $validStatuses)) {
            throw new \Exception("Invalid status provided");
        }
    }
}
