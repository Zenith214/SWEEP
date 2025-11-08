@section('title', 'My Assignment Today')

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
            <a class="nav-link active" href="{{ route('crew.assignments') }}">
                <i class="bi bi-clipboard-check"></i> My Assignment
            </a>
            <a class="nav-link" href="{{ route('crew.assignments.upcoming') }}">
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
        <div class="mb-4">
            <h1 class="h2 mb-1">
                <i class="bi bi-clipboard-check"></i> My Assignment Today
            </h1>
            <p class="text-muted mb-0">
                <i class="bi bi-calendar3"></i> {{ now()->format('l, F j, Y') }}
            </p>
        </div>

        @if($assignment)
            <!-- Assignment Card -->
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="card assignment-card shadow-lg border-0">
                        <!-- Card Header with Truck Info -->
                        <div class="card-header text-white py-4" style="background: linear-gradient(135deg, var(--sweep-primary) 0%, var(--sweep-accent) 100%);">
                            <div class="text-center">
                                <i class="bi bi-truck" style="font-size: 3rem;"></i>
                                <h2 class="mt-3 mb-1 fw-bold">{{ $assignment->truck->truck_number }}</h2>
                                <p class="mb-0 opacity-90">
                                    <i class="bi bi-card-text"></i> {{ $assignment->truck->license_plate }}
                                </p>
                            </div>
                        </div>

                        <!-- Card Body with Assignment Details -->
                        <div class="card-body p-4">
                            <!-- Truck Details -->
                            <div class="mb-4">
                                <h5 class="text-muted small mb-3">
                                    <i class="bi bi-truck"></i> TRUCK DETAILS
                                </h5>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="detail-box p-3 rounded" style="background-color: #f8f9fa;">
                                            <div class="small text-muted mb-1">Capacity</div>
                                            <div class="fw-bold">{{ number_format($assignment->truck->capacity, 2) }} tons</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-box p-3 rounded" style="background-color: #f8f9fa;">
                                            <div class="small text-muted mb-1">Status</div>
                                            <div>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Operational
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Route Information -->
                            <div class="mb-4">
                                <h5 class="text-muted small mb-3">
                                    <i class="bi bi-map"></i> ROUTE INFORMATION
                                </h5>
                                <div class="route-info-box p-3 rounded" style="background-color: var(--sweep-accent); color: white;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h4 class="mb-1 fw-bold">{{ $assignment->route->name }}</h4>
                                            <p class="mb-0 opacity-90">
                                                <i class="bi bi-geo-alt"></i> Zone: {{ $assignment->route->zone }}
                                            </p>
                                        </div>
                                        @if($collectionTime)
                                            <div class="text-end">
                                                <div class="badge bg-white text-dark fs-6 px-3 py-2">
                                                    <i class="bi bi-clock"></i> {{ $collectionTime->format('g:i A') }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @if($assignment->route->description)
                                        <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.3);">
                                            <p class="mb-0 small opacity-90">{{ $assignment->route->description }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($collectionTime)
                                <!-- Collection Time Highlight -->
                                <div class="mb-4">
                                    <div class="alert alert-info mb-0" style="border-left: 4px solid var(--sweep-accent);">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clock-fill" style="font-size: 2rem; margin-right: 1rem;"></i>
                                            <div>
                                                <h6 class="alert-heading mb-1">Collection Time</h6>
                                                <p class="mb-0">Start collection at <strong>{{ $collectionTime->format('g:i A') }}</strong></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($specialInstructions)
                                <!-- Special Instructions -->
                                <div class="mb-4">
                                    <div class="alert alert-warning mb-0" style="border-left: 4px solid var(--sweep-secondary);">
                                        <h6 class="alert-heading mb-2">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Special Instructions
                                        </h6>
                                        <p class="mb-0">{{ $specialInstructions }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($assignment->notes)
                                <!-- Assignment Notes -->
                                <div class="mb-4">
                                    <h5 class="text-muted small mb-2">
                                        <i class="bi bi-sticky"></i> ASSIGNMENT NOTES
                                    </h5>
                                    <div class="p-3 rounded" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
                                        <p class="mb-0">{{ $assignment->notes }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Action Button -->
                            <div class="d-grid gap-2 mt-4">
                                <a href="{{ route('crew.routes.show', $assignment->route) }}" class="btn btn-lg btn-primary">
                                    <i class="bi bi-eye"></i> View Route Details
                                </a>
                                <a href="{{ route('crew.assignments.upcoming') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-calendar-week"></i> View Upcoming Assignments
                                </a>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer bg-light text-center py-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Assignment Date: {{ $assignment->assignment_date->format('F j, Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- No Assignment State -->
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card text-center py-5 shadow-sm border-0">
                        <div class="card-body">
                            <i class="bi bi-calendar-x" style="font-size: 5rem; color: var(--sweep-accent); opacity: 0.5;"></i>
                            <h3 class="mt-4 mb-2">No Assignment Today</h3>
                            <p class="text-muted mb-4 px-3">
                                You don't have a truck assignment for today. 
                                Check your upcoming assignments or contact your administrator if you believe this is an error.
                            </p>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="{{ route('crew.assignments.upcoming') }}" class="btn btn-primary">
                                    <i class="bi bi-calendar-week"></i> View Upcoming Assignments
                                </a>
                                <a href="{{ route('crew.schedules') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-calendar-check"></i> View Today's Routes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .assignment-card {
            transition: transform 0.3s ease;
        }

        .assignment-card:hover {
            transform: translateY(-2px);
        }

        .detail-box {
            transition: background-color 0.2s ease;
        }

        .detail-box:hover {
            background-color: #e9ecef !important;
        }

        .route-info-box {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Mobile Optimization */
        @media (max-width: 768px) {
            .card-header {
                padding: 2rem 1rem !important;
            }

            .card-header i {
                font-size: 2.5rem !important;
            }

            .card-header h2 {
                font-size: 1.75rem;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .route-info-box h4 {
                font-size: 1.25rem;
            }

            .detail-box {
                padding: 0.75rem !important;
            }

            .btn-lg {
                padding: 0.75rem 1rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .card-header i {
                font-size: 2rem !important;
            }

            .card-header h2 {
                font-size: 1.5rem;
            }

            .alert {
                padding: 0.75rem;
            }

            .alert i {
                font-size: 1.5rem !important;
                margin-right: 0.5rem !important;
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
            }
        }
    </style>
    @endpush
</x-app-layout>
