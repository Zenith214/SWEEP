@section('title', 'Create Assignment')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="assignments" />
    </x-slot>

    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.assignments.index') }}">Assignments</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Assignment</li>
            </ol>
        </nav>
        <h1 class="h2 mb-0">Create Assignment</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <x-validation-errors />
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.assignments.store') }}" id="assignmentForm">
                        @csrf

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
                                        {{ old('truck_id') == $truck->id ? 'selected' : '' }}
                                    >
                                        {{ $truck->truck_number }} - {{ $truck->license_plate }} ({{ number_format($truck->capacity, 2) }} tons)
                                    </option>
                                @endforeach
                            </select>
                            @error('truck_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($trucks->isEmpty())
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> No operational trucks available. 
                                    <a href="{{ route('admin.trucks.create') }}">Register a truck</a> or update truck status.
                                </div>
                            @else
                                <div class="form-text">Only operational trucks are shown</div>
                            @endif
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
                                        {{ old('user_id') == $crew->id ? 'selected' : '' }}
                                    >
                                        {{ $crew->name }} - {{ $crew->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($crewMembers->isEmpty())
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> No collection crew members available. 
                                    <a href="{{ route('admin.users.create') }}">Create a user</a> with collection_crew role.
                                </div>
                            @else
                                <div class="form-text">Only users with collection_crew role are shown</div>
                            @endif
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
                                        {{ (old('route_id', $selectedRouteId) == $route->id) ? 'selected' : '' }}
                                    >
                                        {{ $route->name }} - {{ $route->zone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('route_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($routes->isEmpty())
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> No active routes available. 
                                    <a href="{{ route('admin.routes.create') }}">Create a route</a> first.
                                </div>
                            @else
                                <div class="form-text">Only active routes are shown</div>
                            @endif
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
                                value="{{ old('assignment_date', $selectedDate) }}"
                                min="{{ now()->format('Y-m-d') }}"
                                required
                            >
                            @error('assignment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Assignment date cannot be in the past</div>
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
                            >{{ old('notes') }}</textarea>
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
                            <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Create Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Assignment Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Requirements:</h6>
                    <ul class="small">
                        <li>Truck must be in operational status</li>
                        <li>Crew member must have collection_crew role</li>
                        <li>Route must be active</li>
                        <li>Assignment date cannot be in the past</li>
                    </ul>

                    <h6 class="mt-3">Conflict Rules:</h6>
                    <ul class="small">
                        <li>A truck can only be assigned to one route per day</li>
                        <li>A crew member can only be assigned to one route per day</li>
                        <li>The system will check for conflicts before creating the assignment</li>
                    </ul>

                    <h6 class="mt-3">Tips:</h6>
                    <ul class="small mb-0">
                        <li>Use the calendar view to see existing assignments</li>
                        <li>Check truck availability before creating assignments</li>
                        <li>Add notes for special instructions or reminders</li>
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
            const submitBtn = document.getElementById('submitBtn');

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

                try {
                    const response = await fetch('{{ route('admin.assignments.store') }}', {
                        method: 'POST',
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
