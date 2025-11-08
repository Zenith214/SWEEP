@section('title', 'Copy Assignments')

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
                <li class="breadcrumb-item"><a href="{{ route('admin.assignments.index') }}">Assignments</a></li>
                <li class="breadcrumb-item active" aria-current="page">Copy Assignments</li>
            </ol>
        </nav>
        <h1 class="h2 mb-0">Copy Assignments</h1>
        <p class="text-muted">Copy assignments from one date to another to quickly set up recurring patterns</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <x-validation-errors />
            <x-conflict-list />
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.assignments.copy') }}" id="copyForm">
                        @csrf

                        <!-- Source Date -->
                        <div class="mb-3">
                            <label for="source_date" class="form-label">
                                Source Date <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="date" 
                                class="form-control @error('source_date') is-invalid @enderror" 
                                id="source_date" 
                                name="source_date" 
                                value="{{ old('source_date', $sourceDate) }}"
                                required
                            >
                            @error('source_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the date to copy assignments from</div>
                        </div>

                        <!-- Target Date -->
                        <div class="mb-3">
                            <label for="target_date" class="form-label">
                                Target Date <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="date" 
                                class="form-control @error('target_date') is-invalid @enderror" 
                                id="target_date" 
                                name="target_date" 
                                value="{{ old('target_date') }}"
                                min="{{ now()->format('Y-m-d') }}"
                                required
                            >
                            @error('target_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the date to copy assignments to (must be different from source date)</div>
                        </div>

                        <!-- Optional Truck Filter -->
                        <div class="mb-3">
                            <label for="truck_ids" class="form-label">
                                Filter by Trucks (Optional)
                            </label>
                            <select 
                                class="form-select @error('truck_ids') is-invalid @enderror" 
                                id="truck_ids" 
                                name="truck_ids[]" 
                                multiple
                                size="5"
                            >
                                @foreach($trucks as $truck)
                                    <option 
                                        value="{{ $truck->id }}"
                                        {{ in_array($truck->id, old('truck_ids', [])) ? 'selected' : '' }}
                                    >
                                        {{ $truck->truck_number }} - {{ $truck->license_plate }}
                                    </option>
                                @endforeach
                            </select>
                            @error('truck_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty to copy all assignments, or select specific trucks to copy only their assignments</div>
                        </div>

                        <!-- Preview Section -->
                        <div id="previewSection" class="mb-3 {{ $sourceAssignments->isEmpty() ? 'd-none' : '' }}">
                            <label class="form-label">Preview of Assignments to Copy</label>
                            <div class="card">
                                <div class="card-body">
                                    <div id="previewContent">
                                        @if($sourceAssignments->isNotEmpty())
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i>
                                                Found <strong>{{ $sourceAssignments->count() }}</strong> assignment(s) on {{ \Carbon\Carbon::parse($sourceDate)->format('M d, Y') }}
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Truck</th>
                                                            <th>Crew</th>
                                                            <th>Route</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($sourceAssignments as $assignment)
                                                            <tr>
                                                                <td>{{ $assignment->truck->truck_number }}</td>
                                                                <td>{{ $assignment->user->name }}</td>
                                                                <td>{{ $assignment->route->name }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-0">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                No assignments found on the selected source date
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conflict Warning Area -->
                        @if(session('conflicts'))
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Conflicts Detected:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach(session('conflicts') as $conflict)
                                        <li>{{ $conflict }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn" {{ $sourceAssignments->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-files"></i> Copy Assignments
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
                        <i class="bi bi-info-circle"></i> Copy Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <h6>How It Works:</h6>
                    <ul class="small">
                        <li>Select a source date with existing assignments</li>
                        <li>Choose a target date to copy assignments to</li>
                        <li>Optionally filter by specific trucks</li>
                        <li>The system will create new assignments with the same truck, crew, and route combinations</li>
                    </ul>

                    <h6 class="mt-3">Conflict Handling:</h6>
                    <ul class="small">
                        <li>The system checks for conflicts before copying</li>
                        <li>If a truck or crew member is already assigned on the target date, that assignment will be skipped</li>
                        <li>You'll see a summary of successful and failed copies</li>
                    </ul>

                    <h6 class="mt-3">Use Cases:</h6>
                    <ul class="small mb-0">
                        <li>Set up weekly recurring assignment patterns</li>
                        <li>Quickly replicate a successful day's assignments</li>
                        <li>Copy assignments for specific trucks only</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightbulb"></i> Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Use the calendar view to find dates with good assignment patterns</li>
                        <li>Check truck availability on the target date first</li>
                        <li>Consider crew schedules and availability</li>
                        <li>Review the preview before copying</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sourceDateInput = document.getElementById('source_date');
            const targetDateInput = document.getElementById('target_date');
            const previewSection = document.getElementById('previewSection');
            const previewContent = document.getElementById('previewContent');
            const submitBtn = document.getElementById('submitBtn');

            // Load preview when source date changes
            sourceDateInput.addEventListener('change', loadPreview);

            async function loadPreview() {
                const sourceDate = sourceDateInput.value;
                
                if (!sourceDate) {
                    previewSection.classList.add('d-none');
                    return;
                }

                try {
                    const response = await fetch(`{{ route('admin.assignments.copy-form') }}?source_date=${sourceDate}`);
                    const html = await response.text();
                    
                    // Parse the response to extract the preview content
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newPreview = doc.getElementById('previewContent');
                    
                    if (newPreview) {
                        previewContent.innerHTML = newPreview.innerHTML;
                        previewSection.classList.remove('d-none');
                        
                        // Check if there are assignments
                        const hasAssignments = newPreview.querySelector('.table') !== null;
                        submitBtn.disabled = !hasAssignments;
                    }
                } catch (error) {
                    console.error('Error loading preview:', error);
                }
            }

            // Validate that target date is different from source date
            targetDateInput.addEventListener('change', function() {
                const sourceDate = sourceDateInput.value;
                const targetDate = targetDateInput.value;

                if (sourceDate && targetDate && sourceDate === targetDate) {
                    targetDateInput.setCustomValidity('Target date must be different from source date');
                } else {
                    targetDateInput.setCustomValidity('');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
