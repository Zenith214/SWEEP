@section('title', 'Collection Schedule - ' . $zone)

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
        <div class="col-lg-10">
            <!-- Header with Back Button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="bi bi-calendar3"></i> Collection Schedule for {{ $zone }}
                </h1>
                <a href="{{ route('resident.schedules') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> New Search
                </a>
            </div>

            <!-- Next Collection Card -->
            @php
                $nextCollections = $routes->filter(function($item) {
                    return $item['next_collection'] !== null;
                })->sortBy(function($item) {
                    return $item['next_collection']['date'];
                });
                
                $soonestCollection = $nextCollections->first();
            @endphp

            @if($soonestCollection)
                <div class="card mb-4" style="border-left: 5px solid var(--sweep-primary); background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="card-title mb-2">
                                    <i class="bi bi-calendar-check-fill" style="color: var(--sweep-primary);"></i>
                                    Next Collection
                                </h5>
                                <h2 class="mb-2" style="color: var(--sweep-primary);">
                                    {{ $soonestCollection['next_collection']['date']->format('l, F j, Y') }}
                                </h2>
                                <p class="mb-0 text-muted">
                                    <i class="bi bi-clock"></i> 
                                    Collection Time: {{ $soonestCollection['next_collection']['time']->format('g:i A') }}
                                    <br>
                                    <i class="bi bi-map"></i>
                                    Route: {{ $soonestCollection['route']->name }}
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="badge bg-success fs-6 px-3 py-2">
                                    @php
                                        $daysUntil = now()->diffInDays($soonestCollection['next_collection']['date'], false);
                                    @endphp
                                    @if($daysUntil == 0)
                                        Today
                                    @elseif($daysUntil == 1)
                                        Tomorrow
                                    @else
                                        In {{ $daysUntil }} days
                                    @endif
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('resident.schedules.calendar', ['zone' => $zone]) }}" 
                                       class="btn btn-primary">
                                        <i class="bi bi-calendar3"></i> View Calendar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Collection Routes -->
            <div class="card">
                <div class="card-header" style="background-color: var(--sweep-primary); color: white;">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Collection Routes in Your Zone
                    </h5>
                </div>
                <div class="card-body">
                    @if($routes->count() > 0)
                        <div class="row g-4">
                            @foreach($routes as $item)
                                @php
                                    $route = $item['route'];
                                    $nextCollection = $item['next_collection'];
                                    $schedules = $route->activeSchedules;
                                @endphp

                                <div class="col-md-6">
                                    <div class="card h-100" style="border-left: 3px solid var(--sweep-accent);">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="bi bi-map"></i> {{ $route->name }}
                                            </h5>
                                            
                                            @if($route->description)
                                                <p class="text-muted small mb-3">{{ $route->description }}</p>
                                            @endif

                                            <!-- Collection Days and Times -->
                                            @foreach($schedules as $schedule)
                                                <div class="mb-3">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-clock me-2" style="color: var(--sweep-accent);"></i>
                                                        <strong>{{ $schedule->collection_time->format('g:i A') }}</strong>
                                                    </div>
                                                    
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @php
                                                            $days = $schedule->getDaysOfWeek();
                                                            $dayNames = [
                                                                0 => 'Sunday',
                                                                1 => 'Monday',
                                                                2 => 'Tuesday',
                                                                3 => 'Wednesday',
                                                                4 => 'Thursday',
                                                                5 => 'Friday',
                                                                6 => 'Saturday'
                                                            ];
                                                        @endphp
                                                        @foreach($days as $day)
                                                            <span class="badge" style="background-color: var(--sweep-primary);">
                                                                {{ $dayNames[$day] }}
                                                            </span>
                                                        @endforeach
                                                    </div>

                                                    @if($schedule->start_date || $schedule->end_date)
                                                        <div class="text-muted small mt-2">
                                                            <i class="bi bi-calendar-range"></i>
                                                            @if($schedule->start_date)
                                                                From {{ $schedule->start_date->format('M d, Y') }}
                                                            @endif
                                                            @if($schedule->end_date)
                                                                to {{ $schedule->end_date->format('M d, Y') }}
                                                            @else
                                                                (Ongoing)
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach

                                            <!-- Next Collection for this Route -->
                                            @if($nextCollection)
                                                <div class="alert alert-success mb-0 mt-3">
                                                    <small>
                                                        <i class="bi bi-calendar-check"></i>
                                                        <strong>Next Collection:</strong><br>
                                                        {{ $nextCollection['date']->format('l, M j') }} at {{ $nextCollection['time']->format('g:i A') }}
                                                    </small>
                                                </div>
                                            @else
                                                <div class="alert alert-warning mb-0 mt-3">
                                                    <small>
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                        No upcoming collections scheduled
                                                    </small>
                                                </div>
                                            @endif

                                            @if($route->notes)
                                                <div class="mt-3 p-2 bg-light rounded">
                                                    <small>
                                                        <i class="bi bi-info-circle"></i>
                                                        <strong>Special Instructions:</strong><br>
                                                        {{ $route->notes }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Calendar Link -->
                        <div class="text-center mt-4">
                            <a href="{{ route('resident.schedules.calendar', ['zone' => $zone]) }}" 
                               class="btn btn-primary btn-lg">
                                <i class="bi bi-calendar3"></i> View Full Calendar
                            </a>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">No Active Schedules</h4>
                            <p class="text-muted">
                                There are currently no active collection schedules for this zone.
                                Please contact your administrator for more information.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card mt-4" style="border-left: 4px solid var(--sweep-secondary);">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="bi bi-lightbulb"></i> Collection Tips
                    </h6>
                    <ul class="mb-0 small">
                        <li>Place bins at the curb by 7:00 AM on collection day</li>
                        <li>Ensure bins are accessible and not blocked by vehicles</li>
                        <li>Check for holiday schedule changes</li>
                        <li>Keep bins clean and lids closed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
