<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportResponse;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Create a new report with unique reference number.
     */
    public function createReport(array $data, User $resident): Report
    {
        return DB::transaction(function () use ($data, $resident) {
            $data['resident_id'] = $resident->id;
            $data['reference_number'] = Report::generateReferenceNumber();
            $data['status'] = Report::STATUS_PENDING;

            $report = Report::create($data);

            // Record initial status in history
            ReportStatusHistory::create([
                'report_id' => $report->id,
                'old_status' => null,
                'new_status' => Report::STATUS_PENDING,
                'changed_by' => $resident->id,
                'note' => 'Report submitted',
                'created_at' => now()
            ]);

            return $report;
        });
    }

    /**
     * Update report status and record history.
     */
    public function updateStatus(Report $report, string $newStatus, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($report, $newStatus, $admin, $note) {
            $oldStatus = $report->status;

            // Update report status
            $report->status = $newStatus;
            
            // Set resolved_at timestamp if status is resolved or closed
            if (in_array($newStatus, [Report::STATUS_RESOLVED, Report::STATUS_CLOSED]) && !$report->resolved_at) {
                $report->resolved_at = now();
            }
            
            $report->save();

            // Record status change in history
            ReportStatusHistory::create([
                'report_id' => $report->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $admin->id,
                'note' => $note,
                'created_at' => now()
            ]);
        });
    }

    /**
     * Add an administrator response to a report.
     */
    public function addResponse(Report $report, string $response, User $admin): ReportResponse
    {
        return ReportResponse::create([
            'report_id' => $report->id,
            'admin_id' => $admin->id,
            'response' => $response
        ]);
    }

    /**
     * Assign report to route or crew member.
     */
    public function assignReport(Report $report, ?int $routeId, ?int $userId): void
    {
        $report->route_id = $routeId;
        $report->assigned_to = $userId;
        $report->save();
    }

    /**
     * Get reports for a specific resident with optional filters.
     */
    public function getResidentReports(User $resident, ?array $filters = null): Collection
    {
        $query = Report::where('resident_id', $resident->id)
            ->with(['photos', 'responses.admin', 'statusHistory.changedBy', 'route', 'assignedTo'])
            ->orderBy('created_at', 'desc');

        if ($filters) {
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['report_type'])) {
                $query->where('report_type', $filters['report_type']);
            }

            if (isset($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to']);
            }
        }

        return $query->get();
    }

    /**
     * Get reports with admin filters.
     */
    public function getReportsWithFilters(array $filters)
    {
        $query = Report::with(['resident', 'photos', 'responses.admin', 'statusHistory.changedBy', 'route', 'assignedTo'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['report_type'])) {
            $query->where('report_type', $filters['report_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['resident_id'])) {
            $query->where('resident_id', $filters['resident_id']);
        }

        if (isset($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('resident', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate(15);
    }

    /**
     * Search resident's reports by reference number.
     */
    public function searchByReference(string $referenceNumber, User $resident): ?Report
    {
        return Report::where('resident_id', $resident->id)
            ->where('reference_number', $referenceNumber)
            ->with(['photos', 'responses.admin', 'statusHistory.changedBy', 'route', 'assignedTo'])
            ->first();
    }
}
