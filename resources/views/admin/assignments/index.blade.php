@section('title', 'Assignment Management')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="assignments" />
    </x-slot>

    @php
        $breadcrumbItems = [];
        if (request('return_url')) {
            $breadcrumbItems[] = [
                'label' => 'Dashboard',
                'url' => request('return_url'),
                'icon' => 'speedometer2'
            ];
        }
        $breadcrumbItems[] = [
            'label' => 'Assignment Management',
            'url' => route('admin.assignments.index'),
            'icon' => 'clipboard-check'
        ];
    @endphp

    @if(count($breadcrumbItems) > 1)
        <x-dashboard.breadcrumb :items="$breadcrumbItems" />
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Assignment Management</h1>
        <div>
            <a href="{{ route('admin.assignments.unassigned-routes') }}" class="btn btn-outline-warning me-2">
                <i class="bi bi-exclamation-triangle"></i> Unassigned Routes
            </a>
            <a href="{{ route('admin.assignments.copy-form') }}" class="btn btn-outline-primary me-2">
                <i class="bi bi-files"></i> Copy Assignments
            </a>
            <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Assignment
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="truckFilter" class="form-label">Filter by Truck</label>
                    <select class="form-select" id="truckFilter">
                        <option value="">All Trucks</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->truck_number }} - {{ $truck->license_plate }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="crewFilter" class="form-label">Filter by Crew</label>
                    <select class="form-select" id="crewFilter">
                        <option value="">All Crew Members</option>
                        @foreach($crewMembers as $crew)
                            <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="applyFilters">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card">
        <div class="card-body">
            <div id="assignmentCalendar"></div>
        </div>
    </div>

    @push('styles')
    <style>
        #assignmentCalendar {
            max-width: 100%;
            margin: 0 auto;
        }

        .fc {
            font-family: inherit;
        }

        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 600;
        }

        .fc-button {
            background-color: var(--sweep-accent) !important;
            border-color: var(--sweep-accent) !important;
            text-transform: capitalize;
        }

        .fc-button:hover {
            background-color: #0a7a6e !important;
            border-color: #0a7a6e !important;
        }

        .fc-button:disabled {
            opacity: 0.5;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
        }

        .fc-event:hover {
            opacity: 0.85;
        }

        .fc-daygrid-event {
            white-space: normal;
        }

        .fc-day-today {
            background-color: rgba(13, 148, 136, 0.1) !important;
        }

        .fc-h-event {
            border: none !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script type="module">
        import { Calendar } from '@fullcalendar/core';
        import dayGridPlugin from '@fullcalendar/daygrid';
        import interactionPlugin from '@fullcalendar/interaction';

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('assignmentCalendar');
            
            const calendar = new Calendar(calendarEl, {
                plugins: [dayGridPlugin, interactionPlugin],
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                height: 'auto',
                events: function(info, successCallback, failureCallback) {
                    const truckId = document.getElementById('truckFilter').value;
                    const userId = document.getElementById('crewFilter').value;
                    
                    const params = new URLSearchParams({
                        start: info.startStr,
                        end: info.endStr
                    });
                    
                    if (truckId) params.append('truck_id', truckId);
                    if (userId) params.append('user_id', userId);
                    
                    fetch(`{{ route('admin.assignments.calendar.data') }}?${params.toString()}`)
                        .then(response => response.json())
                        .then(data => successCallback(data))
                        .catch(error => {
                            console.error('Error loading calendar data:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                },
                dateClick: function(info) {
                    // Navigate to create assignment with pre-filled date
                    window.location.href = `{{ route('admin.assignments.create') }}?date=${info.dateStr}`;
                }
            });

            calendar.render();

            // Apply filters button
            document.getElementById('applyFilters').addEventListener('click', function() {
                calendar.refetchEvents();
            });

            // Also refetch on filter change
            document.getElementById('truckFilter').addEventListener('change', function() {
                calendar.refetchEvents();
            });

            document.getElementById('crewFilter').addEventListener('change', function() {
                calendar.refetchEvents();
            });
        });
    </script>
    @endpush
</x-app-layout>
