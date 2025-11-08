@section('title', 'Unassigned Routes')

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
            <a class="nav-link" href="{{ route('admin.assignments.index') }}">
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
            <a class="nav-link active" href="{{ route('admin.assignments.unassigned-routes') }}">
                <i class="bi bi-exclamation-triangle"></i> Unassigned Routes
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
        <h1 class="h2 mb-0">
            <i class="bi bi-exclamation-triangle text-warning"></i> Unassigned Routes
            @if($unassignedRoutes->count() > 0)
                <span class="badge bg-warning text-dark ms-2">{{ $unassignedRoutes->count() }}</span>
            @endif
        </h1>
        <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Assignments
        </a>
    </div>

    <!-- Date Range Selector Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.assignments.unassigned-routes') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="start_date" 
                        name="start_date" 
                        value="{{ $startDate->format('Y-m-d') }}"
                    >
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="end_date" 
                        name="end_date" 
                        value="{{ $endDate->format('Y-m-d') }}"
                    >
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Apply Filter
                    </button>
                </div>
            </form>
            <div class="mt-3">
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.assignments.unassigned-routes', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->addDays(7)->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        Next 7 Days
                    </a>
                    <a href="{{ route('admin.assignments.unassigned-routes', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->addDays(14)->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        Next 14 Days
                    </a>
                    <a href="{{ route('admin.assignments.unassigned-routes', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->addDays(30)->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        Next 30 Days
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Info -->
    @if($unassignedRoutes->count() > 0)
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Action Required:</strong> 
                There {{ $unassignedRoutes->count() === 1 ? 'is' : 'are' }} 
                <strong>{{ $unassignedRoutes->count() }}</strong> 
                scheduled collection{{ $unassignedRoutes->count() === 1 ? '' : 's' }} without truck assignments 
                between {{ $startDate->format('M j, Y') }} and {{ $endDate->format('M j, Y') }}.
            </div>
        </div>
    @endif

    <!-- Unassigned Routes List -->
    <div class="card">
        <div class="card-header" style="background-color: var(--sweep-accent); color: white;">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> Routes Without Assignments
            </h5>
        </div>
        <div class="card-body">
            @if($unassignedRoutes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Route Name</th>
                                <th>Zone</th>
                                <th>Collection Time</th>
                                <th>Schedule Type</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unassignedRoutes as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item['date']->format('D, M j, Y') }}</strong>
                                        @if($item['date']->isToday())
                                            <span class="badge bg-danger ms-1">Today</span>
                                        @elseif($item['date']->isTomorrow())
                                            <span class="badge bg-warning text-dark ms-1">Tomorrow</span>
                                        @elseif($item['date']->diffInDays(now()) <= 3)
                                            <span class="badge bg-warning text-dark ms-1">{{ $item['date']->diffInDays(now()) }} days</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $item['route']->name }}</strong>
                                        @if($item['route']->description)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($item['route']->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: var(--sweep-primary);">
                                            {{ $item['route']->zone }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item['collection_time'])
                                            <i class="bi bi-clock"></i> 
                                            {{ \Carbon\Carbon::parse($item['collection_time'])->format('g:i A') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($item['schedule']->frequency) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.assignments.create', ['route_id' => $item['route']->id, 'date' => $item['date']->format('Y-m-d')]) }}" 
                                           class="btn btn-sm btn-primary"
                                           data-bs-toggle="tooltip"
                                           title="Create assignment for this route">
                                            <i class="bi bi-plus-circle"></i> Create Assignment
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-success">All Routes Assigned!</h4>
                    <p class="text-muted">
                        All scheduled routes between {{ $startDate->format('M j, Y') }} and {{ $endDate->format('M j, Y') }} 
                        have truck assignments.
                    </p>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-calendar"></i> View Assignment Calendar
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if($unassignedRoutes->count() > 0)
        <!-- Quick Actions Card -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-lightning-charge"></i> Quick Actions
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('admin.truck-availability.index', ['date' => $startDate->format('Y-m-d')]) }}" 
                           class="btn btn-outline-primary w-100 mb-2">
                            <i class="bi bi-truck"></i> Check Truck Availability
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('admin.assignments.copy-form') }}" 
                           class="btn btn-outline-secondary w-100 mb-2">
                            <i class="bi bi-files"></i> Copy Assignments from Another Date
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
