<?php

namespace App\Services;

use App\Models\RecyclingLog;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecyclingExportService
{
    /**
     * Export recycling logs to CSV format.
     *
     * @param Collection $logs Collection of RecyclingLog models
     * @param string $startDate Start date for filename
     * @param string $endDate End date for filename
     * @return StreamedResponse
     */
    public function exportLogs(Collection $logs, string $startDate, string $endDate): StreamedResponse
    {
        $filename = $this->generateFilename($startDate, $endDate);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Write CSV header
            fputcsv($file, [
                'Collection Date',
                'Crew Member Name',
                'Route Identifier',
                'Zone',
                'Material Types',
                'Weight Values (kg)',
                'Total Weight (kg)',
                'Quality Issue',
                'Notes',
            ]);

            // Write data rows
            foreach ($logs as $log) {
                fputcsv($file, $this->formatLogForExport($log));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Format a RecyclingLog for CSV export.
     *
     * @param RecyclingLog $log
     * @return array
     */
    public function formatLogForExport(RecyclingLog $log): array
    {
        // Get materials data
        $materials = $log->materials;
        
        // Build material types string (comma-separated)
        $materialTypes = $materials->pluck('material_type')->implode(', ');
        
        // Build weight values string (comma-separated with material type labels)
        $weightValues = $materials->map(function ($material) {
            return "{$material->material_type}: {$material->weight}";
        })->implode(', ');

        // Get total weight
        $totalWeight = $log->getTotalWeight();

        // Get crew member name
        $crewMemberName = $log->user ? $log->user->name : 'N/A';

        // Get route identifier
        $routeIdentifier = $log->route ? $log->route->name : 'N/A';

        // Get zone
        $zone = $log->route ? $log->route->zone : 'N/A';

        // Format quality issue
        $qualityIssue = $log->quality_issue ? 'Yes' : 'No';

        // Format notes (handle null and line breaks)
        $notes = $log->notes ? str_replace(["\r\n", "\r", "\n"], ' ', $log->notes) : '';

        return [
            $log->collection_date->format('Y-m-d'),
            $crewMemberName,
            $routeIdentifier,
            $zone,
            $materialTypes,
            $weightValues,
            number_format($totalWeight, 2),
            $qualityIssue,
            $notes,
        ];
    }

    /**
     * Generate filename for CSV export.
     *
     * @param string $startDate
     * @param string $endDate
     * @return string
     */
    protected function generateFilename(string $startDate, string $endDate): string
    {
        return "recycling-export-{$startDate}-{$endDate}.csv";
    }
}
