@section('title', 'Edit Route')

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
                <li class="breadcrumb-item"><a href="{{ route('admin.routes.index') }}">Routes</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.routes.show', $route) }}">{{ $route->name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h1 class="h2">Edit Route: {{ $route->name }}</h1>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.routes.update', $route) }}" id="routeForm">
                        @csrf
                        @method('PUT')

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
                                value="{{ old('name', $route->name) }}"
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
                                value="{{ old('zone', $route->zone) }}"
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
                            >{{ old('description', $route->description) }}</textarea>
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
                            >{{ old('notes', $route->notes) }}</textarea>
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
                                    {{ old('is_active', $route->is_active) ? 'checked' : '' }}
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
                                <i class="bi bi-check-circle"></i> Update Route
                            </button>
                            <a href="{{ route('admin.routes.show', $route) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Route Status</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Created:</dt>
                        <dd class="col-sm-6">{{ $route->created_at->format('M d, Y') }}</dd>
                        
                        <dt class="col-sm-6">Last Updated:</dt>
                        <dd class="col-sm-6">{{ $route->updated_at->format('M d, Y') }}</dd>
                        
                        <dt class="col-sm-6">Active Schedules:</dt>
                        <dd class="col-sm-6">
                            @if($route->hasActiveSchedules())
                                <span class="badge" style="background-color: var(--sweep-primary);">
                                    {{ $route->activeSchedules()->count() }}
                                </span>
                            @else
                                <span class="badge bg-light text-dark">None</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Tips</h5>
                </div>
                <div class="card-body">
                    <h6>Editing Routes</h6>
                    <ul class="small mb-0">
                        <li>Route name must remain unique</li>
                        <li>Changing zone affects resident search</li>
                        <li>Deactivating hides route from residents</li>
                        <li>Active schedules remain unaffected</li>
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
