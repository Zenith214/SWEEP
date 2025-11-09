@props(['active' => ''])

<div class="nav flex-column">
    <a class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link {{ $active === 'schedules' ? 'active' : '' }}" href="{{ route('crew.schedules') }}">
        <i class="bi bi-calendar-check"></i> Today's Routes
    </a>
    <a class="nav-link {{ $active === 'schedules-upcoming' ? 'active' : '' }}" href="{{ route('crew.schedules.upcoming') }}">
        <i class="bi bi-calendar3"></i> Upcoming Routes
    </a>
    <a class="nav-link {{ $active === 'assignments' ? 'active' : '' }}" href="{{ route('crew.assignments') }}">
        <i class="bi bi-clipboard-check"></i> My Assignment
    </a>
    <a class="nav-link {{ $active === 'assignments-upcoming' ? 'active' : '' }}" href="{{ route('crew.assignments.upcoming') }}">
        <i class="bi bi-calendar-week"></i> Upcoming Assignments
    </a>
    <a class="nav-link {{ $active === 'collections' ? 'active' : '' }}" href="{{ route('crew.collections') }}">
        <i class="bi bi-clipboard-check"></i> Log Collection
    </a>
    <a class="nav-link {{ $active === 'collections-history' ? 'active' : '' }}" href="{{ route('crew.collections.history') }}">
        <i class="bi bi-clock-history"></i> Collection History
    </a>
    <a class="nav-link {{ $active === 'recycling-logs' ? 'active' : '' }}" href="{{ route('crew.recycling-logs.index') }}">
        <i class="bi bi-recycle"></i> Recycling Logs
    </a>
    <hr>
    <a class="nav-link" href="{{ route('profile.edit') }}">
        <i class="bi bi-gear"></i> Settings
    </a>
</div>
