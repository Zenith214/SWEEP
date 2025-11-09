<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GeneratedReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'scheduled_report_id',
        'file_path',
        'generated_at',
        'period_start',
        'period_end',
        'file_size',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'period_start' => 'date',
            'period_end' => 'date',
            'file_size' => 'integer',
        ];
    }

    /**
     * Get the scheduled report that owns this generated report.
     */
    public function scheduledReport(): BelongsTo
    {
        return $this->belongsTo(ScheduledReport::class);
    }

    /**
     * Scope to filter reports by date range.
     */
    public function scopeForDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('generated_at', [$start, $end]);
    }

    /**
     * Scope to filter recent reports.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by most recent.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('generated_at', 'desc');
    }

    /**
     * Check if the report file exists.
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Get the download URL for the report.
     */
    public function getDownloadUrl(): string
    {
        return route('reports.download', $this->id);
    }

    /**
     * Get the file extension.
     */
    public function getFileExtension(): string
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file name without path.
     */
    public function getFileName(): string
    {
        return basename($this->file_path);
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedFileSize(): string
    {
        if ($this->file_size === null) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Delete the report file from storage.
     */
    public function deleteFile(): bool
    {
        if ($this->fileExists()) {
            return Storage::delete($this->file_path);
        }

        return true;
    }

    /**
     * Get the period description.
     */
    public function getPeriodDescription(): string
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start->format('M d, Y') . ' - ' . $this->period_end->format('M d, Y');
        }

        return 'N/A';
    }

    /**
     * Clean up old generated reports (older than specified days).
     */
    public static function cleanupOldReports(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        $oldReports = self::where('generated_at', '<', $cutoffDate)->get();

        $deletedCount = 0;
        foreach ($oldReports as $report) {
            $report->deleteFile();
            $report->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete file when model is deleted
        static::deleting(function ($report) {
            $report->deleteFile();
        });
    }
}
