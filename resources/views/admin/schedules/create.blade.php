@section('title', 'Create Schedule')

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
                <li class="breadcrumb-item active">Create Schedule</li>
            </ol>
        </nav>
        <h1 class="h2">Create New Schedule</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.schedules.store') }}" id="scheduleForm">
                        @csrf

                        <!-- Route Selection -->
                        <div class="mb-3">
                            <label for="route_id" class="form-label">
                                Route <span class="text-danger">*</span>
                            </label>
                            <select 
                                class="form-select @error('route_id') is-invalid @enderror" 
                                id="route_id" 
                                name="route_id"
                                required
                            >
                                <option value="">Select a route...</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}" {{ old('route_id') == $route->id ? 'selected' : '' }}>
                                        {{ $route->name }} ({{ $route->zone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('route_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the route for this collection schedule.</div>
                        </div>

                        <!-- Days of Week Selection -->
                        <div class="mb-3">
                            <label class="form-label">
                                Collection Days <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($days as $dayValue => $dayName)
                                    <div class="form-check form-check-inline day-checkbox">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            id="day_{{ $dayValue }}" 
                                            name="days_of_week[]" 
                                            value="{{ $dayValue }}"
                                            {{ is_array(old('days_of_week')) && in_array($dayValue, old('days_of_week')) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label day-label" for="day_{{ $dayValue }}">
                                            {{ $dayName }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('days_of_week')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select at least one day for collection.</div>
                        </div>

                        <!-- Collection Time -->
                        <div class="mb-3">
                            <label for="collection_time" class="form-label">
                                Collection Time <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="time" 
                                class="form-control @error('collection_time') is-invalid @enderror" 
                                id="collection_time" 
                                name="collection_time" 
                                value="{{ old('collection_time') }}"
                                required
                            >
                            @error('collection_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Specify the time when collection occurs.</div>
                        </div>

                        <!-- Start Date -->
                        <div class="mb-3">
                            <label for="start_date" class="form-label">
                                Start Date <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="date" 
                                class="form-control @error('start_date') is-invalid @enderror" 
                                id="start_date" 
                                name="start_date" 
                                value="{{ old('start_date', date('Y-m-d')) }}"
                                min="{{ date('Y-m-d') }}"
                                required
                            >
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">The date when this schedule becomes active.</div>
                        </div>

                        <!-- End Date -->
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date (Optional)</label>
                            <input 
                                type="date" 
                                class="form-control @error('end_date') is-invalid @enderror" 
                                id="end_date" 
                                name="end_date" 
                                value="{{ old('end_date') }}"
                            >
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave blank for an ongoing schedule.</div>
                        </div>

                        <!-- Active Status -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="is_active" 
                                    name="is_active" 
                                    value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Schedule</strong>
                                    <div class="form-text">Inactive schedules are hidden from residents and crew.</div>
                                </label>
                            </div>
                        </div>

                        <!-- Conflict Warning -->
                        <div id="conflictWarning" class="alert alert-warning d-none" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Warning:</strong> This schedule may conflict with existing schedules on the same route.
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Schedule
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
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Schedule Information</h5>
                </div>
                <div class="card-body">
                    <h6>What is a Schedule?</h6>
                    <p class="small">A schedule defines when waste collection occurs on a specific route. You can set recurring collection days and times.</p>
                    
                    <h6 class="mt-3">Required Fields</h6>
                    <ul class="small">
                        <li><strong>Route:</strong> The collection route</li>
                        <li><strong>Days:</strong> At least one collection day</li>
                        <li><strong>Time:</strong> Collection time</li>
                        <li><strong>Start Date:</strong> When schedule begins</li>
                    </ul>

                    <h6 class="mt-3">Schedule Conflicts</h6>
                    <p class="small">The system will prevent creating schedules that conflict with existing schedules on the same route for the same days.</p>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .day-checkbox {
            margin: 0;
        }
        
        .day-label {
            padding: 8px 16px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }
        
        .day-checkbox input[type="checkbox"]:checked + .day-label {
            background-color: var(--sweep-primary);
            color: white;
            border-color: var(--sweep-primary);
        }
        
        .day-checkbox input[type="checkbox"] {
            display: none;
        }
        
        .day-label:hover {
            border-color: var(--sweep-primary);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Client-side validation
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            const routeId = document.getElementById('route_id').value;
            const collectionTime = document.getElementById('collection_time').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const daysChecked = document.querySelectorAll('input[name="days_of_week[]"]:checked').length;
            
            if (!routeId) {
                e.preventDefault();
                alert('Please select a route.');
                document.getElementById('route_id').focus();
                return false;
            }
            
            if (daysChecked === 0) {
                e.preventDefault();
                alert('Please select at least one collection day.');
                return false;
            }
            
            if (!collectionTime) {
                e.preventDefault();
                alert('Please specify a collection time.');
                document.getElementById('collection_time').focus();
                return false;
            }
            
            if (!startDate) {
                e.preventDefault();
                alert('Please specify a start date.');
                document.getElementById('start_date').focus();
                return false;
            }
            
            // Validate end date is after start date
            if (endDate && endDate <= startDate) {
                e.preventDefault();
                alert('End date must be after start date.');
                document.getElementById('end_date').focus();
                return false;
            }
        });

        // Update end date minimum when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            const endDateInput = document.getElementById('end_date');
            endDateInput.min = this.value;
            
            // Clear end date if it's now before start date
            if (endDateInput.value && endDateInput.value <= this.value) {
                endDateInput.value = '';
            }
        });
    </script>
    @endpush
</x-app-layout>
