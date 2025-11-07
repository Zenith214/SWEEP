@section('title', 'Truck Availability')

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
            <a class="nav-link active" href="{{ route('admin.truck-availability.index') }}">
                <i class="bi bi-calendar-check"></i> Truck Availability
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Truck Availability</h1>
    </div>

    <!-- Date Selector Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.truck-availability.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date" class="form-label">Select Date</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="date" 
                        name="date" 
                        value="{{ $selectedDate->format('Y-m-d') }}"
                        onchange="this.form.submit()"
                    >
                </div>
                <div class="col-md-8">
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.truck-availability.index', ['date' => now()->format('Y-m-d')]) }}" 
                           class="btn btn-outline-secondary {{ $selectedDate->isToday() ? 'active' : '' }}">
                            Today
                        </a>
                        <a href="{{ route('admin.truck-availability.index', ['date' => now()->addDay()->format('Y-m-d')]) }}" 
                           class="btn btn-outline-secondary {{ $selectedDate->isTomorrow() ? 'active' : '' }}">
                            Tomorrow
                        </a>
                        <a href="{{ route('admin.truck-availability.index', ['date' => now()->addDays(7)->format('Y-m-d')]) }}" 
                           class="btn btn-outline-secondary">
                            Next Week
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-3">
            <h4>{{ $selectedDate->format('l, F j, Y') }}</h4>
        </div>
    </div>

    <!-- Operational Trucks -->
    <div class="card mb-4">
        <div class="card-header" style="background-color: var(--sweep-primary); color: white;">
            <h5 class="mb-0">
                <i class="bi bi-truck"></i> Operational Trucks
                <span class="badge bg-light text-dark ms-2">{{ count($availability['operational']) }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if(count($availability['operational']) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Truck Number</th>
                                <th>License Plate</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Assignment Details</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($availability['operational'] as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item['truck']->truck_number }}</strong>
                                    </td>
                                    <td>{{ $item['truck']->license_plate }}</td>
                                    <td>{{ number_format($item['truck']->capacity, 2) }} tons</td>
                                    <td>
                                        @if($item['is_available'])
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Available
                                            </span>
                                        @else
                                            <span class="badge" style="background-color: var(--sweep-accent);">
                                                <i class="bi bi-clipboard-check"></i> Assigned
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$item['is_available'] && $item['assignment'])
                                            <div>
                                                <strong>Route:</strong> {{ $item['assignment']->route->name }}
                                            </div>
                                            <div class="text-muted small">
                                                <strong>Crew:</strong> {{ $item['assignment']->user->name }}
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($item['is_available'])
                                            <a href="{{ route('admin.assignments.create', ['truck_id' => $item['truck']->id, 'date' => $selectedDate->format('Y-m-d')]) }}" 
                                               class="btn btn-sm btn-primary"
                                               data-bs-toggle="tooltip"
                                               title="Create Assignment">
                                                <i class="bi bi-plus-circle"></i> Assign
                                            </a>
                                        @else
                                            <a href="{{ route('admin.assignments.show', $item['assignment']) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               data-bs-toggle="tooltip"
                                               title="View Assignment">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-truck text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No operational trucks available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Maintenance Trucks -->
    @if(count($availability['maintenance']) > 0)
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0">
                    <i class="bi bi-wrench"></i> Trucks in Maintenance
                    <span class="badge bg-dark ms-2">{{ count($availability['maintenance']) }}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Truck Number</th>
                                <th>License Plate</th>
                                <th>Capacity</th>
                                <th>Notes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($availability['maintenance'] as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item['truck']->truck_number }}</strong>
                                    </td>
                                    <td>{{ $item['truck']->license_plate }}</td>
                                    <td>{{ number_format($item['truck']->capacity, 2) }} tons</td>
                                    <td>
                                        @if($item['truck']->notes)
                                            <span class="text-muted">{{ Str::limit($item['truck']->notes, 50) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.trucks.show', $item['truck']) }}" 
                                           class="btn btn-sm btn-outline-secondary"
                                           data-bs-toggle="tooltip"
                                           title="View Truck Details">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Out of Service Trucks -->
    @if(count($availability['out_of_service']) > 0)
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-x-circle"></i> Trucks Out of Service
                    <span class="badge bg-dark ms-2">{{ count($availability['out_of_service']) }}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Truck Number</th>
                                <th>License Plate</th>
                                <th>Capacity</th>
                                <th>Notes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($availability['out_of_service'] as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item['truck']->truck_number }}</strong>
                                    </td>
                                    <td>{{ $item['truck']->license_plate }}</td>
                                    <td>{{ number_format($item['truck']->capacity, 2) }} tons</td>
                                    <td>
                                        @if($item['truck']->notes)
                                            <span class="text-muted">{{ Str::limit($item['truck']->notes, 50) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.trucks.show', $item['truck']) }}" 
                                           class="btn btn-sm btn-outline-secondary"
                                           data-bs-toggle="tooltip"
                                           title="View Truck Details">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
