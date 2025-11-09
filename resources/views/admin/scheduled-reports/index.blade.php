@section('title', 'Scheduled Reports')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="scheduled-reports" />
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Scheduled Reports</h1>
        <a href="{{ route('admin.scheduled-reports.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Create Scheduled Report
        </a>
    </div>

    @if ($scheduledReports->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-file-text text-muted" style="font-size: 3rem;"></i>
                <h3 class="mt-3">No scheduled reports</h3>
                <p class="text-muted">Get started by creating a new scheduled report.</p>
                <a href="{{ route('admin.scheduled-reports.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle"></i> Create Scheduled Report
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach ($scheduledReports as $report)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <h3 class="h5 mb-0">{{ $report->name }}</h3>
                                        @if ($report->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </div>
                                    
                                    <div class="d-flex gap-3 text-muted small mb-2">
                                        <span>
                                            <i class="bi bi-clock"></i> {{ ucfirst($report->frequency) }}
                                        </span>
                                        <span>
                                            <i class="bi bi-file-earmark"></i> {{ strtoupper($report->format) }}
                                        </span>
                                        <span>
                                            <i class="bi bi-bar-chart"></i> {{ count($report->metrics) }} metrics
                                        </span>
                                    </div>

                                    @if ($report->last_generated_at)
                                        <p class="text-muted small mb-1">
                                            Last generated: {{ $report->last_generated_at->diffForHumans() }}
                                        </p>
                                    @endif

                                    @if ($report->is_active && $report->next_generation_at)
                                        <p class="text-muted small mb-0">
                                            Next generation: {{ $report->next_generation_at->diffForHumans() }}
                                        </p>
                                    @endif
                                </div>

                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.scheduled-reports.toggle', $report) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ $report->is_active ? 'Disable' : 'Enable' }}">
                                            @if ($report->is_active)
                                                <i class="bi bi-pause-circle"></i>
                                            @else
                                                <i class="bi bi-play-circle"></i>
                                            @endif
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.scheduled-reports.show', $report) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.scheduled-reports.edit', $report) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('admin.scheduled-reports.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this scheduled report?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if ($report->generatedReports->isNotEmpty())
                                <hr class="my-3">
                                <h6 class="text-muted mb-2">Recent Reports</h6>
                                <div class="list-group list-group-flush">
                                    @foreach ($report->generatedReports->take(3) as $generated)
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span class="text-muted small">
                                                {{ $generated->getPeriodDescription() }}
                                                <span class="text-secondary">({{ $generated->getFormattedFileSize() }})</span>
                                            </span>
                                            <a href="{{ route('reports.download', $generated) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $scheduledReports->links() }}
        </div>
    @endif
</x-app-layout>
