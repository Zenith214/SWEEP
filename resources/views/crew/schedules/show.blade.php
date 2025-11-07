@section('title', 'Route Details - ' . $route->name)

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('crew.schedules') }}">
                <i class="bi bi-calendar-check"></i> Today's Routes
            </a>
            <a class="nav-link" href="{{ route('crew.schedules.upcoming') }}">
                <i class="bi bi-calendar3"></i> Upcoming Routes
            </a>
            <a class="nav-link" href="{{ route('crew.assignments') }}">
                <i class="bi bi-clipboard-check"></i> My Assignment
            </a>
            <a class="nav-link" href="{{ route('crew.assignments.upcoming') }}">
                <i class="bi bi-calendar-week"></i> Upcoming Assignments
            </a>
            <hr>
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-map"></i> {{ $route->name }}
                </h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt"></i> Zone: {{ $route->zone }}
                </p>
            </div>
            <div>
                <a href="{{ route('crew.schedules.upcoming') }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <a href="{{ route('crew.schedules') }}" class="btn btn-outline-primary">
                    <i class="bi bi-calendar-check"></i> Today's Routes
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Information -->
            <div class="col-lg-8">
                <!-- Route Information Card -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: var(--sweep-primary); color: white;">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> Route Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">
                                        <i class="bi bi-map"></i> Route Name
                                    </label>
                                    <p class="mb-0 fw-semibold">{{ $route->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">
                                        <i class="bi bi-geo-alt"></i> Zone
                                    </label>
                                    <p class="mb-0 fw-semibold">{{ $route->zone }}</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">
                                        <i class="bi bi-file-text"></i> Description
                                    </label>
                                    <p class="mb-0">
                                        {{ $route->description ?: 'No description available.' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">
                                        <i class="bi bi-activity"></i> Status
                                    </label>
                                    <p class="mb-0">
                                        @if($route->is_active)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle"></i> Inactive
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions Card -->
                @if($route->notes)
                    <div class="card mb-4" style="border-left: 4px solid var(--sweep-secondary);">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle-fill text-warning"></i> Special Instructions
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $route->notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Schedule Details Card -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: var(--sweep-accent); color: white;">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar3"></i> Schedule Details
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($route->activeSchedules->isEmpty())
                            <div class="text-center py-4">
                                <i class="bi bi-calendar-x" style="font-size: 3rem; color: #dee2e6;"></i>
                                <p class="text-muted mt-3 mb-0">No active schedules for this route.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-calendar-week"></i> Collection Days</th>
                                            <th><i class="bi bi-clock"></i> Time</th>
                                            <th><i class="bi bi-calendar-range"></i> Date Range</th>
                                            <th><i class="bi bi-activity"></i> Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($route->activeSchedules as $schedule)
                                            <tr>
                                                <td>
                                                    @php
                                                        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                                        $scheduleDays = $schedule->scheduleDays->pluck('day_of_week')->toArray();
                                                    @endphp
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($scheduleDays as $dayNum)
                                                            <span class="badge bg-primary">{{ $days[$dayNum] }}</span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>{{ $schedule->collection_time->format('g:i A') }}</strong>
                                                </td>
                                                <td>
                                                    <small>
                                                        {{ $schedule->start_date->format('M j, Y') }}
                                                        @if($schedule->end_date)
                                                            <br>to {{ $schedule->end_date->format('M j, Y') }}
                                                        @else
                                                            <br><span class="text-muted">Ongoing</span>
                                                        @endif
                                                    </small>
                                                </td>
                                                <td>
                                                    @if($schedule->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Map Placeholder Card -->
                <div class="card">
                    <div class="card-header" style="background-color: #6c757d; color: white;">
                        <h5 class="mb-0">
                            <i class="bi bi-map"></i> Route Map
                        </h5>
                    </div>
                    <div class="card-body text-center py-5" style="background-color: #f8f9fa;">
                        <i class="bi bi-map" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h5 class="mt-3 mb-2 text-muted">Map View Coming Soon</h5>
                        <p class="text-muted mb-0">
                            Interactive route mapping will be available in a future update.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Next Collection Card -->
                @if($nextCollection)
                    <div class="card mb-4" style="border-left: 4px solid var(--sweep-primary);">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-calendar-check-fill"></i> Next Collection
                            </h6>
                            <div class="text-center py-3">
                                <div class="display-6 fw-bold text-primary mb-2">
                                    {{ \Carbon\Carbon::parse($nextCollection['date'])->format('M j') }}
                                </div>
                                <p class="mb-1">
                                    {{ \Carbon\Carbon::parse($nextCollection['date'])->format('l, Y') }}
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($nextCollection['time'])->format('g:i A') }}
                                </p>
                                @php
                                    $daysUntil = \Carbon\Carbon::parse($nextCollection['date'])->diffInDays(now(), false);
                                    $daysUntil = abs($daysUntil);
                                @endphp
                                <div class="mt-3">
                                    @if(\Carbon\Carbon::parse($nextCollection['date'])->isToday())
                                        <span class="badge bg-success fs-6 px-3 py-2">Today</span>
                                    @elseif(\Carbon\Carbon::parse($nextCollection['date'])->isTomorrow())
                                        <span class="badge bg-info fs-6 px-3 py-2">Tomorrow</span>
                                    @else
                                        <span class="badge bg-primary fs-6 px-3 py-2">In {{ $daysUntil }} days</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quick Actions Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-lightning-fill"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('crew.schedules') }}" class="btn btn-outline-primary">
                                <i class="bi bi-calendar-check"></i> Today's Routes
                            </a>
                            <a href="{{ route('crew.schedules.upcoming') }}" class="btn btn-outline-primary">
                                <i class="bi bi-calendar3"></i> Upcoming Routes
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card" style="border-left: 4px solid var(--sweep-accent);">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-question-circle"></i> Need Help?
                        </h6>
                        <p class="small text-muted mb-3">
                            If you have questions about this route or need to report an issue, 
                            please contact your supervisor or administrator.
                        </p>
                        <div class="d-grid">
                            <button class="btn btn-sm btn-outline-secondary" disabled>
                                <i class="bi bi-telephone"></i> Contact Support
                                <small class="d-block">(Coming Soon)</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .info-item {
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid var(--sweep-accent);
        }

        .info-item label {
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.9rem;
            border-bottom: 2px solid #dee2e6;
        }

        .badge {
            font-weight: 500;
        }

        @media (max-width: 992px) {
            .col-lg-4 {
                margin-top: 1rem;
            }
        }

        @media (max-width: 768px) {
            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .d-flex.justify-content-between.align-items-center.mb-4 > div:last-child {
                margin-top: 1rem;
                width: 100%;
            }

            .d-flex.justify-content-between.align-items-center.mb-4 > div:last-child .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .info-item {
                padding: 0.5rem;
            }

            .card-body {
                padding: 1rem;
            }
        }
    </style>
    @endpush
</x-app-layout>
