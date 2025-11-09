@props(['active' => ''])

<div class="nav flex-column">
    <a class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link {{ $active === 'users' ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
        <i class="bi bi-people"></i> Users
    </a>
    <a class="nav-link {{ $active === 'routes' ? 'active' : '' }}" href="{{ route('admin.routes.index') }}">
        <i class="bi bi-map"></i> Routes
    </a>
    <a class="nav-link {{ $active === 'schedules' ? 'active' : '' }}" href="{{ route('admin.schedules.index') }}">
        <i class="bi bi-calendar"></i> Schedules
    </a>
    <a class="nav-link {{ $active === 'holidays' ? 'active' : '' }}" href="{{ route('admin.holidays.index') }}">
        <i class="bi bi-calendar-x"></i> Holidays
    </a>
    <a class="nav-link {{ $active === 'trucks' ? 'active' : '' }}" href="{{ route('admin.trucks.index') }}">
        <i class="bi bi-truck"></i> Trucks
    </a>
    <a class="nav-link {{ $active === 'assignments' ? 'active' : '' }}" href="{{ route('admin.assignments.index') }}">
        <i class="bi bi-clipboard-check"></i> Assignments
    </a>
    <a class="nav-link {{ $active === 'truck-availability' ? 'active' : '' }}" href="{{ route('admin.truck-availability.index') }}">
        <i class="bi bi-calendar-check"></i> Truck Availability
    </a>
    <a class="nav-link {{ $active === 'collection-logs' ? 'active' : '' }}" href="{{ route('admin.collection-logs.index') }}">
        <i class="bi bi-clipboard-data"></i> Collection Logs
    </a>
    <a class="nav-link {{ $active === 'collection-analytics' ? 'active' : '' }}" href="{{ route('admin.analytics.collections.index') }}">
        <i class="bi bi-graph-up"></i> Collection Analytics
    </a>
    <a class="nav-link {{ $active === 'reports' ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
        <i class="bi bi-file-text"></i> Reports
    </a>
    <a class="nav-link {{ $active === 'report-analytics' ? 'active' : '' }}" href="{{ route('admin.analytics.reports.index') }}">
        <i class="bi bi-graph-up-arrow"></i> Report Analytics
    </a>
    <a class="nav-link {{ $active === 'recycling-logs' ? 'active' : '' }}" href="{{ route('admin.recycling-logs.index') }}">
        <i class="bi bi-recycle"></i> Recycling Logs
    </a>
    <a class="nav-link {{ $active === 'recycling-analytics' ? 'active' : '' }}" href="{{ route('admin.recycling.analytics.dashboard') }}">
        <i class="bi bi-bar-chart"></i> Recycling Analytics
    </a>
    <a class="nav-link {{ $active === 'recycling-targets' ? 'active' : '' }}" href="{{ route('admin.recycling.targets.index') }}">
        <i class="bi bi-bullseye"></i> Recycling Targets
    </a>
    <hr>
    <a class="nav-link" href="{{ route('profile.edit') }}">
        <i class="bi bi-gear"></i> Settings
    </a>
</div>
