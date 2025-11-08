@section('title', 'Upcoming Assignments')

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
            <a class="nav-link active" href="{{ route('crew.assignments.upcoming') }}">
                <i class="bi bi-calendar-week"></i> Upcoming Assignments
            </a>
            <a class="nav-link" href="{{ route('crew.collections') }}">
                <i class="bi bi-clipboard-check"></i> Log Collection
            </a>
            <a class="nav-link" href="{{ route('crew.collections.history') }}">
                <i class="bi bi-clock-history"></i> Collection History
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
                    <i class="bi bi-calendar-week"></i> Upcoming Assignments
                </h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-calendar3"></i> Next 14 days
                </p>
            </div>
            <a href="{{ route('crew.assignments') }}" class="btn btn-outline-primary">
                <i class="bi bi-clipboard-check"></i> Today's Assignment
            </a>
        </div>

        @if($groupedAssignments->isEmpty())
            <!-- Empty State -->
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card text-center py-5 shadow-sm border-0">
                        <div class="card-body">
                            <i class="bi bi-calendar-x" style="font-size: 5rem; color: var(--sweep-accent); opacity: 0.5;"></i>
                            <h3 class="mt-4 mb-2">No Upcoming Assignments</h3>
                            <p class="text-muted mb-4 px-3">
                                You don't have any truck assignments scheduled for the next 14 days. 
                                New assignments will appear here once they are created by your administrator.
                            </p>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="{{ route('crew.assignments') }}" class="btn btn-primary">
                                    <i class="bi bi-clipboard-check"></i> View Today's Assignment
                                </a>
                                <a href="{{ route('crew.schedules.upcoming') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-calendar3"></i> View Upcoming Routes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Summary Card -->
            <div class="alert alert-info mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="alert-heading mb-1">
                            <i class="bi bi-info-circle-fill"></i> Assignment Summary
                        </h5>
                        <p class="mb-0">
                            You have <strong>{{ $groupedAssignments->sum(fn($group) => $group->count()) }}</strong> 
                            assignment{{ $groupedAssignments->sum(fn($group) => $group->count()) !== 1 ? 's' : '' }} 
                            scheduled over the next 14 days.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <span class="badge bg-white text-info fs-6 px-3 py-2">
                            <i class="bi bi-calendar-week"></i> {{ $groupedAssignments->count() }} Day{{ $groupedAssignments->count() !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Assignments Timeline -->
            <div class="row">
                <div class="col-12">
                    @foreach($groupedAssignments as $date => $assignments)
                        @php
                            $dateObj = \Carbon\Carbon::parse($date);
                            $isToday = $dateObj->isToday();
                            $isTomorrow = $dateObj->isTomorrow();
                        @endphp

                        <!-- Date Header -->
                        <div class="date-header mb-3">
                            <div class="d-flex align-items-center">
                                <div class="date-badge me-3">
                                    <div class="text-center p-3 rounded" style="background-color: var(--sweep-primary); color: white; min-width: 80px;">
                                        <div class="fw-bold" style="font-size: 1.5rem;">{{ $dateObj->format('d') }}</div>
                                        <div class="small">{{ $dateObj->format('M') }}</div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-1">
                                        {{ $dateObj->format('l, F j, Y') }}
                                        @if($isToday)
                                            <span class="badge bg-success ms-2">Today</span>
                                        @elseif($isTomorrow)
                                            <span class="badge bg-info ms-2">Tomorrow</span>
                                        @endif
                                    </h4>
                                    <p class="text-muted mb-0 small">
                                        <i class="bi bi-calendar3"></i> {{ $dateObj->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Assignments for this date -->
                        <div class="row g-3 mb-4">
                            @foreach($assignments as $assignment)
                                <div class="col-lg-6">
                                    <div class="card assignment-item h-100 shadow-sm border-0">
                                        <div class="card-body">
                                            <!-- Truck Info -->
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="truck-icon me-3">
                                                    <i class="bi bi-truck-front" style="font-size: 2.5rem; color: var(--sweep-accent);"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1 fw-bold">{{ $assignment->truck->truck_number }}</h5>
                                                    <p class="text-muted mb-0 small">
                                                        <i class="bi bi-card-text"></i> {{ $assignment->truck->license_plate }}
                                                    </p>
                                                    <p class="text-muted mb-0 small">
                                                        <i class="bi bi-box"></i> Capacity: {{ number_format($assignment->truck->capacity, 2) }} tons
                                                    </p>
                                                </div>
                                            </div>

                                            <hr class="my-3">

                                            <!-- Route Info -->
                                            <div class="route-section">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <i class="bi bi-map"></i> {{ $assignment->route->name }}
                                                        </h6>
                                                        <p class="text-muted mb-0 small">
                                                            <i class="bi bi-geo-alt"></i> Zone: {{ $assignment->route->zone }}
                                                        </p>
                                                    </div>
                                                    @if($assignment->collection_time)
                                                        <div>
                                                            <span class="badge" style="background-color: var(--sweep-accent);">
                                                                <i class="bi bi-clock"></i> {{ $assignment->collection_time->format('g:i A') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($assignment->route->description)
                                                    <p class="text-muted small mb-2 mt-2">{{ Str::limit($assignment->route->description, 100) }}</p>
                                                @endif

                                                @if($assignment->notes || $assignment->route->notes)
                                                    <div class="alert alert-warning py-2 px-3 mb-0 mt-2" style="border-left: 3px solid var(--sweep-secondary);">
                                                        <small>
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                            <strong>Note:</strong> {{ $assignment->notes ?? $assignment->route->notes }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    @if($assignment->collection_time)
                                                        <i class="bi bi-clock"></i> {{ $assignment->collection_time->format('g:i A') }}
                                                    @else
                                                        <i class="bi bi-info-circle"></i> Time TBD
                                                    @endif
                                                </small>
                                                <a href="{{ route('crew.routes.show', $assignment->route) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View Route
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .assignment-item {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .assignment-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
        }

        .date-header {
            position: relative;
            padding-left: 0;
        }

        .date-badge {
            flex-shrink: 0;
        }

        .truck-icon {
            flex-shrink: 0;
        }

        .route-section {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
        }

        /* Mobile Optimization */
        @media (max-width: 768px) {
            .date-header {
                margin-bottom: 1rem !important;
            }

            .date-badge .rounded {
                min-width: 60px !important;
                padding: 0.75rem !important;
            }

            .date-badge .fw-bold {
                font-size: 1.25rem !important;
            }

            .date-header h4 {
                font-size: 1.1rem;
            }

            .truck-icon i {
                font-size: 2rem !important;
            }

            .card-body {
                padding: 1rem;
            }

            .route-section {
                padding: 0.75rem;
            }

            .alert {
                padding: 0.5rem 0.75rem !important;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .date-header .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .date-badge {
                margin-bottom: 0.75rem;
                margin-right: 0 !important;
            }

            .col-lg-6 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }

        /* Print Styles */
        @media print {
            .sidebar, .btn, .card-footer {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
                page-break-inside: avoid;
            }

            .date-header {
                page-break-after: avoid;
            }
        }
    </style>
    @endpush
</x-app-layout>
