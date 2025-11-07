@section('title', 'Truck Details')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a class="nav-link" href="{{ route('admin.routes.index') }}">
                <i class="bi bi-map"></i> Routes
            </a>
            <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                <i class="bi bi-calendar"></i> Schedules
            </a>
            <a class="nav-link" href="{{ route('admin.holidays.index') }}">
                <i class="bi bi-calendar-x"></i> Holidays
            </a>
            <a class="nav-link active" href="{{ route('admin.trucks.index') }}">
                <i class="bi bi-truck"></i> Trucks
            </a>
            <a class="nav-link" href="{{ route('admin.assignments.index') }}">
                <i class="bi bi-clipboard-check"></i> Assignments
            </a>
            <a class="nav-link" href="{{ route('admin.truck-availability.index') }}">
                <i class="bi bi-calendar-check"></i> Truck Availability
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-file-text"></i> Reports <small>(Coming Soon)</small>
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-recycle"></i> Recycling <small>(Coming Soon)</small>
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.trucks.index') }}">Trucks</a></li>
                <li class="breadcrumb-item active">{{ $truck->truck_number }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h2 mb-0">Truck: {{ $truck->truck_number }}</h1>
            <div>
                <a href="{{ route('admin.trucks.edit', $truck) }}" class="btn btn-secondary">
                    <i class="bi bi-pencil"></i> Edit Truck
                </a>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#statusModal">
                    <i class="bi bi-arrow-repeat"></i> Update Status
                </button>
            </div>
        </div>
    </div>

    <!-- Truck Information Card -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Truck Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Truck Number</label>
                            <div class="fw-bold">{{ $truck->truck_number }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">License Plate</label>
                            <div class="fw-bold">{{ $truck->license_plate }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Capacity</label>
                            <div class="fw-bold">{{ number_format($truck->capacity, 2) }} tons</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Operational Status</label>
                            <div>
                                @php
                                    $statusBadge = match($truck->operational_status) {
                                        'operational' => ['class' => 'bg-success', 'icon' => 'check-circle', 'text' => 'Operational'],
                                        'maintenance' => ['class' => 'bg-warning text-dark', 'icon' => 'tools', 'text' => 'Maintenance'],
                                        'out_of_service' => ['class' => 'bg-danger', 'icon' => 'x-circle', 'text' => 'Out of Service'],
                                        default => ['class' => 'bg-secondary', 'icon' => 'question-circle', 'text' => 'Unknown']
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge['class'] }} fs-6">
                                    <i class="bi bi-{{ $statusBadge['icon'] }}"></i> {{ $statusBadge['text'] }}
                                </span>
                            </div>
                        </div>
                        @if($truck->notes)
                            <div class="col-12 mb-3">
                                <label class="text-muted small">Notes</label>
                                <div>{{ $truck->notes }}</div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="text-muted small">Created</label>
                            <div>{{ $truck->created_at->format('M d, Y g:i A') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Last Updated</label>
                            <div>{{ $truck->updated_at->format('M d, Y g:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Utilization Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Utilization Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Total Assignments</label>
                        <div class="h3 mb-0">{{ $totalAssignments }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Utilization Rate (Last 30 Days)</label>
                        <div class="h3 mb-0">{{ number_format($utilizationRate, 1) }}%</div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: {{ $utilizationRate }}%; background-color: var(--sweep-accent);" 
                                 aria-valuenow="{{ $utilizationRate }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="text-muted small">Assignments (Last 30 Days)</label>
                        <div class="h3 mb-0">{{ $recentAssignments }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment History -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Assignment History</h5>
            <div class="d-flex gap-2">
                <form method="GET" action="{{ route('admin.trucks.show', $truck) }}" class="d-flex gap-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" 
                           value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}"
                           max="{{ now()->format('Y-m-d') }}">
                    <input type="date" name="end_date" class="form-control form-control-sm" 
                           value="{{ request('end_date', now()->format('Y-m-d')) }}"
                           max="{{ now()->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </form>
                <a href="{{ route('admin.trucks.history', ['truck' => $truck, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                   class="btn btn-sm btn-success">
                    <i class="bi bi-download"></i> Export
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($assignments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Route</th>
                                <th>Crew Member</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->assignment_date->format('M d, Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assignment->assignment_date->format('l') }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $assignment->route->name }}</div>
                                        <small class="text-muted">Zone: {{ $assignment->route->zone }}</small>
                                    </td>
                                    <td>{{ $assignment->user->name }}</td>
                                    <td>
                                        @if($assignment->status === 'active')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->notes)
                                            <span data-bs-toggle="tooltip" title="{{ $assignment->notes }}">
                                                <i class="bi bi-sticky"></i>
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($assignments->hasPages())
                    <div class="mt-3">
                        {{ $assignments->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No assignments found for the selected date range.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Status Change History -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Status Change History</h5>
        </div>
        <div class="card-body">
            @if($statusHistory->count() > 0)
                <div class="timeline">
                    @foreach($statusHistory as $history)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="rounded-circle bg-light p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            @if($history->old_status)
                                                @php
                                                    $oldBadge = match($history->old_status) {
                                                        'operational' => ['class' => 'bg-success', 'text' => 'Operational'],
                                                        'maintenance' => ['class' => 'bg-warning text-dark', 'text' => 'Maintenance'],
                                                        'out_of_service' => ['class' => 'bg-danger', 'text' => 'Out of Service'],
                                                        default => ['class' => 'bg-secondary', 'text' => 'Unknown']
                                                    };
                                                    $newBadge = match($history->new_status) {
                                                        'operational' => ['class' => 'bg-success', 'text' => 'Operational'],
                                                        'maintenance' => ['class' => 'bg-warning text-dark', 'text' => 'Maintenance'],
                                                        'out_of_service' => ['class' => 'bg-danger', 'text' => 'Out of Service'],
                                                        default => ['class' => 'bg-secondary', 'text' => 'Unknown']
                                                    };
                                                @endphp
                                                <span class="badge {{ $oldBadge['class'] }}">{{ $oldBadge['text'] }}</span>
                                                <i class="bi bi-arrow-right mx-2"></i>
                                                <span class="badge {{ $newBadge['class'] }}">{{ $newBadge['text'] }}</span>
                                            @else
                                                @php
                                                    $newBadge = match($history->new_status) {
                                                        'operational' => ['class' => 'bg-success', 'text' => 'Operational'],
                                                        'maintenance' => ['class' => 'bg-warning text-dark', 'text' => 'Maintenance'],
                                                        'out_of_service' => ['class' => 'bg-danger', 'text' => 'Out of Service'],
                                                        default => ['class' => 'bg-secondary', 'text' => 'Unknown']
                                                    };
                                                @endphp
                                                <span class="badge {{ $newBadge['class'] }}">{{ $newBadge['text'] }}</span>
                                                <span class="text-muted ms-2">(Initial Status)</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $history->created_at->format('M d, Y g:i A') }}</small>
                                    </div>
                                    <div class="mt-1">
                                        <small class="text-muted">Changed by: {{ $history->changedBy->name }}</small>
                                    </div>
                                    @if($history->notes)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small>{{ $history->notes }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No status changes recorded.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status - {{ $truck->truck_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.trucks.update-status', $truck) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <div>
                                <span class="badge {{ $statusBadge['class'] }} fs-6">
                                    <i class="bi bi-{{ $statusBadge['icon'] }}"></i> {{ $statusBadge['text'] }}
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="operational_status" class="form-label">New Status</label>
                            <select name="operational_status" id="operational_status" class="form-select" required>
                                <option value="operational" {{ $truck->operational_status === 'operational' ? 'selected' : '' }}>Operational</option>
                                <option value="maintenance" {{ $truck->operational_status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="out_of_service" {{ $truck->operational_status === 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Status Change Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Optional notes about this status change..."></textarea>
                        </div>
                        @if($futureAssignmentsCount > 0)
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Warning:</strong> This truck has {{ $futureAssignmentsCount }} future 
                                {{ Str::plural('assignment', $futureAssignmentsCount) }}. 
                                Changing status may affect operations.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize Bootstrap tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    @endpush
</x-app-layout>
