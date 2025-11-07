@section('title', 'Duplicate Schedule')

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
                <li class="breadcrumb-item active">Duplicate Schedule</li>
            </ol>
        </nav>
        <h1 class="h2">Duplicate Schedule</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Source Schedule Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Source Schedule Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Route</h6>
                            <p class="mb-3"><strong>{{ $schedule->route->name }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Zone</h6>
                            <p class="mb-3"><span class="badge bg-secondary">{{ $schedule->route->zone }}</span></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Collection Days</h6>
                            <p class="mb-3">
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
                                    <span class="badge" style="background-color: var(--sweep-primary); margin-right: 4px;">
                                        {{ $dayNames[$day] }}
                                    </span>
                                @endforeach
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Collection Time</h6>
                            <p class="mb-3">
                                <i class="bi bi-clock"></i> {{ $schedule->collection_time->format('g:i A') }}
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Start Date</h6>
                            <p class="mb-3">{{ $schedule->start_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">End Date</h6>
                            <p class="mb-3">
                                @if($schedule->end_date)
                                    {{ $schedule->end_date->format('M d, Y') }}
                                @else
                                    <span class="text-muted">Ongoing</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Status</h6>
                            <p class="mb-0">
                                @if($schedule->is_active)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle"></i> Inactive
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Duplication Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-files"></i> Duplicate to Another Route</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.schedules.store-duplicate', $schedule) }}" id="duplicateForm">
                        @csrf

                        <!-- Target Route Selection -->
                        <div class="mb-4">
                            <label for="target_route_id" class="form-label">
                                Target Route <span class="text-danger">*</span>
                            </label>
                            <select 
                                class="form-select @error('target_route_id') is-invalid @enderror" 
                                id="target_route_id" 
                                name="target_route_id"
                                required
                            >
                                <option value="">Select a route to duplicate to...</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}" {{ old('target_route_id') == $route->id ? 'selected' : '' }}>
                                        {{ $route->name }} ({{ $route->zone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('target_route_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Select the route where you want to duplicate this schedule. 
                                The new schedule will have the same collection days, time, and date range.
                            </div>
                        </div>

                        <!-- Conflict Warning -->
                        <div id="conflictWarning" class="alert alert-warning d-none" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Warning:</strong> Duplicating this schedule may create conflicts with existing schedules on the target route.
                        </div>

                        <!-- Information Alert -->
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong>Note:</strong> The duplicated schedule will copy all settings from the source schedule including:
                            <ul class="mb-0 mt-2">
                                <li>Collection days ({{ implode(', ', array_map(fn($d) => $dayNames[$d], $days)) }})</li>
                                <li>Collection time ({{ $schedule->collection_time->format('g:i A') }})</li>
                                <li>Start date ({{ $schedule->start_date->format('M d, Y') }})</li>
                                <li>End date ({{ $schedule->end_date ? $schedule->end_date->format('M d, Y') : 'Ongoing' }})</li>
                                <li>Active status ({{ $schedule->is_active ? 'Active' : 'Inactive' }})</li>
                            </ul>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-files"></i> Duplicate Schedule
                            </button>
                            <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Duplication Information</h5>
                </div>
                <div class="card-body">
                    <h6>What is Schedule Duplication?</h6>
                    <p class="small">Schedule duplication allows you to quickly create a new schedule with the same settings on a different route.</p>
                    
                    <h6 class="mt-3">When to Use</h6>
                    <p class="small">Use duplication when you have multiple routes with similar collection patterns. This saves time compared to creating each schedule manually.</p>

                    <h6 class="mt-3">Conflict Prevention</h6>
                    <p class="small">The system will check for conflicts on the target route. If a conflict exists, the duplication will be prevented and you'll need to adjust the existing schedules first.</p>

                    <h6 class="mt-3">After Duplication</h6>
                    <p class="small">You can edit the duplicated schedule independently. Changes to one schedule won't affect the other.</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Client-side validation
        document.getElementById('duplicateForm').addEventListener('submit', function(e) {
            const targetRouteId = document.getElementById('target_route_id').value;
            
            if (!targetRouteId) {
                e.preventDefault();
                alert('Please select a target route.');
                document.getElementById('target_route_id').focus();
                return false;
            }
            
            // Confirm duplication
            const confirmed = confirm('Are you sure you want to duplicate this schedule to the selected route?');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        });
    </script>
    @endpush
</x-app-layout>
