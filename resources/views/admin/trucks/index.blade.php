@section('title', 'Truck Management')

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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Truck Management</h1>
        <a href="{{ route('admin.trucks.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Register Truck
        </a>
    </div>

    <!-- Search and Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.trucks.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="search" 
                            name="search" 
                            placeholder="Search by truck number or license plate..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Operational Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Trucks</option>
                        <option value="operational" {{ request('status') === 'operational' ? 'selected' : '' }}>Operational</option>
                        <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="out_of_service" {{ request('status') === 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Trucks Table -->
    <div class="card">
        <div class="card-body">
            @if($trucks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Truck Number</th>
                                <th>License Plate</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Assignments</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trucks as $truck)
                                <tr>
                                    <td>
                                        <strong>{{ $truck->truck_number }}</strong>
                                    </td>
                                    <td>{{ $truck->license_plate }}</td>
                                    <td>{{ number_format($truck->capacity, 2) }} tons</td>
                                    <td>
                                        @php
                                            $statusBadge = match($truck->operational_status) {
                                                'operational' => ['class' => 'bg-success', 'icon' => 'check-circle', 'text' => 'Operational'],
                                                'maintenance' => ['class' => 'bg-warning text-dark', 'icon' => 'tools', 'text' => 'Maintenance'],
                                                'out_of_service' => ['class' => 'bg-danger', 'icon' => 'x-circle', 'text' => 'Out of Service'],
                                                default => ['class' => 'bg-secondary', 'icon' => 'question-circle', 'text' => 'Unknown']
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge['class'] }}">
                                            <i class="bi bi-{{ $statusBadge['icon'] }}"></i> {{ $statusBadge['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($truck->assignments_count > 0)
                                            <span class="badge" style="background-color: var(--sweep-accent);">
                                                {{ $truck->assignments_count }} 
                                                {{ Str::plural('Assignment', $truck->assignments_count) }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark">No Assignments</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.trucks.show', $truck) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.trucks.edit', $truck) }}" 
                                               class="btn btn-sm btn-outline-secondary"
                                               data-bs-toggle="tooltip"
                                               title="Edit Truck">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#statusModal{{ $truck->id }}"
                                                    title="Update Status">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $truck->id }}"
                                                    title="Delete Truck">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Status Update Modal -->
                                        <div class="modal fade" id="statusModal{{ $truck->id }}" tabindex="-1">
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
                                                                <label for="operational_status{{ $truck->id }}" class="form-label">New Status</label>
                                                                <select name="operational_status" id="operational_status{{ $truck->id }}" class="form-select" required>
                                                                    <option value="operational" {{ $truck->operational_status === 'operational' ? 'selected' : '' }}>Operational</option>
                                                                    <option value="maintenance" {{ $truck->operational_status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                                    <option value="out_of_service" {{ $truck->operational_status === 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="notes{{ $truck->id }}" class="form-label">Status Change Notes</label>
                                                                <textarea name="notes" id="notes{{ $truck->id }}" class="form-control" rows="3" placeholder="Optional notes about this status change..."></textarea>
                                                            </div>
                                                            @if($truck->future_assignments_count > 0)
                                                                <div class="alert alert-warning">
                                                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                                                    <strong>Warning:</strong> This truck has {{ $truck->future_assignments_count }} future 
                                                                    {{ Str::plural('assignment', $truck->future_assignments_count) }}. 
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

                                        <!-- Delete Confirmation Modal -->
                                        <div class="modal fade" id="deleteModal{{ $truck->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete truck <strong>{{ $truck->truck_number }}</strong>?</p>
                                                        @if($truck->future_assignments_count > 0)
                                                            <div class="alert alert-danger">
                                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                                <strong>Cannot Delete:</strong> This truck has {{ $truck->future_assignments_count }} future 
                                                                {{ Str::plural('assignment', $truck->future_assignments_count) }}. 
                                                                You must cancel or reassign them first.
                                                            </div>
                                                        @else
                                                            <p class="text-danger">This action cannot be undone.</p>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        @if($truck->future_assignments_count === 0)
                                                            <form method="POST" action="{{ route('admin.trucks.destroy', $truck) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Delete Truck</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $trucks->firstItem() }} to {{ $trucks->lastItem() }} of {{ $trucks->total() }} trucks
                    </div>
                    <div>
                        {{ $trucks->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-truck text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No Trucks Found</h4>
                    <p class="text-muted">
                        @if(request('search') || request('status'))
                            No trucks match your search criteria. Try adjusting your filters.
                        @else
                            Get started by registering your first truck.
                        @endif
                    </p>
                    @if(!request('search') && !request('status'))
                        <a href="{{ route('admin.trucks.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Register First Truck
                        </a>
                    @else
                        <a href="{{ route('admin.trucks.index') }}" class="btn btn-secondary mt-2">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    @endif
                </div>
            @endif
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
