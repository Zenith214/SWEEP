@section('title', 'Upcoming Routes')

<x-app-layout>
    <x-slot name="sidebar">
        <div class="nav flex-column">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link" href="{{ route('crew.schedules') }}">
                <i class="bi bi-calendar-check"></i> Today's Routes
            </a>
            <a class="nav-link active" href="{{ route('crew.schedules.upcoming') }}">
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
                    <i class="bi bi-calendar3"></i> Upcoming Routes
                </h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-calendar-range"></i> Next 7 Days Schedule
                </p>
            </div>
            <a href="{{ route('crew.schedules') }}" class="btn btn-outline-primary">
                <i class="bi bi-calendar-check"></i> Today's Routes
            </a>
        </div>

        @if($routesByDate->isEmpty())
            <!-- Empty State -->
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-calendar-x" style="font-size: 4rem; color: var(--sweep-accent);"></i>
                            <h4 class="mt-3 mb-2">No Upcoming Routes</h4>
                            <p class="text-muted mb-4">
                                There are no collection routes scheduled for the next 7 days.
                                Check back later or contact your administrator for more information.
                            </p>
                            <a href="{{ route('crew.schedules') }}" class="btn btn-primary">
                                <i class="bi bi-calendar-check"></i> View Today's Routes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Summary Card -->
            <div class="card mb-4" style="border-left: 4px solid var(--sweep-accent);">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">
                                <i class="bi bi-calendar-range"></i> Schedule Overview
                            </h5>
                            <p class="text-muted mb-0">
                                Showing routes scheduled for the next 7 days
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-2 mt-md-0">
                            @php
                                $totalRoutes = $routesByDate->sum(function($day) {
                                    return $day['routes']->count();
                                });
                            @endphp
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                <i class="bi bi-map"></i> {{ $totalRoutes }} Total Route{{ $totalRoutes !== 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Routes by Date -->
            @foreach($routesByDate as $dayData)
                @php
                    $date = $dayData['date'];
                    $routes = $dayData['routes'];
                    $isToday = $date->isToday();
                    $isTomorrow = $date->isTomorrow();
                @endphp

                <div class="mb-4">
                    <!-- Date Header -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="date-badge me-3">
                            <div class="date-badge-month">{{ $date->format('M') }}</div>
                            <div class="date-badge-day">{{ $date->format('d') }}</div>
                        </div>
                        <div>
                            <h4 class="mb-0">
                                {{ $date->format('l, F j, Y') }}
                                @if($isToday)
                                    <span class="badge bg-success ms-2">Today</span>
                                @elseif($isTomorrow)
                                    <span class="badge bg-info ms-2">Tomorrow</span>
                                @endif
                            </h4>
                            <p class="text-muted mb-0 small">
                                <i class="bi bi-map"></i> {{ $routes->count() }} route{{ $routes->count() !== 1 ? 's' : '' }} scheduled
                            </p>
                        </div>
                    </div>

                    <!-- Routes for this date -->
                    <div class="row g-3">
                        @foreach($routes as $routeData)
                            @php
                                $route = $routeData['route'];
                                $schedule = $routeData['schedule'];
                                $collectionTime = $schedule ? $schedule->collection_time->format('g:i A') : 'N/A';
                            @endphp

                            <div class="col-lg-6 col-xl-4">
                                <div class="card h-100 route-card-compact">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">
                                                <i class="bi bi-map text-primary"></i> {{ $route->name }}
                                            </h6>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-clock"></i> {{ $collectionTime }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-geo-alt"></i> Zone: {{ $route->zone }}
                                        </p>

                                        @if($route->notes)
                                            <div class="alert alert-warning alert-sm mb-2 p-2">
                                                <small>
                                                    <i class="bi bi-exclamation-triangle"></i> 
                                                    <strong>Note:</strong> {{ Str::limit($route->notes, 60) }}
                                                </small>
                                            </div>
                                        @endif

                                        <div class="d-grid mt-2">
                                            <a href="{{ route('crew.routes.show', $route) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(!$loop->last)
                    <hr class="my-4">
                @endif
            @endforeach
        @endif
    </div>

    @push('styles')
    <style>
        .date-badge {
            background: linear-gradient(135deg, var(--sweep-primary) 0%, var(--sweep-accent) 100%);
            color: white;
            border-radius: 8px;
            padding: 8px 12px;
            text-align: center;
            min-width: 70px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .date-badge-month {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .date-badge-day {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .route-card-compact {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .route-card-compact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            border-color: var(--sweep-accent);
        }

        .alert-sm {
            font-size: 0.85rem;
            border-left: 3px solid var(--sweep-secondary);
        }

        hr {
            border-top: 2px solid #e5e7eb;
            opacity: 1;
        }

        @media (max-width: 768px) {
            .date-badge {
                min-width: 60px;
                padding: 6px 10px;
            }

            .date-badge-month {
                font-size: 0.7rem;
            }

            .date-badge-day {
                font-size: 1.25rem;
            }

            h4 {
                font-size: 1.1rem;
            }

            .card-body {
                padding: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .col-lg-6 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }

            .d-flex.align-items-center.mb-3 {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .date-badge {
                margin-bottom: 0.5rem;
            }
        }
    </style>
    @endpush
</x-app-layout>
