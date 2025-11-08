@section('title', 'Assignment Details')

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
            <a class="nav-link" href="{{ route('admin.trucks.index') }}">
                <i class="bi bi-truck"></i> Trucks
            </a>
            <a class="nav-link active" href="{{ route('admin.assignments.index') }}">
                <i class="bi bi-clipboard-check"></i> Assignments
            </a>
            <a class="nav-link" href="{{ route('admin.truck-availability.index') }}">
                <i class="bi bi-calendar-check"></i> Truck Availability
            </a>
            <a class="nav-link" href="{{ route('admin.collection-logs.index') }}">
                <i class="bi bi-clipboard-data"></i> Collection Logs
            </a>
            <a class="nav-link" href="{{ route('admin.analytics.collections.index') }}">
                <i class="bi bi-graph-up"></i> Collection Analytics
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
                <li class="breadcrumb-item"><a href="{{ route('admin.assignments.index') }}">Assignments</a></li>
                <li class="breadcrumb-item active" aria-current="page">Assignment #{{ $assignment->id }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-0">Assignment Details</h1>
                <p class="text-muted mb-0">{{ $assignment->assignment_date->format('l, F j, Y') }}</p>
            </div>
            <div>
                @if($assignment->status === 'active')
                    <a href="{{ route('admin.assignments.edit', $assignment) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                @else
                    <span class="badge bg-secondary fs-6">Cancelled</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Assignment Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clipboard-check"></i> Assignment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Truck Details -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-truck"></i> Truck
                            </h6>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $assignment->truck->truck_number }}</h5>
                                    <p class="text-muted mb-1">{{ $assignment->truck->license_plate }}</p>
                                    <p class="mb-1">
                                        <small class="text-muted">Capacity:</small> 
                                        {{ number_format($assignment->truck->capacity, 2) }} tons
                                    </p>
                                    <p class="mb-0">
                                        @php
                                            $statusBadge = match($assignment->truck->operational_status) {
                                                'operational' => ['class' => 'bg-success', 'icon' => 'check-circle', 'text' => 'Operational'],
                                                'maintenance' => ['class' => 'bg-warning text-dark', 'icon' => 'tools', 'text' => 'Maintenance'],
                                                'out_of_service' => ['class' => 'bg-danger', 'icon' => 'x-circle', 'text' => 'Out of Service'],
                                                default => ['class' => 'bg-secondary', 'icon' => 'question-circle', 'text' => 'Unknown']
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge['class'] }}">
                                            <i class="bi bi-{{ $statusBadge['icon'] }}"></i> {{ $statusBadge['text'] }}
                                        </span>
                                    </p>
                                </div>
                                <a href="{{ route('admin.trucks.show', $assignment->truck) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </div>
                        </div>

                        <!-- Crew Details -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-person"></i> Crew Member
                            </h6>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $assignment->user->name }}</h5>
                                    <p class="text-muted mb-1">{{ $assignment->user->email }}</p>
                                    <p class="mb-0">
                                        <span class="badge" style="background-color: var(--sweep-accent);">
                                            <i class="bi bi-person-badge"></i> Collection Crew
                                        </span>
                                    </p>
                                </div>
                                <a href="{{ route('admin.users.show', $assignment->user) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </div>
                        </div>

                        <!-- Route Details -->
                        <div class="col-12">
                            <h6 class="text-muted mb-3">
                                <i class="bi bi-map"></i> Route
                            </h6>
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $assignment->route->name }}</h5>
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-geo-alt"></i> Zone: {{ $assignment->route->zone }}
                                    </p>
                                    @if($assignment->route->description)
                                        <p class="mb-0">{{ $assignment->route->description }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('admin.routes.show', $assignment->route) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            @if($assignment->notes || $assignment->cancellation_reason)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-sticky"></i> Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($assignment->notes)
                            <div class="mb-3">
                                <h6 class="text-muted">Assignment Notes:</h6>
                                <p class="mb-0">{{ $assignment->notes }}</p>
                            </div>
                        @endif

                        @if($assignment->cancellation_reason)
                            <div class="alert alert-warning mb-0">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-triangle"></i> Cancellation Reason:
                                </h6>
                                <p class="mb-0">{{ $assignment->cancellation_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Status
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Status:</dt>
                        <dd class="col-sm-7">
                            @if($assignment->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Cancelled</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5">Date:</dt>
                        <dd class="col-sm-7">{{ $assignment->assignment_date->format('M d, Y') }}</dd>

                        <dt class="col-sm-5">Day:</dt>
                        <dd class="col-sm-7">{{ $assignment->assignment_date->format('l') }}</dd>

                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7">{{ $assignment->created_at->format('M d, Y') }}</dd>

                        <dt class="col-sm-5">Updated:</dt>
                        <dd class="col-sm-7 mb-0">{{ $assignment->updated_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar"></i> View Calendar
                        </a>
                        <a href="{{ route('admin.assignments.create', ['date' => $assignment->assignment_date->format('Y-m-d')]) }}" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Create Another
                        </a>
                        <a href="{{ route('admin.truck-availability.index', ['date' => $assignment->assignment_date->format('Y-m-d')]) }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-check"></i> Check Availability
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Assignment Modal -->
    @if($assignment->status === 'active')
        <div class="modal fade" id="cancelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Assignment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.assignments.cancel', $assignment) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body">
                            <p>Are you sure you want to cancel this assignment?</p>
                            
                            <div class="alert alert-info">
                                <strong>Assignment Details:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Truck: {{ $assignment->truck->truck_number }}</li>
                                    <li>Crew: {{ $assignment->user->name }}</li>
                                    <li>Route: {{ $assignment->route->name }}</li>
                                    <li>Date: {{ $assignment->assignment_date->format('M d, Y') }}</li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label for="cancellation_reason" class="form-label">Cancellation Reason (Optional)</label>
                                <textarea 
                                    class="form-control" 
                                    id="cancellation_reason" 
                                    name="cancellation_reason" 
                                    rows="3"
                                    placeholder="Provide a reason for cancelling this assignment..."
                                ></textarea>
                                <div class="form-text">This will be visible to administrators</div>
                            </div>

                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Note:</strong> The assignment will be marked as cancelled but not deleted. You can view cancelled assignments in the calendar.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle"></i> Keep Assignment
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Cancel Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
