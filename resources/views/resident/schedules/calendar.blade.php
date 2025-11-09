@section('title', 'Collection Calendar - ' . $zone)

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
        <div class="col-lg-11">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="bi bi-calendar3"></i> Collection Calendar - {{ $zone }}
                </h1>
                <div>
                    <a href="{{ route('resident.schedules.search', ['zone' => $zone]) }}" 
                       class="btn btn-outline-secondary me-2">
                        <i class="bi bi-list-ul"></i> List View
                    </a>
                    <a href="{{ route('resident.schedules') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> New Search
                    </a>
                </div>
            </div>

            <!-- Next Collection Alert -->
            <div id="nextCollectionAlert" class="alert alert-success d-none mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="alert-heading mb-2">
                            <i class="bi bi-calendar-check-fill"></i> Next Collection
                        </h5>
                        <p class="mb-0" id="nextCollectionText"></p>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <span id="nextCollectionBadge" class="badge bg-white text-success fs-6 px-3 py-2"></span>
                    </div>
                </div>
            </div>

            <!-- Calendar Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Calendar Controls -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <button id="prevMonth" class="btn btn-outline-secondary">
                            <i class="bi bi-chevron-left"></i> Previous
                        </button>
                        <h4 id="currentMonth" class="mb-0"></h4>
                        <button id="nextMonth" class="btn btn-outline-secondary">
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>

                    <!-- Calendar Grid -->
                    <div id="calendar" class="calendar-grid"></div>

                    <!-- Legend -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-3"><i class="bi bi-info-circle"></i> Legend</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="legend-box" style="background-color: #2D5F3F;"></div>
                                    <span class="ms-2">Regular Collection Day</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="legend-box" style="background-color: #F59E0B;"></div>
                                    <span class="ms-2">Rescheduled Collection</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="legend-box" style="background-color: #E5E7EB;"></div>
                                    <span class="ms-2">No Collection</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--sweep-primary); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-event"></i> Collection Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="eventDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: #dee2e6;
            border: 1px solid #dee2e6;
        }

        .calendar-header {
            background-color: var(--sweep-primary);
            color: white;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .calendar-day {
            background-color: white;
            min-height: 100px;
            padding: 8px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .calendar-day:hover {
            background-color: #f8f9fa;
        }

        .calendar-day.other-month {
            background-color: #f8f9fa;
            color: #adb5bd;
        }

        .calendar-day.today {
            background-color: #fff3cd;
        }

        .day-number {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 0.9rem;
        }

        .calendar-day.collection-day {
            background-color: #d4edda;
            border: 2px solid #2D5F3F;
        }

        .calendar-day.rescheduled-day {
            background-color: #fff3cd;
            border: 2px solid #F59E0B;
        }

        .collection-badge {
            background-color: #2D5F3F;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            display: inline-block;
            margin-top: 4px;
            width: 100%;
            text-align: center;
        }

        .rescheduled-badge {
            background-color: #F59E0B;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            display: inline-block;
            margin-top: 4px;
            width: 100%;
            text-align: center;
        }

        .collection-time {
            font-size: 0.7rem;
            color: #6c757d;
            margin-top: 2px;
        }

        .legend-box {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        @media (max-width: 768px) {
            .calendar-day {
                min-height: 80px;
                padding: 4px;
                font-size: 0.85rem;
            }

            .day-number {
                font-size: 0.8rem;
            }

            .collection-badge,
            .rescheduled-badge {
                font-size: 0.65rem;
                padding: 2px 4px;
            }

            .collection-time {
                font-size: 0.6rem;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        const zone = @json($zone);
        let currentDate = new Date();
        let calendarEvents = [];

        // Initialize calendar on page load
        document.addEventListener('DOMContentLoaded', function() {
            renderCalendar();
        });

        // Previous month button
        document.getElementById('prevMonth').addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        // Next month button
        document.getElementById('nextMonth').addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            // Update month display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

            // Calculate date range for API call
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            
            // Extend range to include previous/next month days shown in calendar
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());
            
            const endDate = new Date(lastDay);
            endDate.setDate(endDate.getDate() + (6 - lastDay.getDay()));

            // Fetch calendar data
            fetchCalendarData(startDate, endDate, function() {
                renderCalendarGrid(year, month);
                updateNextCollectionAlert();
            });
        }

        function fetchCalendarData(startDate, endDate, callback) {
            const url = new URL('{{ route("resident.schedules.calendar.data") }}');
            url.searchParams.append('zone', zone);
            url.searchParams.append('start', formatDate(startDate));
            url.searchParams.append('end', formatDate(endDate));

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                calendarEvents = data;
                if (callback) callback();
            })
            .catch(error => {
                console.error('Error fetching calendar data:', error);
            });
        }

        function renderCalendarGrid(year, month) {
            const calendar = document.getElementById('calendar');
            calendar.innerHTML = '';

            // Add day headers
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayNames.forEach(day => {
                const header = document.createElement('div');
                header.className = 'calendar-header';
                header.textContent = day;
                calendar.appendChild(header);
            });

            // Get first and last day of month
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Calculate starting day
            let currentDay = new Date(firstDay);
            currentDay.setDate(currentDay.getDate() - firstDay.getDay());

            // Render 6 weeks
            for (let week = 0; week < 6; week++) {
                for (let day = 0; day < 7; day++) {
                    const dayCell = createDayCell(currentDay, month, today);
                    calendar.appendChild(dayCell);
                    currentDay.setDate(currentDay.getDate() + 1);
                }
            }
        }

        function createDayCell(date, currentMonth, today) {
            const cell = document.createElement('div');
            cell.className = 'calendar-day';

            // Check if day is in current month
            if (date.getMonth() !== currentMonth) {
                cell.classList.add('other-month');
            }

            // Check if today
            if (date.getTime() === today.getTime()) {
                cell.classList.add('today');
            }

            // Day number
            const dayNumber = document.createElement('div');
            dayNumber.className = 'day-number';
            dayNumber.textContent = date.getDate();
            cell.appendChild(dayNumber);

            // Check for events on this day
            const dateStr = formatDate(date);
            const dayEvents = calendarEvents.filter(event => event.start === dateStr);

            if (dayEvents.length > 0) {
                dayEvents.forEach(event => {
                    if (event.extendedProps.is_rescheduled) {
                        cell.classList.add('rescheduled-day');
                        const badge = document.createElement('div');
                        badge.className = 'rescheduled-badge';
                        badge.innerHTML = `<i class="bi bi-arrow-repeat"></i> Rescheduled`;
                        cell.appendChild(badge);
                    } else {
                        cell.classList.add('collection-day');
                        const badge = document.createElement('div');
                        badge.className = 'collection-badge';
                        badge.innerHTML = `<i class="bi bi-trash"></i> Collection`;
                        cell.appendChild(badge);
                    }

                    const time = document.createElement('div');
                    time.className = 'collection-time';
                    time.textContent = formatTime(event.extendedProps.collection_time);
                    cell.appendChild(time);
                });

                // Add click handler
                cell.style.cursor = 'pointer';
                cell.addEventListener('click', function() {
                    showEventDetails(dayEvents, date);
                });
            }

            return cell;
        }

        function showEventDetails(events, date) {
            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
            const detailsDiv = document.getElementById('eventDetails');

            let html = `<h6 class="mb-3">${formatDateLong(date)}</h6>`;

            events.forEach(event => {
                const props = event.extendedProps;
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-map"></i> ${props.route_name}
                            </h6>
                            <p class="mb-2">
                                <i class="bi bi-clock"></i> 
                                <strong>Collection Time:</strong> ${formatTime(props.collection_time)}
                            </p>
                            <p class="mb-2">
                                <i class="bi bi-geo-alt"></i> 
                                <strong>Zone:</strong> ${props.zone}
                            </p>
                            ${props.is_rescheduled ? `
                                <div class="alert alert-warning mb-0 mt-2">
                                    <small>
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Rescheduled Collection</strong><br>
                                        Originally scheduled for ${formatDateShort(props.original_date)}<br>
                                        Reason: ${props.holiday_name}
                                    </small>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });

            detailsDiv.innerHTML = html;
            modal.show();
        }

        function updateNextCollectionAlert() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Find next collection date
            const futureEvents = calendarEvents
                .filter(event => new Date(event.start) >= today)
                .sort((a, b) => new Date(a.start) - new Date(b.start));

            if (futureEvents.length > 0) {
                const nextEvent = futureEvents[0];
                const nextDate = new Date(nextEvent.start);
                const daysUntil = Math.ceil((nextDate - today) / (1000 * 60 * 60 * 24));

                const alert = document.getElementById('nextCollectionAlert');
                const text = document.getElementById('nextCollectionText');
                const badge = document.getElementById('nextCollectionBadge');

                text.innerHTML = `
                    <strong>${formatDateLong(nextDate)}</strong><br>
                    <i class="bi bi-clock"></i> ${formatTime(nextEvent.extendedProps.collection_time)} - 
                    <i class="bi bi-map"></i> ${nextEvent.extendedProps.route_name}
                `;

                if (daysUntil === 0) {
                    badge.textContent = 'Today';
                } else if (daysUntil === 1) {
                    badge.textContent = 'Tomorrow';
                } else {
                    badge.textContent = `In ${daysUntil} days`;
                }

                alert.classList.remove('d-none');
            }
        }

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatDateLong(date) {
            if (typeof date === 'string') {
                date = new Date(date);
            }
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        function formatDateShort(dateStr) {
            const date = new Date(dateStr);
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        function formatTime(timeStr) {
            const [hours, minutes] = timeStr.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        }
    </script>
    @endpush
</x-app-layout>
