@section('title', 'Collection Crew Dashboard')

<x-app-layout>
    <x-slot name="sidebar">
        <x-crew-sidebar active="dashboard" />
    </x-slot>

    <h1 class="h2 mb-4">Collection Crew Dashboard</h1>

    <div class="alert alert-info mb-4" role="alert">
        <i class="bi bi-info-circle-fill"></i> Welcome, <strong>{{ $user->name }}</strong>! You are logged in as a Collection Crew member.
    </div>

    <!-- Today's Assignment -->
    <div class="card mb-4" style="border-left: 4px solid var(--sweep-secondary);">
        <div class="card-body">
            <h5 class="card-title">
                <i class="bi bi-calendar-check"></i> Today's Assignment
            </h5>
            <p class="text-muted mb-3">{{ now()->format('l, F d, Y') }}</p>
            
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle"></i> No routes assigned yet. Route management feature coming soon.
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-map text-secondary fs-1 mb-3"></i>
                    <h5 class="card-title">My Routes</h5>
                    <p class="card-text text-muted">View your assigned collection routes</p>
                    <button class="btn btn-outline-secondary" disabled>Coming Soon</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check text-secondary fs-1 mb-3"></i>
                    <h5 class="card-title">Collection Logs</h5>
                    <p class="card-text text-muted">Log completed collections</p>
                    <button class="btn btn-outline-secondary" disabled>Coming Soon</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar3 text-secondary fs-1 mb-3"></i>
                    <h5 class="card-title">Schedule</h5>
                    <p class="card-text text-muted">View your work schedule</p>
                    <button class="btn btn-outline-secondary" disabled>Coming Soon</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Activity</h5>
        </div>
        <div class="card-body">
            <p class="text-muted text-center py-4">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No recent activity to display.
            </p>
        </div>
    </div>
</x-app-layout>
