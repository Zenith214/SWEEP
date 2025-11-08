@section('title', 'Route Details')

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
                <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                <li class="breadcrumb-item active">{{ $route->name }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h2 mb-0">{{ $route->name }}</h1>
            <div class="btn-group">
                <a href="{{ route('admin.routes.edit', $route) }}" class="btn btn-secondary">
                    <i class="bi bi-pencil"></i> Edit Route
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Route Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Route Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Route Name:</dt>
                        <dd class="col-sm-9">{{ $route->name }}</dd>

                        <dt class="col-sm-3">Zone:</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-secondary">{{ $route->zone }}</span>
                        </dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            @if($route->is_active)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Active
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Inactive
                                </span>
                            @endif
                        </dd>

                        @if($route->description)
                            <dt class="col-sm-3">Description:</dt>
                            <dd class="col-sm-9">{{ $route->description }}</dd>
                        @endif

                        @if($route->notes)
                            <dt class="col-sm-3">Special Instructions:</dt>
                            <dd class="col-sm-9">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle-fill"></i> {{ $route->notes }}
                                </div>
                            </dd>
                        @endif

                        <dt class="col-sm-3">Created:</dt>
                        <dd class="col-sm-9">{{ $route->created_at->format('F d, Y \a\t g:i A') }}</dd>

                        <dt class="col-sm-3">Last Updated:</dt>
                        <dd class="col-sm-9">{{ $route->updated_at->format('F d, Y \a\t g:i A') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Schedules Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar"></i> Collection Schedules</h5>
                    <button class="btn btn-sm btn-primary" disabled>
                        <i class="bi bi-plus-circle"></i> Create Schedule
                    </button>
                </div>
                <div class="card-body">
                    @if($route->schedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Collection Days</th>
                                        <th>Time</th>
                                        <th>Date Range</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($route->schedules as $schedule)
                                        <tr>
                                            <td>
                                                @php
                                                    $days = $schedule->scheduleDays->sortBy('day_of_week');
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
                                                    <span class="badge" style="background-color: var(--sweep-primary);">
                                                        {{ $dayNames[$day->day_of_week] }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <i class="bi bi-clock"></i> 
                                                {{ $schedule->collection_time->format('g:i A') }}
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
                                                    @if(!$schedule->end_date || $schedule->end_date->isFuture())
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-warning">Ended</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" disabled>
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary" disabled>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" disabled>
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No Schedules Assigned</h5>
                            <p class="text-muted">This route doesn't have any collection schedules yet.</p>
                            <button class="btn btn-primary" disabled>
                                <i class="bi bi-plus-circle"></i> Create First Schedule
                            </button>
                            <p class="text-muted small mt-2">Schedule management coming soon</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Stats Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <div class="text-muted small">Total Schedules</div>
                            <h3 class="mb-0">{{ $route->schedules->count() }}</h3>
                        </div>
                        <i class="bi bi-calendar fs-2 text-muted"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <div class="text-muted small">Active Schedules</div>
                            <h3 class="mb-0">{{ $route->activeSchedules->count() }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-2" style="color: var(--sweep-primary);"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Next Collection</div>
                            @php
                                $nextDate = $route->getNextCollectionDate();
                            @endphp
                            @if($nextDate)
                                <strong>{{ $nextDate->format('M d, Y') }}</strong>
                                <div class="small text-muted">{{ $nextDate->diffForHumans() }}</div>
                            @else
                                <span class="text-muted">Not scheduled</span>
                            @endif
                        </div>
                        <i class="bi bi-calendar-event fs-2" style="color: var(--sweep-accent);"></i>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.routes.edit', $route) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Edit Route Details
                        </a>
                        <button class="btn btn-outline-primary" disabled>
                            <i class="bi bi-plus-circle"></i> Add Schedule
                        </button>
                        <button class="btn btn-outline-info" disabled>
                            <i class="bi bi-files"></i> Duplicate Route
                        </button>
                        <hr>
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash"></i> Delete Route
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Route Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the route <strong>{{ $route->name }}</strong>?</p>
                    
                    @if($route->hasActiveSchedules())
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Cannot Delete!</strong><br>
                            This route has {{ $route->activeSchedules->count() }} active 
                            {{ Str::plural('schedule', $route->activeSchedules->count()) }}. 
                            You must deactivate or delete them first.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            This action cannot be undone. The route will be permanently removed from the system.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if(!$route->hasActiveSchedules())
                        <form method="POST" action="{{ route('admin.routes.destroy', $route) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Delete Route
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
