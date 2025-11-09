@section('title', 'Schedules Management')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="schedules" />
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Schedules Management</h1>
        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Schedule
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.schedules.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label for="route_id" class="form-label">Filter by Route</label>
                    <select class="form-select" id="route_id" name="route_id">
                        <option value="">All Routes</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                                {{ $route->name }} ({{ $route->zone }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Schedules</option>
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

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-body">
            @if($schedules->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Route</th>
                                <th>Zone</th>
                                <th>Days</th>
                                <th>Time</th>
                                <th>Date Range</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td>
                                        <strong>{{ $schedule->route->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $schedule->route->zone }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $days = $schedule->getDaysOfWeek();
                                            $dayNames = [
                                                0 => 'Sun',
                                                1 => 'Mon',
                                                2 => 'Tue',
                                                3 => 'Wed',
                                                4 => 'Thu',
                                                5 => 'Fri',
                                                6 => 'Sat'
                                            ];
                                        @endphp
                                        @foreach($days as $day)
                                            <span class="badge" style="background-color: var(--sweep-primary); margin-right: 2px;">
                                                {{ $dayNames[$day] }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <i class="bi bi-clock"></i> {{ $schedule->collection_time->format('g:i A') }}
                                    </td>
                                    <td>
                                        <small>
                                            {{ $schedule->start_date->format('M d, Y') }}
                                            @if($schedule->end_date)
                                                <br>to {{ $schedule->end_date->format('M d, Y') }}
                                            @else
                                                <br><span class="text-muted">Ongoing</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if($schedule->is_active)
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
                                            <a href="{{ route('admin.schedules.show', $schedule) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.schedules.edit', $schedule) }}" 
                                               class="btn btn-sm btn-outline-secondary"
                                               data-bs-toggle="tooltip"
                                               title="Edit Schedule">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('admin.schedules.duplicate', $schedule) }}" 
                                               class="btn btn-sm btn-outline-info"
                                               data-bs-toggle="tooltip"
                                               title="Duplicate Schedule">
                                                <i class="bi bi-files"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-{{ $schedule->is_active ? 'warning' : 'success' }} toggle-active-btn"
                                                    data-schedule-id="{{ $schedule->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ $schedule->is_active ? 'Deactivate' : 'Activate' }} Schedule">
                                                <i class="bi bi-{{ $schedule->is_active ? 'pause' : 'play' }}-circle"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $schedule->id }}"
                                                    title="Delete Schedule">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Confirmation Modal -->
                                        <div class="modal fade" id="deleteModal{{ $schedule->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete this schedule for <strong>{{ $schedule->route->name }}</strong>?</p>
                                                        <p class="text-muted small">This action cannot be undone.</p>
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
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $schedules->firstItem() }} to {{ $schedules->lastItem() }} of {{ $schedules->total() }} schedules
                    </div>
                    <div>
                        {{ $schedules->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No Schedules Found</h4>
                    <p class="text-muted">
                        @if(request('route_id') || request('status'))
                            No schedules match your filter criteria. Try adjusting your filters.
                        @else
                            Get started by creating your first collection schedule.
                        @endif
                    </p>
                    @if(!request('route_id') && !request('status'))
                        <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Create First Schedule
                        </a>
                    @else
                        <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary mt-2">
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

            // Handle toggle active button clicks
            document.querySelectorAll('.toggle-active-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    const scheduleId = this.getAttribute('data-schedule-id');
                    const btn = this;
                    
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
            });
        });
    </script>
    @endpush
</x-app-layout>
