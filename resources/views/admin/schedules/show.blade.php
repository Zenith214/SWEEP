@section('title', 'Schedule Details')

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
            <a class="nav-link active" href="{{ route('admin.schedules.index') }}">
                <i class="bi bi-calendar"></i> Schedules
            </a>
            <a class="nav-link" href="{{ route('admin.holidays.index') }}">
                <i class="bi bi-calendar-x"></i> Holidays
            </a>
            <a class="nav-link" href="{{ route('admin.trucks.index') }}">
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
                <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Schedules</a></li>
                <li class="breadcrumb-item active">Schedule Details</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h2 mb-0">Schedule Details</h1>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="{{ route('admin.schedules.duplicate', $schedule) }}" class="btn btn-info">
                    <i class="bi bi-files"></i> Duplicate
                </a>
                <button type="button" 
                        class="btn btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Schedule Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Schedule Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Route</h6>
                            <p class="mb-0">
                                <strong>{{ $schedule->route->name }}</strong>
                                <a href="{{ route('admin.routes.show', $schedule->route) }}" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="bi bi-eye"></i> View Route
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Zone</h6>
                            <p class="mb-0">
                                <span class="badge bg-secondary fs-6">{{ $schedule->route->zone }}</span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Collection Days</h6>
                            <p class="mb-0">
                                @php
                                    $days = $schedule->getDaysOfWeek();
                                    $dayNames = [
                                        0 => 'Sunday',
                                        1 => 'Monday',
                                        2 => 'Tuesday',
                                        3 => 'Wednesday',
                                        4 => 'Thursday',
                                        5 => 'Friday',
                                        6 => 'Saturday'
                                    ];
                                @endphp
                                @foreach($days as $day)
                                    <span class="badge fs-6" style="background-color: var(--sweep-primary); margin-right: 4px;">
                                        {{ $dayNames[$day] }}
                                    </span>
                                @endforeach
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Collection Time</h6>
                            <p class="mb-0">
                                <i class="bi bi-clock"></i> 
                                <strong>{{ $schedule->collection_time->format('g:i A') }}</strong>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Start Date</h6>
                            <p class="mb-0">
                                <i class="bi bi-calendar-event"></i> 
                                {{ $schedule->start_date->format('l, F d, Y') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">End Date</h6>
                            <p class="mb-0">
                                @if($schedule->end_date)
                                    <i class="bi bi-calendar-event"></i> 
                                    {{ $schedule->end_date->format('l, F d, Y') }}
                                @else
                                    <span class="text-muted">
                                        <i class="bi bi-infinity"></i> Ongoing (No end date)
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Status</h6>
                            <p class="mb-0">
                                @if($schedule->is_active)
                                    <span class="badge bg-success fs-6">
                                        <i class="bi bi-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary fs-6">
                                        <i class="bi bi-x-circle"></i> Inactive
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Created</h6>
                            <p class="mb-0 text-muted small">
                                {{ $schedule->created_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Route Details Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-map"></i> Route Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h6 class="text-muted small mb-1">Route Name</h6>
                            <p class="mb-0"><strong>{{ $schedule->route->name }}</strong></p>
                        </div>
                    </div>

                    @if($schedule->route->description)
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <h6 class="text-muted small mb-1">Description</h6>
                                <p class="mb-0">{{ $schedule->route->description }}</p>
                            </div>
                        </div>
                    @endif

                    @if($schedule->route->notes)
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-muted small mb-1">Special Instructions / Notes</h6>
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle"></i>
                                    {{ $schedule->route->notes }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Schedule
                        </a>
                        <a href="{{ route('admin.schedules.duplicate', $schedule) }}" class="btn btn-info">
                            <i class="bi bi-files"></i> Duplicate to Another Route
                        </a>
                        <button type="button" 
                                class="btn btn-{{ $schedule->is_active ? 'warning' : 'success' }} toggle-active-btn"
                                data-schedule-id="{{ $schedule->id }}">
                            <i class="bi bi-{{ $schedule->is_active ? 'pause' : 'play' }}-circle"></i> 
                            {{ $schedule->is_active ? 'Deactivate' : 'Activate' }} Schedule
                        </button>
                        <hr>
                        <button type="button" 
                                class="btn btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal">
                            <i class="bi bi-trash"></i> Delete Schedule
                        </button>
                    </div>
                </div>
            </div>

            <!-- Schedule Summary Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Schedule Summary</h5>
                </div>
                <div class="card-body">
                    <h6>Collection Pattern</h6>
                    <p class="small">
                        This schedule runs 
                        <strong>{{ implode(', ', array_map(fn($d) => $dayNames[$d], $days)) }}</strong>
                        at <strong>{{ $schedule->collection_time->format('g:i A') }}</strong>.
                    </p>

                    <h6 class="mt-3">Duration</h6>
                    <p class="small">
                        @if($schedule->end_date)
                            This schedule runs from 
                            <strong>{{ $schedule->start_date->format('M d, Y') }}</strong>
                            to <strong>{{ $schedule->end_date->format('M d, Y') }}</strong>
                            ({{ $schedule->start_date->diffInDays($schedule->end_date) }} days).
                        @else
                            This is an ongoing schedule that started on 
                            <strong>{{ $schedule->start_date->format('M d, Y') }}</strong>
                            with no end date.
                        @endif
                    </p>

                    <h6 class="mt-3">Visibility</h6>
                    <p class="small mb-0">
                        @if($schedule->is_active)
                            This schedule is <strong class="text-success">active</strong> and visible to residents and collection crews.
                        @else
                            This schedule is <strong class="text-secondary">inactive</strong> and hidden from residents and collection crews.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this schedule?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Warning:</strong> This action cannot be undone. The schedule will be permanently removed.
                    </div>
                    <p class="mb-0"><strong>Schedule Details:</strong></p>
                    <ul>
                        <li>Route: {{ $schedule->route->name }}</li>
                        <li>Days: {{ implode(', ', array_map(fn($d) => $dayNames[$d], $days)) }}</li>
                        <li>Time: {{ $schedule->collection_time->format('g:i A') }}</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Schedule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Handle toggle active button click
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-active-btn');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const scheduleId = this.getAttribute('data-schedule-id');
                    
                    fetch(`/admin/schedules/${scheduleId}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to reflect changes
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to toggle schedule status. Please try again.');
                    });
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
