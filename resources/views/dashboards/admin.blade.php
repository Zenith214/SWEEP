@section('title', 'Administrator Dashboard')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link active" href="{{ route('dashboard') }}">
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

    <h1 class="h2 mb-4">Administrator Dashboard</h1>

    <div class="alert alert-success mb-4" role="alert">
        <i class="bi bi-check-circle-fill"></i> Welcome back, <strong>{{ $user->name }}</strong>! You are logged in as an Administrator.
    </div>

    <!-- Assignment Alerts -->
    @if(isset($alerts) && count($alerts) > 0)
        <div class="row g-3 mb-4">
            @foreach($alerts as $alert)
                <div class="col-md-6">
                    <x-alert-card 
                        :type="$alert['type']"
                        :title="$alert['title']"
                        :count="$alert['count']"
                        :message="$alert['message']"
                        :link="$alert['link']"
                        :linkText="$alert['link_text']"
                        :bgColor="$alert['type'] === 'unassigned_routes' ? 'amber' : 'teal'"
                    />
                </div>
            @endforeach
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-white" style="background-color: var(--sweep-primary);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-white-50">Total Users</h6>
                            <h2 class="card-title mb-0">{{ \App\Models\User::count() }}</h2>
                        </div>
                        <i class="bi bi-people fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white" style="background-color: var(--sweep-secondary);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-white-50">Collection Crew</h6>
                            <h2 class="card-title mb-0">{{ \App\Models\User::role('collection_crew')->count() }}</h2>
                        </div>
                        <i class="bi bi-truck fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white" style="background-color: var(--sweep-accent);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-white-50">Residents</h6>
                            <h2 class="card-title mb-0">{{ \App\Models\User::role('resident')->count() }}</h2>
                        </div>
                        <i class="bi bi-house fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-white-50">Routes</h6>
                            <h2 class="card-title mb-0">{{ \App\Models\Route::count() }}</h2>
                        </div>
                        <i class="bi bi-map fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-person-plus fs-4 d-block mb-2"></i>
                                <strong>Add New User</strong>
                                <div class="small text-muted">Create a new user account</div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-people fs-4 d-block mb-2"></i>
                                <strong>Manage Users</strong>
                                <div class="small text-muted">View and edit user accounts</div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.routes.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-map fs-4 d-block mb-2"></i>
                                <strong>Manage Routes</strong>
                                <div class="small text-muted">View and edit collection routes</div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-secondary w-100 py-3" disabled>
                                <i class="bi bi-file-text fs-4 d-block mb-2"></i>
                                <strong>View Reports</strong>
                                <div class="small text-muted">Coming Soon</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                        <div>
                            <strong>System Online</strong>
                            <div class="small text-muted">All services operational</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                        <div>
                            <strong>Database Connected</strong>
                            <div class="small text-muted">MariaDB running</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                        <div>
                            <strong>Version 1.0.0</strong>
                            <div class="small text-muted">SWEEP Platform</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
