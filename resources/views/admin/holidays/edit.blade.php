@section('title', 'Edit Holiday')

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
            <a class="nav-link active" href="{{ route('admin.holidays.index') }}">
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
                <li class="breadcrumb-item"><a href="{{ route('admin.holidays.index') }}">Holidays</a></li>
                <li class="breadcrumb-item active">Edit Holiday</li>
            </ol>
        </nav>
        <h1 class="h2 mb-0">Edit Holiday</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.holidays.update', $holiday) }}">
                        @csrf
                        @method('PUT')

                        <!-- Holiday Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Holiday Name <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name', $holiday->name) }}"
                                placeholder="e.g., New Year's Day, Independence Day"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Holiday Date -->
                        <div class="mb-3">
                            <label for="date" class="form-label">Holiday Date <span class="text-danger">*</span></label>
                            <input 
                                type="date" 
                                class="form-control @error('date') is-invalid @enderror" 
                                id="date" 
                                name="date" 
                                value="{{ old('date', $holiday->date->format('Y-m-d')) }}"
                                required
                            >
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the date when the holiday occurs.</div>
                        </div>

                        <!-- Collection Status -->
                        <div class="mb-3">
                            <label class="form-label">Collection Status <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="is_collection_skipped" 
                                    id="collection_skipped" 
                                    value="1"
                                    {{ old('is_collection_skipped', $holiday->is_collection_skipped ? '1' : '0') == '1' ? 'checked' : '' }}
                                    onchange="toggleRescheduleDate()"
                                >
                                <label class="form-check-label" for="collection_skipped">
                                    <strong>Collection Skipped</strong>
                                    <div class="text-muted small">No waste collection will occur on this day</div>
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="is_collection_skipped" 
                                    id="collection_rescheduled" 
                                    value="0"
                                    {{ old('is_collection_skipped', $holiday->is_collection_skipped ? '1' : '0') == '0' ? 'checked' : '' }}
                                    onchange="toggleRescheduleDate()"
                                >
                                <label class="form-check-label" for="collection_rescheduled">
                                    <strong>Collection Rescheduled</strong>
                                    <div class="text-muted small">Waste collection will be moved to a different date</div>
                                </label>
                            </div>
                        </div>

                        <!-- Reschedule Date (Conditional) -->
                        <div class="mb-3" id="reschedule_date_field" style="display: {{ old('is_collection_skipped', $holiday->is_collection_skipped ? '1' : '0') == '0' ? 'block' : 'none' }};">
                            <label for="reschedule_date" class="form-label">Reschedule Date</label>
                            <input 
                                type="date" 
                                class="form-control @error('reschedule_date') is-invalid @enderror" 
                                id="reschedule_date" 
                                name="reschedule_date" 
                                value="{{ old('reschedule_date', $holiday->reschedule_date?->format('Y-m-d')) }}"
                            >
                            @error('reschedule_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select the alternate date for waste collection.</div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Holiday
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle"></i> Holiday Information
                    </h5>
                    <p class="card-text">
                        Holidays affect waste collection schedules for all routes. When you edit a holiday:
                    </p>
                    <ul class="small">
                        <li><strong>Collection Skipped:</strong> No collection occurs on this date. Residents will see this in their calendar.</li>
                        <li><strong>Collection Rescheduled:</strong> Collection is moved to an alternate date. Specify the reschedule date.</li>
                    </ul>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> Changes to holidays apply to all routes automatically.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleRescheduleDate() {
            const isSkipped = document.getElementById('collection_skipped').checked;
            const rescheduleField = document.getElementById('reschedule_date_field');
            const rescheduleInput = document.getElementById('reschedule_date');
            
            if (isSkipped) {
                rescheduleField.style.display = 'none';
                rescheduleInput.value = '';
                rescheduleInput.removeAttribute('required');
            } else {
                rescheduleField.style.display = 'block';
                rescheduleInput.setAttribute('required', 'required');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleRescheduleDate();
        });
    </script>
    @endpush
</x-app-layout>
