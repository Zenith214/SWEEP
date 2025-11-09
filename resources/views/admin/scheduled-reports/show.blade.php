@section('title', 'Scheduled Report Details')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="scheduled-reports" />
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">{{ $scheduledReport->name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.scheduled-reports.edit', $scheduledReport) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.scheduled-reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Report Configuration</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            @if ($scheduledReport->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Frequency</dt>
                        <dd class="col-sm-8">{{ ucfirst($scheduledReport->frequency) }}</dd>

                        <dt class="col-sm-4 text-muted">Format</dt>
                        <dd class="col-sm-8">{{ strtoupper($scheduledReport->format) }}</dd>

                        <dt class="col-sm-4 text-muted">Created</dt>
                        <dd class="col-sm-8">{{ $scheduledReport->created_at->format('M d, Y') }}</dd>

                        @if ($scheduledReport->last_generated_at)
                            <dt class="col-sm-4 text-muted">Last Generated</dt>
                            <dd class="col-sm-8">{{ $scheduledReport->last_generated_at->format('M d, Y g:i A') }}</dd>
                        @endif

                        @if ($scheduledReport->is_active && $scheduledReport->next_generation_at)
                            <dt class="col-sm-4 text-muted">Next Generation</dt>
                            <dd class="col-sm-8 mb-0">{{ $scheduledReport->next_generation_at->format('M d, Y g:i A') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Included Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($scheduledReport->metrics as $metric)
                            <span class="badge bg-primary">
                                {{ \App\Models\ScheduledReport::getAvailableMetrics()[$metric] ?? $metric }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Generated Reports</h5>
        </div>
        <div class="card-body">
            @if ($scheduledReport->generatedReports->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-file-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No reports have been generated yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Generated</th>
                                <th>Size</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scheduledReport->generatedReports as $generated)
                                <tr>
                                    <td>{{ $generated->getPeriodDescription() }}</td>
                                    <td>{{ $generated->generated_at->format('M d, Y g:i A') }}</td>
                                    <td>{{ $generated->getFormattedFileSize() }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('reports.download', $generated) }}" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <form action="{{ route('reports.delete-generated', $generated) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
