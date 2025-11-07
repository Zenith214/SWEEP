@section('title', 'Edit Assignment')

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
                <li class="breadcrumb-item"><a href="{{ route('admin.assignments.show', $assignment) }}">Assignment #{{ $assignment->id }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h1 class="h2 mb-0">Edit Assignment</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.assignments.update', $assignment) }}" id="assignmentForm">
                        @csrf
                        @method('PUT')

                        <!-- Truck Selection -->
                        <div class="mb-3">
                            <label for="truck_id" class="form-label">
                                Truck <span class="text-danger">*</span>
                            </label>
                            <select 
                                class="form-select @error('truck_id') is-invalid @enderror" 
                                id="truck_id" 
                                name="truck_id" 
                                required
                            >
                                <option value="">Select a truck...</option>
                                @foreach($trucks as $truck)
                                    <option 
                                        value="{{ $truck->id }}" 
                                        {{ old('truck_id', $assignment->truck_id) == $truck->id ? 'selected' : '' }}
                                    >
                                        {{ $truck->truck_number }} - {{ $truck->license_plate }} ({{ number_format($truck->capacity, 2) }} tons)
                                    </option>
                                @endforeach
                            </select>
                            @error('truck_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Only operational trucks are shown</div>
                        </div>

                        <!-- Crew Member Selection -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">
                                Crew Member <span class="text-danger">*</span>
                            </label>
                            <select 
                                class="form-select @error('user_id') is-invalid @enderror" 
                                id="user_id" 
                                name="user_id" 
                                required
                            >
                                <option value="">Select a crew member...</option>
                                @foreach($crewMembers as $crew)
                                    <option 
                                        value="{{ $crew->id }}" 
                                        {{ old('user_id', $assignment->user_id) == $crew->id ? 'selected' : '' }}
                                    >
                                        {{ $crew->name }} - {{ $crew->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Only users with collection_crew role are shown</div>
                        </div>

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
                                    <option 
                                        value="{{ $route->id }}" 
                                        {{ old('route_id', $assignment->route_id) == $route->id ? 'selected' : '' }}
                                    >
                                        {{ $route->name }} - {{ $route->zone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('route_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Only active routes are shown</div>
                        </div>

                        <!-- Assignment Date -->
                        <div class="mb-3">
                            <label for="assignment_date" class="form-label">
                                Assignment Date <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="date" 
                                class="form-control @error('assignment_date') is-invalid @enderror" 
                                id="assignment_date" 
                                name="assignment_date" 
                                value="{{ old('assignment_date', $assignment->assignment_date->format('Y-m-d')) }}"
                                required
                            >
                            @error('assignment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Past dates are allowed when editing existing assignments</div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea 
                                class="form-control @error('notes') is-invalid @enderror" 
                                id="notes" 
                                name="notes" 
                                rows="3"
                                placeholder="Optional notes about this assignment..."
                            >{{ old('notes', $assignment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Conflict Warning Area -->
                        <div id="conflictWarning" class="alert alert-warning d-none" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Conflict Detected:</strong>
                            <ul id="conflictList" class="mb-0 mt-2"></ul>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Update Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Assignment Info
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

                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7">{{ $assignment->created_at->format('M d, Y') }}</dd>

                        <dt class="col-sm-5">Last Updated:</dt>
                        <dd class="col-sm-7 mb-0">{{ $assignment->updated_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Edit Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Conflict Rules:</h6>
                    <ul class="small">
                        <li>A truck can only be assigned to one route per day</li>
                        <li>A crew member can only be assigned to one route per day</li>
                        <li>The system will check for conflicts before updating</li>
                    </ul>

                    <h6 class="mt-3">Notes:</h6>
                    <ul class="small mb-0">
                        <li>Past dates are allowed when editing</li>
                        <li>Changes will be reflected immediately</li>
                        <li>Consider notifying the crew member of changes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('assignmentForm');
            const truckSelect = document.getElementById('truck_id');
            const userSelect = document.getElementById('user_id');
            const dateInput = document.getElementById('assignment_date');
            const conflictWarning = document.getElementById('conflictWarning');
            const conflictList = document.getElementById('conflictList');

            // Check for conflicts when relevant fields change
            let checkTimeout;
            function scheduleConflictCheck() {
                clearTimeout(checkTimeout);
                checkTimeout = setTimeout(checkConflicts, 500);
            }

            truckSelect.addEventListener('change', scheduleConflictCheck);
            userSelect.addEventListener('change', scheduleConflictCheck);
            dateInput.addEventListener('change', scheduleConflictCheck);

            async function checkConflicts() {
                const truckId = truckSelect.value;
                const userId = userSelect.value;
                const date = dateInput.value;

                // Clear previous warnings
                conflictWarning.classList.add('d-none');
                conflictList.innerHTML = '';

                // Only check if all required fields are filled
                if (!truckId || !userId || !date) {
                    return;
                }

                // Skip check if values haven't changed
                const originalTruckId = '{{ $assignment->truck_id }}';
                const originalUserId = '{{ $assignment->user_id }}';
                const originalDate = '{{ $assignment->assignment_date->format('Y-m-d') }}';

                if (truckId === originalTruckId && userId === originalUserId && date === originalDate) {
                    return;
                }

                try {
                    const response = await fetch('{{ route('admin.assignments.update', $assignment) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            truck_id: truckId,
                            user_id: userId,
                            assignment_date: date,
                            _check_only: true
                        })
                    });

                    const data = await response.json();

                    if (data.conflicts && data.conflicts.length > 0) {
                        conflictList.innerHTML = data.conflicts.map(c => `<li>${c}</li>`).join('');
                        conflictWarning.classList.remove('d-none');
                    }
                } catch (error) {
                    // Silently fail - validation will catch issues on submit
                    console.error('Conflict check error:', error);
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
