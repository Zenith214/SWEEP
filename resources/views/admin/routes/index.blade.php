@section('title', 'Routes Management')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <i class="bi bi-people"></i> Users
            </a>
            <a class="nav-link active" href="{{ route('admin.routes.index') }}">
                <i class="bi bi-map"></i> Routes
            </a>
            <a class="nav-link" href="{{ route('admin.schedules.index') }}">
                <i class="bi bi-calendar"></i> Schedules
            </a>
            <a class="nav-link" href="{{ route('admin.holidays.index') }}">
                <i class="bi bi-calendar-x"></i> Holidays
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
        <h1 class="h2 mb-0">Routes Management</h1>
        <a href="{{ route('admin.routes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Route
        </a>
    </div>

    <!-- Search and Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.routes.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="search" 
                            name="search" 
                            placeholder="Search by route name or zone..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Routes</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
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

    <!-- Routes Table -->
    <div class="card">
        <div class="card-body">
            @if($routes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Route Name</th>
                                <th>Zone</th>
                                <th>Schedules</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($routes as $route)
                                <tr>
                                    <td>
                                        <strong>{{ $route->name }}</strong>
                                        @if($route->active_schedules_count === 0)
                                            <i class="bi bi-exclamation-triangle-fill text-warning ms-2" 
                                               data-bs-toggle="tooltip" 
                                               title="No active schedules"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $route->zone }}</span>
                                    </td>
                                    <td>
                                        @if($route->active_schedules_count > 0)
                                            <span class="badge" style="background-color: var(--sweep-primary);">
                                                {{ $route->active_schedules_count }} 
                                                {{ Str::plural('Schedule', $route->active_schedules_count) }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark">No Schedules</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($route->is_active)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.routes.show', $route) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.routes.edit', $route) }}" 
                                               class="btn btn-sm btn-outline-secondary"
                                               data-bs-toggle="tooltip"
                                               title="Edit Route">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $route->id }}"
                                                    title="Delete Route">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Confirmation Modal -->
                                        <div class="modal fade" id="deleteModal{{ $route->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete the route <strong>{{ $route->name }}</strong>?</p>
                                                        @if($route->active_schedules_count > 0)
                                                            <div class="alert alert-warning">
                                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                                This route has {{ $route->active_schedules_count }} active 
                                                                {{ Str::plural('schedule', $route->active_schedules_count) }}. 
                                                                You must deactivate or delete them first.
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form method="POST" action="{{ route('admin.routes.destroy', $route) }}" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete Route</button>
                                                        </form>
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
                        Showing {{ $routes->firstItem() }} to {{ $routes->lastItem() }} of {{ $routes->total() }} routes
                    </div>
                    <div>
                        {{ $routes->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-map text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No Routes Found</h4>
                    <p class="text-muted">
                        @if(request('search') || request('status'))
                            No routes match your search criteria. Try adjusting your filters.
                        @else
                            Get started by creating your first collection route.
                        @endif
                    </p>
                    @if(!request('search') && !request('status'))
                        <a href="{{ route('admin.routes.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Create First Route
                        </a>
                    @else
                        <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary mt-2">
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
