@section('title', 'Register Truck')

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
            <a class="nav-link active" href="{{ route('admin.trucks.index') }}">
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
                <li class="breadcrumb-item"><a href="{{ route('admin.trucks.index') }}">Trucks</a></li>
                <li class="breadcrumb-item active">Register Truck</li>
            </ol>
        </nav>
        <h1 class="h2">Register New Truck</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <x-validation-errors />
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.trucks.store') }}" id="truckForm">
                        @csrf

                        <!-- Truck Number -->
                        <div class="mb-3">
                            <label for="truck_number" class="form-label">
                                Truck Number <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('truck_number') is-invalid @enderror" 
                                id="truck_number" 
                                name="truck_number" 
                                value="{{ old('truck_number') }}"
                                placeholder="e.g., T-001"
                                required
                                maxlength="50"
                            >
                            @error('truck_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Unique identifier for the truck</div>
                        </div>

                        <!-- License Plate -->
                        <div class="mb-3">
                            <label for="license_plate" class="form-label">
                                License Plate <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('license_plate') is-invalid @enderror" 
                                id="license_plate" 
                                name="license_plate" 
                                value="{{ old('license_plate') }}"
                                placeholder="e.g., ABC-1234"
                                required
                                maxlength="50"
                            >
                            @error('license_plate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Capacity -->
                        <div class="mb-3">
                            <label for="capacity" class="form-label">
                                Capacity <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="number" 
                                    class="form-control @error('capacity') is-invalid @enderror" 
                                    id="capacity" 
                                    name="capacity" 
                                    value="{{ old('capacity') }}"
                                    placeholder="e.g., 5.5"
                                    step="0.01"
                                    min="0"
                                    required
                                >
                                <span class="input-group-text">tons</span>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Maximum load capacity in tons</div>
                        </div>

                        <!-- Operational Status -->
                        <div class="mb-3">
                            <label for="operational_status" class="form-label">
                                Operational Status <span class="text-danger">*</span>
                            </label>
                            <select 
                                class="form-select @error('operational_status') is-invalid @enderror" 
                                id="operational_status" 
                                name="operational_status"
                                required
                            >
                                <option value="operational" {{ old('operational_status', 'operational') === 'operational' ? 'selected' : '' }}>
                                    Operational
                                </option>
                                <option value="maintenance" {{ old('operational_status') === 'maintenance' ? 'selected' : '' }}>
                                    Maintenance
                                </option>
                                <option value="out_of_service" {{ old('operational_status') === 'out_of_service' ? 'selected' : '' }}>
                                    Out of Service
                                </option>
                            </select>
                            @error('operational_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea 
                                class="form-control @error('notes') is-invalid @enderror" 
                                id="notes" 
                                name="notes" 
                                rows="4"
                                placeholder="Optional notes about this truck..."
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.trucks.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Register Truck
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Information</h5>
                    <p class="card-text">
                        Register a new truck in the fleet management system. All fields marked with 
                        <span class="text-danger">*</span> are required.
                    </p>
                    <hr>
                    <h6>Status Definitions:</h6>
                    <ul class="small">
                        <li><strong>Operational:</strong> Truck is available for assignments</li>
                        <li><strong>Maintenance:</strong> Truck is undergoing maintenance</li>
                        <li><strong>Out of Service:</strong> Truck is not available</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Client-side validation
        document.getElementById('truckForm').addEventListener('submit', function(e) {
            const capacity = parseFloat(document.getElementById('capacity').value);
            
            if (capacity < 0) {
                e.preventDefault();
                alert('Capacity must be a positive number.');
                document.getElementById('capacity').focus();
                return false;
            }
            
            const truckNumber = document.getElementById('truck_number').value.trim();
            if (truckNumber.length === 0) {
                e.preventDefault();
                alert('Truck number is required.');
                document.getElementById('truck_number').focus();
                return false;
            }
            
            const licensePlate = document.getElementById('license_plate').value.trim();
            if (licensePlate.length === 0) {
                e.preventDefault();
                alert('License plate is required.');
                document.getElementById('license_plate').focus();
                return false;
            }
        });

        // Format capacity input
        document.getElementById('capacity').addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    </script>
    @endpush
</x-app-layout>
