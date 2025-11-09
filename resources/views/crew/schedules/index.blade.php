@section('title', 'Today\'s Routes')

<x-app-layout>
    <x-slot name="sidebar">
        <x-crew-sidebar active="schedules" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-calendar-check"></i> Today's Routes
                </h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-calendar3"></i> {{ $today->format('l, F j, Y') }}
                </p>
            </div>
            <a href="{{ route('crew.schedules.upcoming') }}" class="btn btn-outline-primary">
                <i class="bi bi-calendar3"></i> View Upcoming
            </a>
        </div>

        @if($routes->isEmpty())
            <!-- Empty State -->
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-calendar-x" style="font-size: 4rem; color: var(--sweep-accent);"></i>
                            <h4 class="mt-3 mb-2">No Routes Scheduled Today</h4>
                            <p class="text-muted mb-4">
                                There are no collection routes assigned for today. 
                                Check the upcoming schedule for future assignments.
                            </p>
                            <a href="{{ route('crew.schedules.upcoming') }}" class="btn btn-primary">
                                <i class="bi bi-calendar3"></i> View Upcoming Routes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Routes Summary -->
            <div class="alert alert-info mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="alert-heading mb-1">
                            <i class="bi bi-info-circle-fill"></i> Today's Summary
                        </h5>
                        <p class="mb-0">
                            You have <strong>{{ $routes->count() }}</strong> route{{ $routes->count() !== 1 ? 's' : '' }} scheduled for collection today.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <span class="badge bg-white text-info fs-6 px-3 py-2">
                            <i class="bi bi-clock"></i> {{ $routes->count() }} Route{{ $routes->count() !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Routes Grid -->
            <div class="row g-4">
                @foreach($routes as $route)
                    @php
                        $schedule = $route->activeSchedules->first();
                        $collectionTime = $schedule ? $schedule->collection_time->format('g:i A') : 'N/A';
                    @endphp
                    
                    <div class="col-lg-6 col-xl-4">
                        <div class="card h-100 route-card">
                            <div class="card-header" style="background-color: var(--sweep-primary); color: white;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="bi bi-map"></i> {{ $route->name }}
                                        </h5>
                                        <small>
                                            <i class="bi bi-geo-alt"></i> Zone: {{ $route->zone }}
                                        </small>
                                    </div>
                                    <span class="badge bg-white text-dark">
                                        <i class="bi bi-clock"></i> {{ $collectionTime }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($route->description)
                                    <div class="mb-3">
                                        <h6 class="text-muted small mb-1">
                                            <i class="bi bi-info-circle"></i> Description
                                        </h6>
                                        <p class="mb-0">{{ $route->description }}</p>
                                    </div>
                                @endif

                                @if($route->notes)
                                    <div class="alert alert-warning mb-3" style="border-left: 4px solid var(--sweep-secondary);">
                                        <h6 class="alert-heading small mb-1">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Special Instructions
                                        </h6>
                                        <p class="mb-0 small">{{ $route->notes }}</p>
                                    </div>
                                @endif

                                @if(!$route->description && !$route->notes)
                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-info-circle"></i> No additional information available.
                                    </p>
                                @endif

                                <div class="d-grid">
                                    <a href="{{ route('crew.routes.show', $route) }}" class="btn btn-primary">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center small text-muted">
                                    <span>
                                        <i class="bi bi-calendar-check"></i> Scheduled
                                    </span>
                                    <span>
                                        <i class="bi bi-clock"></i> {{ $collectionTime }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .route-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .route-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .card-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .card-header {
                padding: 1rem;
            }

            .card-header h5 {
                font-size: 1rem;
            }

            .card-header small {
                font-size: 0.8rem;
            }

            .card-body {
                padding: 1rem;
            }

            .alert {
                padding: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .col-lg-6 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
        }
    </style>
    @endpush
</x-app-layout>
