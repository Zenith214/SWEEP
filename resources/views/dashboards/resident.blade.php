@section('title', 'Resident Dashboard')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link active" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('resident.schedules') }}">
                <i class="bi bi-calendar3"></i> My Schedule
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-file-earmark-plus"></i> Submit Report <small>(Coming Soon)</small>
            </a>
            <a class="nav-link text-muted" href="#">
                <i class="bi bi-list-check"></i> My Reports <small>(Coming Soon)</small>
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <h1 class="h2 mb-4">Resident Dashboard</h1>

    <div class="alert alert-success mb-4" role="alert">
        <i class="bi bi-check-circle-fill"></i> Welcome, <strong>{{ $user->name }}</strong>! You are logged in as a Resident.
    </div>

    <!-- Next Collection -->
    <div class="card mb-4" style="border-left: 4px solid var(--sweep-accent);">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-calendar-event"></i> Next Collection
            </h5>
            <p class="text-muted mb-3">Stay informed about your waste collection schedule</p>
            
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle"></i> 
                To view your collection schedule, please search for your zone using the Collection Schedule feature.
            </div>
            
            <a href="{{ route('resident.schedules') }}" class="btn btn-primary">
                <i class="bi bi-search"></i> Search My Zone
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar3 fs-1 mb-3" style="color: var(--sweep-primary);"></i>
                    <h5 class="card-title">Collection Schedule</h5>
                    <p class="card-text text-muted">View your waste collection schedule</p>
                    <a href="{{ route('resident.schedules') }}" class="btn btn-primary">
                        <i class="bi bi-search"></i> Find My Schedule
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-file-earmark-plus text-secondary fs-1 mb-3"></i>
                    <h5 class="card-title">Submit Report</h5>
                    <p class="card-text text-muted">Report missed collections or issues</p>
                    <button class="btn btn-outline-secondary" disabled>Coming Soon</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-list-check text-secondary fs-1 mb-3"></i>
                    <h5 class="card-title">My Reports</h5>
                    <p class="card-text text-muted">View your submitted reports</p>
                    <button class="btn btn-outline-secondary" disabled>Coming Soon</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background-color: var(--sweep-primary); color: white;">
                    <h5 class="mb-0"><i class="bi bi-recycle"></i> Recycling Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Rinse containers before recycling</li>
                        <li>Remove caps from bottles</li>
                        <li>Flatten cardboard boxes</li>
                        <li>Keep recyclables dry and clean</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header" style="background-color: var(--sweep-accent); color: white;">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Important Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Collection Days:</strong> To be announced</p>
                    <p class="mb-2"><strong>Bin Placement:</strong> Place bins at curb by 7 AM</p>
                    <p class="mb-2"><strong>Holidays:</strong> Check schedule for holiday changes</p>
                    <p class="mb-0"><strong>Questions?</strong> Contact your administrator</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
