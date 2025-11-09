@section('title', 'Edit Truck')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="trucks" />
    </x-slot>

    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.trucks.index') }}">Trucks</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.trucks.show', $truck) }}">{{ $truck->truck_number }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h1 class="h2">Edit Truck: {{ $truck->truck_number }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <x-validation-errors />
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.trucks.update', $truck) }}" id="truckForm">
                        @csrf
                        @method('PUT')

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
                                value="{{ old('truck_number', $truck->truck_number) }}"
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
                                value="{{ old('license_plate', $truck->license_plate) }}"
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
                                    value="{{ old('capacity', $truck->capacity) }}"
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

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea 
                                class="form-control @error('notes') is-invalid @enderror" 
                                id="notes" 
                                name="notes" 
                                rows="4"
                                placeholder="Optional notes about this truck..."
                            >{{ old('notes', $truck->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.trucks.show', $truck) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Truck
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Current Status</h5>
                    @php
                        $statusBadge = match($truck->operational_status) {
                            'operational' => ['class' => 'bg-success', 'icon' => 'check-circle', 'text' => 'Operational'],
                            'maintenance' => ['class' => 'bg-warning text-dark', 'icon' => 'tools', 'text' => 'Maintenance'],
                            'out_of_service' => ['class' => 'bg-danger', 'icon' => 'x-circle', 'text' => 'Out of Service'],
                            default => ['class' => 'bg-secondary', 'icon' => 'question-circle', 'text' => 'Unknown']
                        };
                    @endphp
                    <div class="mb-3">
                        <span class="badge {{ $statusBadge['class'] }} fs-6">
                            <i class="bi bi-{{ $statusBadge['icon'] }}"></i> {{ $statusBadge['text'] }}
                        </span>
                    </div>
                    <p class="card-text small">
                        To change the operational status, use the "Update Status" button on the truck details page.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle"></i> Information</h5>
                    <p class="card-text">
                        Update truck information. All fields marked with 
                        <span class="text-danger">*</span> are required.
                    </p>
                    <hr>
                    <p class="small mb-0">
                        <strong>Note:</strong> The operational status cannot be changed from this form. 
                        Use the dedicated status update feature to track status changes with history.
                    </p>
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
