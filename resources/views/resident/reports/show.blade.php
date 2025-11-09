@section('title', 'Report Details')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('resident.schedules') }}">
                <i class="bi bi-calendar3"></i> My Schedule
            </a>
            <a class="nav-link" href="{{ route('resident.reports.create') }}">
                <i class="bi bi-file-earmark-plus"></i> Submit Report
            </a>
            <a class="nav-link active" href="{{ route('resident.reports') }}">
                <i class="bi bi-list-check"></i> My Reports
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="mb-4">
        <a href="{{ route('resident.reports') }}" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Back to My Reports
        </a>
    </div>

    <!-- Report Header -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div>
                    <h1 class="h3 mb-2">{{ $report->reference_number }}</h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar"></i> Submitted on {{ $report->created_at->format('F d, Y \a\t h:i A') }}
                    </p>
                </div>
                <div>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'in_progress' => 'primary',
                            'resolved' => 'success',
                            'closed' => 'secondary'
                        ];
                        $color = $statusColors[$report->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
                        {{ \App\Models\Report::STATUSES[$report->status] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Report Details -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Report Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Report Type</label>
                        <p class="mb-0">
                            <strong>{{ \App\Models\Report::REPORT_TYPES[$report->report_type] }}</strong>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Location</label>
                        <p class="mb-0">
                            <i class="bi bi-geo-alt"></i> {{ $report->location }}
                        </p>
                    </div>
                    <div>
                        <label class="text-muted small">Description</label>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $report->description }}</p>
                    </div>
                </div>
            </div>

            <!-- Photos -->
            @if($report->photos->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-images"></i> Photos ({{ $report->photos->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($report->photos as $photo)
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <img 
                                            src="{{ Storage::url($photo->file_path) }}" 
                                            alt="Report Photo" 
                                            class="img-fluid rounded"
                                            style="cursor: pointer; width: 100%; height: 200px; object-fit: cover;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#photoModal{{ $photo->id }}"
                                        >
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <button 
                                                class="btn btn-sm btn-light"
                                                data-bs-toggle="modal"
                                                data-bs-target="#photoModal{{ $photo->id }}"
                                            >
                                                <i class="bi bi-zoom-in"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo Modal -->
                                <div class="modal fade" id="photoModal{{ $photo->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $photo->file_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img 
                                                    src="{{ Storage::url($photo->file_path) }}" 
                                                    alt="Report Photo" 
                                                    class="img-fluid"
                                                >
                                            </div>
                                            <div class="modal-footer">
                                                <small class="text-muted me-auto">
                                                    Uploaded: {{ $photo->uploaded_at->format('M d, Y h:i A') }}
                                                </small>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Administrator Responses -->
            @if($report->responses->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Administrator Responses</h5>
                    </div>
                    <div class="card-body">
                        @foreach($report->responses as $response)
                            <div class="border-start border-4 border-primary ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong>{{ $response->admin->name }}</strong>
                                    <small class="text-muted">{{ $response->created_at->format('M d, Y h:i A') }}</small>
                                </div>
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $response->response }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Assignment Information -->
            @if($report->route || $report->assignedTo)
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-person-check"></i> Assignment</h5>
                    </div>
                    <div class="card-body">
                        @if($report->route)
                            <div class="mb-3">
                                <label class="text-muted small">Assigned Route</label>
                                <p class="mb-0">
                                    <i class="bi bi-signpost"></i> <strong>{{ $report->route->name }}</strong>
                                </p>
                            </div>
                        @endif
                        @if($report->assignedTo)
                            <div>
                                <label class="text-muted small">Assigned Crew Member</label>
                                <p class="mb-0">
                                    <i class="bi bi-person"></i> <strong>{{ $report->assignedTo->name }}</strong>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Status History -->
            @if($report->statusHistory->isNotEmpty())
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Status History</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($report->statusHistory->sortByDesc('created_at') as $history)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            @php
                                                $statusIcons = [
                                                    'pending' => 'clock',
                                                    'in_progress' => 'arrow-repeat',
                                                    'resolved' => 'check-circle',
                                                    'closed' => 'x-circle'
                                                ];
                                                $icon = $statusIcons[$history->new_status] ?? 'circle';
                                                $color = $statusColors[$history->new_status] ?? 'secondary';
                                            @endphp
                                            <div class="bg-{{ $color }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-{{ $icon }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <strong>{{ \App\Models\Report::STATUSES[$history->new_status] }}</strong>
                                                <small class="text-muted">{{ $history->created_at->format('M d, Y') }}</small>
                                            </div>
                                            <small class="text-muted d-block">{{ $history->created_at->format('h:i A') }}</small>
                                            @if($history->changedBy)
                                                <small class="text-muted d-block">
                                                    <i class="bi bi-person"></i> {{ $history->changedBy->name }}
                                                </small>
                                            @endif
                                            @if($history->note)
                                                <p class="mt-2 mb-0 small" style="white-space: pre-wrap;">{{ $history->note }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style>
        .timeline-item:not(:last-child) {
            border-left: 2px solid #dee2e6;
            margin-left: 20px;
            padding-left: 20px;
            padding-bottom: 1rem;
        }
        
        .timeline-item:last-child {
            margin-left: 20px;
            padding-left: 20px;
        }
    </style>
    @endpush
</x-app-layout>
