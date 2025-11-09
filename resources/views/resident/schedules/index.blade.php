@section('title', 'Collection Schedule')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link active" href="{{ route('resident.schedules') }}">
                <i class="bi bi-calendar3"></i> My Schedule
            </a>
            <a class="nav-link" href="{{ route('resident.reports.create') }}">
                <i class="bi bi-file-earmark-plus"></i> Submit Report
            </a>
            <a class="nav-link" href="{{ route('resident.reports') }}">
                <i class="bi bi-list-check"></i> My Reports
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h2 mb-4">
                <i class="bi bi-calendar3"></i> Collection Schedule
            </h1>

            <!-- Error Message -->
            @if(session('error') || isset($error))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') ?? $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Search Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Find Your Collection Schedule</h5>
                    <p class="text-muted mb-4">
                        Enter your zone identifier to view your waste collection schedule. 
                        Your zone can be found on your collection notice or by contacting your administrator.
                    </p>

                    <form method="GET" action="{{ route('resident.schedules.search') }}">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="zone" class="form-label">Zone Identifier</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="bi bi-geo-alt"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="zone" 
                                        name="zone" 
                                        placeholder="e.g., Zone A, Downtown, 12345"
                                        value="{{ old('zone', request('zone')) }}"
                                        required
                                        autofocus
                                    >
                                </div>
                                @error('zone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Searches -->
            @if(session()->has('recent_zones'))
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-clock-history"></i> Recent Searches
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(session('recent_zones', []) as $recentZone)
                                <a href="{{ route('resident.schedules.search', ['zone' => $recentZone]) }}" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-geo-alt"></i> {{ $recentZone }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Help Card -->
            <div class="card" style="border-left: 4px solid var(--sweep-accent);">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-question-circle"></i> How to Find Your Zone
                    </h6>
                    <ul class="mb-0 small">
                        <li>Check your collection notice or welcome letter</li>
                        <li>Look for zone markings on your waste bins</li>
                        <li>Contact your local waste management office</li>
                        <li>Ask your neighbors or community administrator</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
