@section('title', 'Create Route')

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
                <li class="breadcrumb-item active">Create Route</li>
            </ol>
        </nav>
        <h1 class="h2">Create New Route</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.routes.store') }}" id="routeForm">
                        @csrf

                        <!-- Route Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Route Name <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('name') is-invalid @enderror" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                placeholder="e.g., Downtown Route A"
                                required
                                maxlength="255"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter a unique name for this collection route.</div>
                        </div>

                        <!-- Zone -->
                        <div class="mb-3">
                            <label for="zone" class="form-label">
                                Zone <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control @error('zone') is-invalid @enderror" 
                                id="zone" 
                                name="zone" 
                                value="{{ old('zone') }}"
                                placeholder="e.g., Zone 1A, Downtown, North District"
                                required
                                maxlength="100"
                            >
                            @error('zone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Specify the geographic area or zone identifier.</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea 
                                class="form-control @error('description') is-invalid @enderror" 
                                id="description" 
                                name="description" 
                                rows="3"
                                placeholder="Provide a brief description of this route..."
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional description of the route coverage area.</div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Special Instructions / Notes</label>
                            <textarea 
                                class="form-control @error('notes') is-invalid @enderror" 
                                id="notes" 
                                name="notes" 
                                rows="3"
                                placeholder="Add any special instructions or notes for collection crews..."
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional notes or special instructions for crews.</div>
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
                                    <strong>Active Route</strong>
                                    <div class="form-text">Inactive routes are hidden from residents and crew.</div>
                                </label>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Route
                            </button>
                            <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Route Information</h5>
                </div>
                <div class="card-body">
                    <h6>What is a Route?</h6>
                    <p class="small">A route represents a defined geographic path or zone where waste collection occurs. Each route can have multiple schedules assigned to it.</p>
                    
                    <h6 class="mt-3">Required Fields</h6>
                    <ul class="small">
                        <li><strong>Route Name:</strong> Must be unique</li>
                        <li><strong>Zone:</strong> Geographic identifier</li>
                    </ul>

                    <h6 class="mt-3">Next Steps</h6>
                    <p class="small">After creating a route, you can:</p>
                    <ul class="small">
                        <li>Create collection schedules</li>
                        <li>Assign collection days and times</li>
                        <li>View route details and history</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Client-side validation
        document.getElementById('routeForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const zone = document.getElementById('zone').value.trim();
            
            if (!name) {
                e.preventDefault();
                alert('Route name is required.');
                document.getElementById('name').focus();
                return false;
            }
            
            if (!zone) {
                e.preventDefault();
                alert('Zone is required.');
                document.getElementById('zone').focus();
                return false;
            }
            
            if (name.length > 255) {
                e.preventDefault();
                alert('Route name must not exceed 255 characters.');
                document.getElementById('name').focus();
                return false;
            }
            
            if (zone.length > 100) {
                e.preventDefault();
                alert('Zone must not exceed 100 characters.');
                document.getElementById('zone').focus();
                return false;
            }
        });

        // Character counter for name field
        const nameInput = document.getElementById('name');
        const nameHelp = nameInput.nextElementSibling.nextElementSibling;
        
        nameInput.addEventListener('input', function() {
            const remaining = 255 - this.value.length;
            if (remaining < 50) {
                nameHelp.textContent = `Enter a unique name for this collection route. (${remaining} characters remaining)`;
            }
        });

        // Character counter for zone field
        const zoneInput = document.getElementById('zone');
        const zoneHelp = zoneInput.nextElementSibling.nextElementSibling;
        
        zoneInput.addEventListener('input', function() {
            const remaining = 100 - this.value.length;
            if (remaining < 20) {
                zoneHelp.textContent = `Specify the geographic area or zone identifier. (${remaining} characters remaining)`;
            }
        });
    </script>
    @endpush
</x-app-layout>
