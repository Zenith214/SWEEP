@section('title', 'Report Details')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="reports" />
    </x-slot>

    <div class="mb-4">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Reports
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
                        {{ $statuses[$report->status] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Resident Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Resident Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Name</label>
                            <p class="mb-0">
                                <strong>{{ $report->resident->name }}</strong>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email</label>
                            <p class="mb-0">
                                <i class="bi bi-envelope"></i> {{ $report->resident->email }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Details -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Report Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Report Type</label>
                        <p class="mb-0">
                            <strong>{{ $reportTypes[$report->report_type] }}</strong>
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
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Administrator Responses</h5>
                </div>
                <div class="card-body">
                    @if($report->responses->isNotEmpty())
                        @foreach($report->responses as $response)
                            <div class="border-start border-4 border-primary ps-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong>{{ $response->admin->name }}</strong>
                                    <small class="text-muted">{{ $response->created_at->format('M d, Y h:i A') }}</small>
                                </div>
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $response->response }}</p>
                            </div>
                        @endforeach
                        <hr>
                    @endif

                    <!-- Add Response Form -->
                    <form action="{{ route('admin.reports.add-response', $report) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="response" class="form-label">Add Response</label>
                            <textarea 
                                name="response" 
                                id="response" 
                                class="form-control @error('response') is-invalid @enderror" 
                                rows="4" 
                                placeholder="Enter your response to the resident..."
                                maxlength="1000"
                                required
                            ></textarea>
                            @error('response')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum 1000 characters</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Add Response
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Update Status -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Update Status</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.update-status', $report) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select 
                                name="status" 
                                id="status" 
                                class="form-select @error('status') is-invalid @enderror"
                                required
                            >
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ $report->status == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <textarea 
                                name="note" 
                                id="note" 
                                class="form-control @error('note') is-invalid @enderror" 
                                rows="3" 
                                placeholder="Add a note about this status change..."
                                maxlength="1000"
                                required
                            ></textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum 1000 characters</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- Assignment -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-check"></i> Assignment</h5>
                    @if($report->route || $report->assignedTo)
                        <form action="{{ route('admin.reports.unassign', $report) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Assignment">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if($report->route || $report->assignedTo)
                        <div class="mb-3">
                            @if($report->route)
                                <div class="mb-2">
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
                        <hr>
                    @endif

                    <!-- Assignment Form -->
                    <form action="{{ route('admin.reports.assign', $report) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="route_id" class="form-label">Route</label>
                            <select 
                                name="route_id" 
                                id="route_id" 
                                class="form-select @error('route_id') is-invalid @enderror"
                            >
                                <option value="">Select Route (Optional)</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}" {{ $report->route_id == $route->id ? 'selected' : '' }}>
                                        {{ $route->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('route_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Crew Member</label>
                            <select 
                                name="assigned_to" 
                                id="assigned_to" 
                                class="form-select @error('assigned_to') is-invalid @enderror"
                            >
                                <option value="">Select Crew (Optional)</option>
                                @foreach($crewMembers as $crew)
                                    <option value="{{ $crew->id }}" {{ $report->assigned_to == $crew->id ? 'selected' : '' }}>
                                        {{ $crew->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> {{ $report->route || $report->assignedTo ? 'Update' : 'Assign' }}
                        </button>
                    </form>
                </div>
            </div>

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
                                                <strong>{{ $statuses[$history->new_status] }}</strong>
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
