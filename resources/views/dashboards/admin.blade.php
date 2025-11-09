@section('title', 'Administrator Dashboard')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="dashboard" />
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4" x-data="dashboardCustomization">
        <h1 class="h2 mb-0">Administrator Dashboard</h1>
        <div class="d-flex align-items-center gap-3">
            <small class="text-muted" id="last-updated">
                <i class="bi bi-clock"></i> 
                Last updated: <span id="update-timestamp">{{ $metrics['metadata']['generated_at'] ?? now()->format('g:i A') }}</span>
            </small>
            <button type="button" 
                    class="btn btn-sm btn-outline-secondary" 
                    @click="toggleCustomizationMode"
                    :aria-label="customizationMode ? 'Exit customization mode' : 'Customize dashboard'">
                <i class="bi" :class="customizationMode ? 'bi-x-lg' : 'bi-gear'"></i>
                <span x-text="customizationMode ? 'Done' : 'Customize'"></span>
            </button>
            <button type="button" 
                    class="btn btn-sm btn-outline-primary" 
                    id="refresh-dashboard"
                    onclick="refreshDashboard()"
                    aria-label="Refresh dashboard data">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Customization Panel -->
    <div x-show="customizationMode" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="alert alert-info mb-4" 
         role="alert"
         x-cloak>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Customization Mode:</strong> Toggle widget visibility or drag widgets to reorder them.
            </div>
            <div class="d-flex gap-2">
                <button type="button" 
                        class="btn btn-sm btn-success" 
                        @click="savePreferences"
                        :disabled="saving"
                        aria-label="Save dashboard preferences">
                    <span x-show="!saving">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </span>
                    <span x-show="saving">
                        <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                    </span>
                </button>
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary" 
                        @click="resetToDefaults"
                        :disabled="saving"
                        aria-label="Reset to default layout">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset to Defaults
                </button>
            </div>
        </div>
    </div>

    <div class="alert alert-success mb-4" role="alert">
        <i class="bi bi-check-circle-fill"></i> Welcome back, <strong>{{ $user->name }}</strong>! You are logged in as an Administrator.
    </div>

    <!-- Filter Bar with Period Selector and Export -->
    <x-dashboard.filter-bar 
        :currentPeriod="request('period', '30days')"
        :comparisonPeriod="request('compare_period')"
    />

    <!-- Comparison Summary (if comparison is active) -->
    @if(isset($metrics['comparisons']) && !empty($metrics['comparisons']))
    <div class="alert alert-info mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Period Comparison Active</strong>
                <p class="mb-0 small">
                    Comparing {{ $metrics['metadata']['period_start'] }} to {{ $metrics['metadata']['period_end'] }}
                    with {{ $metrics['comparisons']['period_info']['previous_start'] }} to {{ $metrics['comparisons']['period_info']['previous_end'] }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Assignment Alerts -->
    @if(isset($alerts) && count($alerts) > 0)
        <div class="row g-3 mb-4">
            @foreach($alerts as $alert)
                <div class="col-md-6">
                    <x-alert-card 
                        :type="$alert['type']"
                        :title="$alert['title']"
                        :count="$alert['count']"
                        :message="$alert['message']"
                        :link="$alert['link']"
                        :linkText="$alert['link_text']"
                        :bgColor="$alert['type'] === 'unassigned_routes' ? 'amber' : 'teal'"
                    />
                </div>
            @endforeach
        </div>
    @endif

    <!-- Dashboard Widgets Container -->
    <div id="dashboard-widgets" x-data="dashboardCustomization">
    
    <!-- Today's Collection Status -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="collection_status"
         x-show="isWidgetVisible('collection_status')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-calendar-check"></i> Today's Collection Status
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('collection_status')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3" id="collection-status-cards">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Scheduled Collections"
                    :value="$metrics['collection_metrics']['scheduled_today'] ?? 0"
                    icon="calendar-event"
                    bgColor="primary"
                    :link="route('admin.assignments.index', ['date' => now()->format('Y-m-d')])"
                    subtitle="Collections scheduled for today"
                    :comparison="isset($metrics['comparisons']['collections']['total']) ? $metrics['comparisons']['collections']['total']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['collections']['total']) ? $metrics['comparisons']['collections']['total']['trend'] : null"
                    drillDownMetric="collections_today"
                    :preserveContext="true"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Completed Collections"
                    :value="$metrics['collection_metrics']['completed_today'] ?? 0"
                    icon="check-circle"
                    bgColor="success"
                    :link="route('admin.collection-logs.index', ['date' => now()->format('Y-m-d'), 'status' => 'completed'])"
                    subtitle="Successfully completed today"
                    :comparison="isset($metrics['comparisons']['collections']['total']) ? $metrics['comparisons']['collections']['total']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['collections']['total']) ? $metrics['comparisons']['collections']['total']['trend'] : null"
                    drillDownMetric="collections_today"
                    :preserveContext="true"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Completion Rate"
                    :value="number_format($metrics['collection_metrics']['completion_rate_today'] ?? 0, 1) . '%'"
                    icon="percent"
                    bgColor="info"
                    :comparison="isset($metrics['comparisons']['collections']['completion_rate']) ? $metrics['comparisons']['collections']['completion_rate']['percentage_change'] : ($metrics['collection_metrics']['completion_rate_change'] ?? null)"
                    :comparisonLabel="isset($metrics['comparisons']) ? 'vs previous period' : 'vs yesterday'"
                    :trend="isset($metrics['comparisons']['collections']['completion_rate']) ? $metrics['comparisons']['collections']['completion_rate']['trend'] : ($metrics['collection_metrics']['completion_trend'] ?? 'stable')"
                    subtitle="Today's completion percentage"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Issues Reported"
                    :value="$metrics['collection_metrics']['issues_today'] ?? 0"
                    icon="exclamation-triangle"
                    bgColor="warning"
                    :link="route('admin.collection-logs.index', ['has_issues' => 1])"
                    subtitle="Collections with reported issues"
                    :comparison="isset($metrics['comparisons']['collections']['issues']) ? $metrics['comparisons']['collections']['issues']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['collections']['issues']) ? $metrics['comparisons']['collections']['issues']['trend'] : null"
                    drillDownMetric="collections_today"
                    :preserveContext="true"
                />
            </div>
        </div>
    </div>

    <!-- Pending Items Requiring Attention -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="pending_items"
         x-show="isWidgetVisible('pending_items')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-bell"></i> Pending Items Requiring Attention
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('pending_items')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3" id="pending-items-cards">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Unassigned Routes"
                    :value="$metrics['pending_items']['unassigned_routes'] ?? 0"
                    icon="calendar-x"
                    bgColor="danger"
                    :link="route('admin.assignments.unassigned-routes')"
                    subtitle="Next 7 days"
                    drillDownMetric="pending_reports"
                    :preserveContext="true"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Pending Reports"
                    :value="$metrics['pending_items']['pending_reports'] ?? 0"
                    icon="file-text"
                    bgColor="warning"
                    :link="route('admin.reports.index', ['status' => 'pending'])"
                    subtitle="Awaiting review"
                    drillDownMetric="pending_reports"
                    :preserveContext="true"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Trucks in Maintenance"
                    :value="$metrics['pending_items']['trucks_in_maintenance'] ?? 0"
                    icon="wrench"
                    bgColor="secondary"
                    :link="route('admin.trucks.index', ['status' => 'maintenance'])"
                    subtitle="Out of service"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Overdue Reports"
                    :value="$metrics['pending_items']['overdue_reports'] ?? 0"
                    icon="clock-history"
                    bgColor="danger"
                    :link="route('admin.reports.index', ['overdue' => 1])"
                    subtitle="Pending > 48 hours"
                />
            </div>
        </div>
    </div>

    <!-- Collection Performance Trend -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="collection_trends"
         x-show="isWidgetVisible('collection_trends')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-graph-up"></i> Collection Performance Trend
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('collection_trends')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12">
                @php
                    $trendData = $metrics['collection_metrics']['trend_data'] ?? [];
                    $chartData = [
                        'labels' => $trendData['labels'] ?? [],
                        'values' => $trendData['values'] ?? [],
                        'label' => 'Completion Rate (%)'
                    ];
                @endphp
                <x-dashboard.chart-widget
                    title="Collection Performance Trend"
                    chartId="collection-performance-chart"
                    chartType="line"
                    height="350"
                    :filters="true"
                    description="Daily collection completion rates over time"
                    :data="$chartData"
                >
                    Collection completion rates showing daily performance trends.
                </x-dashboard.chart-widget>
            </div>
        </div>
    </div>

    <!-- Recycling Performance -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="recycling_metrics"
         x-show="isWidgetVisible('recycling_metrics')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-recycle"></i> Recycling Performance
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('recycling_metrics')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Total Recyclables"
                    :value="number_format($metrics['recycling_metrics']['total_weight'] ?? 0, 1) . ' kg'"
                    icon="recycle"
                    bgColor="success"
                    :link="route('admin.recycling-logs.index')"
                    subtitle="Current month"
                    :comparison="isset($metrics['comparisons']['recycling']['total_weight']) ? $metrics['comparisons']['recycling']['total_weight']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['recycling']['total_weight']) ? $metrics['comparisons']['recycling']['total_weight']['trend'] : null"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Recycling Rate"
                    :value="number_format($metrics['recycling_metrics']['recycling_rate'] ?? 0, 1) . '%'"
                    icon="percent"
                    bgColor="info"
                    subtitle="Collections with recycling"
                    :comparison="isset($metrics['comparisons']['recycling']['recycling_rate']) ? $metrics['comparisons']['recycling']['recycling_rate']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['recycling']['recycling_rate']) ? $metrics['comparisons']['recycling']['recycling_rate']['trend'] : null"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Recycling Logs"
                    :value="$metrics['recycling_metrics']['total_logs'] ?? 0"
                    icon="clipboard-data"
                    bgColor="primary"
                    :link="route('admin.recycling-logs.index')"
                    subtitle="Total logs recorded"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Quality Issues"
                    :value="$metrics['recycling_metrics']['logs_with_quality_issues'] ?? 0"
                    icon="exclamation-triangle"
                    bgColor="warning"
                    :link="route('admin.recycling-logs.index', ['quality_issue' => 1])"
                    subtitle="Logs with quality concerns"
                />
            </div>
        </div>
        
        <!-- Recycling Breakdown Chart -->
        @if(isset($metrics['recycling_metrics']['material_breakdown']) && count($metrics['recycling_metrics']['material_breakdown']) > 0)
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                @php
                    $materialBreakdown = $metrics['recycling_metrics']['material_breakdown'] ?? [];
                    $recyclingChartData = [
                        'labels' => array_map(fn($m) => ucfirst($m['material_type']), $materialBreakdown),
                        'values' => array_map(fn($m) => $m['weight'], $materialBreakdown),
                        'label' => 'Weight (kg)'
                    ];
                @endphp
                <x-dashboard.chart-widget
                    title="Recycling Breakdown by Material Type"
                    chartId="recycling-breakdown-chart"
                    chartType="pie"
                    height="300"
                    description="Distribution of recyclable materials collected"
                    :data="$recyclingChartData"
                >
                    Breakdown of recyclable materials by type and weight.
                </x-dashboard.chart-widget>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Material Breakdown Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Material Type</th>
                                        <th class="text-end">Weight (kg)</th>
                                        <th class="text-end">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($metrics['recycling_metrics']['material_breakdown'] as $material)
                                    <tr>
                                        <td>
                                            <span class="badge bg-success">{{ ucfirst($material['material_type']) }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($material['weight'], 2) }}</td>
                                        <td class="text-end">{{ number_format($material['percentage'], 1) }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-info mt-3" role="alert">
            <i class="bi bi-info-circle"></i> No recycling data available for the selected period.
        </div>
        @endif
    </div>

    <!-- Fleet Utilization -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="fleet_utilization"
         x-show="isWidgetVisible('fleet_utilization')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-truck"></i> Fleet Utilization
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('fleet_utilization')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Total Trucks"
                    :value="$metrics['fleet_metrics']['total_trucks'] ?? 0"
                    icon="truck"
                    bgColor="primary"
                    :link="route('admin.trucks.index')"
                    subtitle="All trucks in fleet"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Operational Trucks"
                    :value="$metrics['fleet_metrics']['operational'] ?? 0"
                    icon="check-circle"
                    bgColor="success"
                    :link="route('admin.trucks.index', ['status' => 'operational'])"
                    subtitle="Ready for assignments"
                    :comparison="isset($metrics['comparisons']['fleet']['operational_trucks']) ? $metrics['comparisons']['fleet']['operational_trucks']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['fleet']['operational_trucks']) ? $metrics['comparisons']['fleet']['operational_trucks']['trend'] : null"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Average Utilization"
                    :value="number_format($metrics['fleet_metrics']['average_utilization'] ?? 0, 1) . '%'"
                    icon="percent"
                    bgColor="info"
                    subtitle="Fleet utilization rate"
                    :comparison="isset($metrics['comparisons']['fleet']['utilization']) ? $metrics['comparisons']['fleet']['utilization']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['fleet']['utilization']) ? $metrics['comparisons']['fleet']['utilization']['trend'] : null"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Trucks with Assignments"
                    :value="$metrics['fleet_metrics']['trucks_with_assignments'] ?? 0"
                    icon="clipboard-check"
                    bgColor="primary"
                    :link="route('admin.assignments.index')"
                    subtitle="Currently assigned"
                />
            </div>
        </div>
        
        <!-- Truck Status Breakdown -->
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Truck Status Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-4 text-center">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['fleet_metrics']['operational'] ?? 0 }}</div>
                                    <div class="small text-muted">Operational</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-3 bg-warning bg-opacity-10 rounded">
                                    <i class="bi bi-wrench text-warning fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['fleet_metrics']['maintenance'] ?? 0 }}</div>
                                    <div class="small text-muted">Maintenance</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-3 bg-danger bg-opacity-10 rounded">
                                    <i class="bi bi-x-circle-fill text-danger fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['fleet_metrics']['out_of_service'] ?? 0 }}</div>
                                    <div class="small text-muted">Out of Service</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Underutilized Trucks</h5>
                        <span class="badge bg-warning">
                            {{ count($metrics['fleet_metrics']['underutilized_trucks'] ?? []) }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if(isset($metrics['fleet_metrics']['underutilized_trucks']) && count($metrics['fleet_metrics']['underutilized_trucks']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Truck Number</th>
                                        <th class="text-end">Utilization</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($metrics['fleet_metrics']['underutilized_trucks'], 0, 5) as $truck)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.trucks.show', $truck['truck_id']) }}" class="text-decoration-none">
                                                {{ $truck['truck_number'] }}
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-warning">{{ number_format($truck['utilization_rate'], 1) }}%</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($metrics['fleet_metrics']['underutilized_trucks']) > 5)
                        <div class="text-center mt-2">
                            <a href="{{ route('admin.trucks.index', ['underutilized' => 1]) }}" class="btn btn-sm btn-outline-primary">
                                View All ({{ count($metrics['fleet_metrics']['underutilized_trucks']) }})
                            </a>
                        </div>
                        @endif
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0">All operational trucks are well utilized!</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Crew Performance -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="crew_performance"
         x-show="isWidgetVisible('crew_performance')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-people"></i> Crew Performance
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('crew_performance')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Active Crew Members"
                    :value="$metrics['crew_performance']['active_crew_count'] ?? 0"
                    icon="people"
                    bgColor="primary"
                    :link="route('admin.users.index', ['role' => 'collection_crew'])"
                    subtitle="Total active crew"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Avg Collections per Crew"
                    :value="number_format($metrics['crew_performance']['avg_collections_per_crew'] ?? 0, 1)"
                    icon="clipboard-check"
                    bgColor="info"
                    subtitle="Average per crew member"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Total Collections"
                    :value="$metrics['crew_performance']['total_collections'] ?? 0"
                    icon="clipboard-data"
                    bgColor="success"
                    :link="route('admin.collection-logs.index')"
                    subtitle="All crew collections"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Crew with Issues"
                    :value="count($metrics['crew_performance']['crew_with_most_issues'] ?? [])"
                    icon="exclamation-triangle"
                    bgColor="warning"
                    subtitle="Members reporting issues"
                />
            </div>
        </div>
        
        <!-- Top Performers and Crew with Most Issues -->
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Top Performers</h5>
                        <span class="badge bg-success">
                            By Completion Rate
                        </span>
                    </div>
                    <div class="card-body">
                        @if(isset($metrics['crew_performance']['top_performers']) && count($metrics['crew_performance']['top_performers']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Crew Member</th>
                                        <th class="text-end">Collections</th>
                                        <th class="text-end">Completion Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($metrics['crew_performance']['top_performers'] as $performer)
                                    <tr>
                                        <td>
                                            <i class="bi bi-person-fill text-success me-1"></i>
                                            {{ $performer['user_name'] }}
                                        </td>
                                        <td class="text-end">{{ $performer['completed'] }}/{{ $performer['total_collections'] }}</td>
                                        <td class="text-end">
                                            <span class="badge bg-success">{{ number_format($performer['completion_rate'], 1) }}%</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0">No crew performance data available for the selected period.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Crew Members with Most Issues</h5>
                        <span class="badge bg-warning">
                            Needs Attention
                        </span>
                    </div>
                    <div class="card-body">
                        @if(isset($metrics['crew_performance']['crew_with_most_issues']) && count($metrics['crew_performance']['crew_with_most_issues']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Crew Member</th>
                                        <th class="text-end">Issues Reported</th>
                                        <th class="text-end">Completion Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($metrics['crew_performance']['crew_with_most_issues'] as $crew)
                                    <tr>
                                        <td>
                                            <i class="bi bi-person-fill text-warning me-1"></i>
                                            {{ $crew['user_name'] }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-warning">{{ $crew['issues_reported'] }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($crew['completion_rate'], 1) }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0">No issues reported by crew members!</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Statistics -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="report_statistics"
         x-show="isWidgetVisible('report_statistics')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-file-text"></i> Report Statistics
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('report_statistics')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Total Reports"
                    :value="$metrics['report_statistics']['total_reports'] ?? 0"
                    icon="file-text"
                    bgColor="primary"
                    :link="route('admin.reports.index')"
                    subtitle="All reports submitted"
                    :comparison="isset($metrics['comparisons']['reports']['total']) ? $metrics['comparisons']['reports']['total']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['reports']['total']) ? $metrics['comparisons']['reports']['total']['trend'] : null"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Pending Reports"
                    :value="$metrics['report_statistics']['by_status']['pending'] ?? 0"
                    icon="clock"
                    bgColor="warning"
                    :link="route('admin.reports.index', ['status' => 'pending'])"
                    subtitle="Awaiting review"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Resolved Reports"
                    :value="$metrics['report_statistics']['resolved_count'] ?? 0"
                    icon="check-circle"
                    bgColor="success"
                    :link="route('admin.reports.index', ['status' => 'resolved'])"
                    subtitle="Successfully resolved"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Avg Resolution Time"
                    :value="isset($metrics['report_statistics']['avg_resolution_time_hours']) && $metrics['report_statistics']['avg_resolution_time_hours'] !== null ? number_format($metrics['report_statistics']['avg_resolution_time_hours'], 1) . ' hrs' : 'N/A'"
                    icon="stopwatch"
                    bgColor="info"
                    subtitle="Average time to resolve"
                    :comparison="isset($metrics['comparisons']['reports']['avg_resolution_time']) ? $metrics['comparisons']['reports']['avg_resolution_time']['percentage_change'] : null"
                    comparisonLabel="vs previous period"
                    :trend="isset($metrics['comparisons']['reports']['avg_resolution_time']) ? $metrics['comparisons']['reports']['avg_resolution_time']['trend'] : null"
                />
            </div>
        </div>
        
        <!-- Report Status Breakdown and Most Common Types -->
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Report Status Breakdown</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($metrics['report_statistics']['by_status']))
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 bg-warning bg-opacity-10 rounded">
                                    <i class="bi bi-clock-fill text-warning fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['report_statistics']['by_status']['pending'] ?? 0 }}</div>
                                    <div class="small text-muted">Pending</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-info bg-opacity-10 rounded">
                                    <i class="bi bi-arrow-repeat text-info fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['report_statistics']['by_status']['in_progress'] ?? 0 }}</div>
                                    <div class="small text-muted">In Progress</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['report_statistics']['by_status']['resolved'] ?? 0 }}</div>
                                    <div class="small text-muted">Resolved</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                    <i class="bi bi-x-circle-fill text-secondary fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['report_statistics']['by_status']['closed'] ?? 0 }}</div>
                                    <div class="small text-muted">Closed</div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0">No report data available.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Most Common Report Types</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($metrics['report_statistics']['by_type']) && count($metrics['report_statistics']['by_type']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Report Type</th>
                                        <th class="text-end">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($metrics['report_statistics']['by_type'], 0, 5, true) as $typeKey => $typeData)
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-text text-primary me-1"></i>
                                            {{ $typeData['label'] }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-primary">{{ $typeData['count'] }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0">No report type data available.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Locations with Most Reports -->
        @if(isset($metrics['report_statistics']['locations_with_most_reports']) && count($metrics['report_statistics']['locations_with_most_reports']) > 0)
        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Locations with Highest Report Counts</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Location/Zone</th>
                                        <th class="text-end">Report Count</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($metrics['report_statistics']['locations_with_most_reports'] as $location)
                                    <tr>
                                        <td>
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                            {{ $location['location'] }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-danger">{{ $location['count'] }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.reports.index', ['zone' => $location['location']]) }}" class="btn btn-sm btn-outline-primary">
                                                View Reports
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Route Performance -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="route_performance"
         x-show="isWidgetVisible('route_performance')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-map"></i> Route Performance
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('route_performance')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Total Routes Tracked"
                    :value="$metrics['route_performance']['total_routes_tracked'] ?? 0"
                    icon="map"
                    bgColor="primary"
                    :link="route('admin.routes.index')"
                    subtitle="Routes with collection data"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Routes with Issues"
                    :value="count($metrics['route_performance']['routes_with_most_issues'] ?? [])"
                    icon="exclamation-triangle"
                    bgColor="warning"
                    subtitle="Routes reporting problems"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Low Completion Routes"
                    :value="count($metrics['route_performance']['routes_with_lowest_completion'] ?? [])"
                    icon="arrow-down-circle"
                    bgColor="danger"
                    subtitle="Routes needing attention"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Avg Completion Rate"
                    :value="isset($metrics['route_performance']['all_routes']) && count($metrics['route_performance']['all_routes']) > 0 ? number_format(collect($metrics['route_performance']['all_routes'])->avg('completion_rate'), 1) . '%' : 'N/A'"
                    icon="percent"
                    bgColor="info"
                    subtitle="Average across all routes"
                />
            </div>
        </div>
        
        <!-- Routes with Lowest Completion Rates and Most Issues -->
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Routes with Lowest Completion Rates</h5>
                        <span class="badge bg-danger">
                            Needs Attention
                        </span>
                    </div>
                    <div class="card-body">
                        @if(isset($metrics['route_performance']['routes_with_lowest_completion']) && count($metrics['route_performance']['routes_with_lowest_completion']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Route Name</th>
                                        <th>Zone</th>
                                        <th class="text-end">Completion Rate</th>
                                        <th class="text-end">Collections</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($metrics['route_performance']['routes_with_lowest_completion'] as $route)
                                    <tr>
                                        <td>
                                            <i class="bi bi-map-fill text-danger me-1"></i>
                                            <a href="{{ route('admin.routes.show', $route['route_id']) }}" class="text-decoration-none">
                                                {{ $route['route_name'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $route['zone'] }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-danger">{{ number_format($route['completion_rate'], 1) }}%</span>
                                        </td>
                                        <td class="text-end">{{ $route['completed'] }}/{{ $route['total_collections'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0">All routes have good completion rates!</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Routes with Most Issues</h5>
                        <span class="badge bg-warning">
                            {{ count($metrics['route_performance']['routes_with_most_issues'] ?? []) }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if(isset($metrics['route_performance']['routes_with_most_issues']) && count($metrics['route_performance']['routes_with_most_issues']) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Route Name</th>
                                        <th>Zone</th>
                                        <th class="text-end">Issues</th>
                                        <th class="text-end">Avg Time (min)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($metrics['route_performance']['routes_with_most_issues'] as $route)
                                    <tr>
                                        <td>
                                            <i class="bi bi-map-fill text-warning me-1"></i>
                                            <a href="{{ route('admin.routes.show', $route['route_id']) }}" class="text-decoration-none">
                                                {{ $route['route_name'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $route['zone'] }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-warning">{{ $route['issues_reported'] }}</span>
                                        </td>
                                        <td class="text-end">
                                            {{ $route['avg_completion_time_minutes'] !== null ? number_format($route['avg_completion_time_minutes'], 0) : 'N/A' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0">No issues reported on routes!</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Average Collection Time per Route -->
        @if(isset($metrics['route_performance']['all_routes']) && count($metrics['route_performance']['all_routes']) > 0)
        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Average Collection Time per Route</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Route Name</th>
                                        <th>Zone</th>
                                        <th class="text-end">Total Collections</th>
                                        <th class="text-end">Completion Rate</th>
                                        <th class="text-end">Avg Time (minutes)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(array_slice($metrics['route_performance']['all_routes'], 0, 10) as $route)
                                    <tr>
                                        <td>
                                            <i class="bi bi-map-fill text-primary me-1"></i>
                                            <a href="{{ route('admin.routes.show', $route['route_id']) }}" class="text-decoration-none">
                                                {{ $route['route_name'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $route['zone'] }}</span>
                                        </td>
                                        <td class="text-end">{{ $route['total_collections'] }}</td>
                                        <td class="text-end">
                                            @if($route['completion_rate'] >= 80)
                                                <span class="badge bg-success">{{ number_format($route['completion_rate'], 1) }}%</span>
                                            @elseif($route['completion_rate'] >= 60)
                                                <span class="badge bg-warning">{{ number_format($route['completion_rate'], 1) }}%</span>
                                            @else
                                                <span class="badge bg-danger">{{ number_format($route['completion_rate'], 1) }}%</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            {{ $route['avg_completion_time_minutes'] !== null ? number_format($route['avg_completion_time_minutes'], 0) : 'N/A' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($metrics['route_performance']['all_routes']) > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.routes.index') }}" class="btn btn-sm btn-outline-primary">
                                View All Routes ({{ count($metrics['route_performance']['all_routes']) }})
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- System Usage Statistics -->
    <div class="mb-4 dashboard-widget" 
         data-widget-id="system_usage"
         x-show="isWidgetVisible('system_usage')"
         x-transition>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0">
                <i class="bi bi-graph-up"></i> System Usage Statistics
            </h3>
            <div x-show="customizationMode" class="widget-controls">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary me-2"
                        @click="toggleWidgetVisibility('system_usage')"
                        aria-label="Hide widget">
                    <i class="bi bi-eye-slash"></i> Hide
                </button>
                <span class="drag-handle" style="cursor: move;">
                    <i class="bi bi-grip-vertical"></i>
                </span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Total Active Users"
                    :value="$metrics['usage_statistics']['active_users_by_role']['total'] ?? 0"
                    icon="people"
                    bgColor="primary"
                    :link="route('admin.users.index')"
                    subtitle="All registered users"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="New Residents"
                    :value="$metrics['usage_statistics']['new_resident_registrations'] ?? 0"
                    icon="person-plus"
                    bgColor="success"
                    :link="route('admin.users.index', ['role' => 'resident', 'recent' => 1])"
                    subtitle="Registered this period"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Recent Logins"
                    :value="$metrics['usage_statistics']['recent_logins'] ?? 0"
                    icon="box-arrow-in-right"
                    bgColor="info"
                    subtitle="Logins this period"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Inactive Users"
                    :value="$metrics['usage_statistics']['inactive_users_30_days'] ?? 0"
                    icon="person-x"
                    bgColor="warning"
                    :link="route('admin.users.index', ['inactive' => 1])"
                    subtitle="No login in 30+ days"
                />
            </div>
        </div>
        
        <!-- Active Users by Role -->
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Active Users by Role</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($metrics['usage_statistics']['active_users_by_role']))
                        <div class="row g-3">
                            <div class="col-4 text-center">
                                <div class="p-3 bg-primary bg-opacity-10 rounded">
                                    <i class="bi bi-shield-fill-check text-primary fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['usage_statistics']['active_users_by_role']['administrators'] ?? 0 }}</div>
                                    <div class="small text-muted">Administrators</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-3 bg-success bg-opacity-10 rounded">
                                    <i class="bi bi-person-badge-fill text-success fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['usage_statistics']['active_users_by_role']['crew_members'] ?? 0 }}</div>
                                    <div class="small text-muted">Crew Members</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="p-3 bg-info bg-opacity-10 rounded">
                                    <i class="bi bi-person-fill text-info fs-3 d-block mb-2"></i>
                                    <div class="fs-4 fw-bold">{{ $metrics['usage_statistics']['active_users_by_role']['residents'] ?? 0 }}</div>
                                    <div class="small text-muted">Residents</div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0">No user data available.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">User Engagement Metrics</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Active Residents</span>
                                <strong>{{ $metrics['usage_statistics']['active_residents'] ?? 0 }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                @php
                                    $totalResidents = $metrics['usage_statistics']['active_users_by_role']['residents'] ?? 1;
                                    $activeResidents = $metrics['usage_statistics']['active_residents'] ?? 0;
                                    $activePercentage = $totalResidents > 0 ? ($activeResidents / $totalResidents) * 100 : 0;
                                @endphp
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $activePercentage }}%"
                                     aria-valuenow="{{ $activePercentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted">{{ number_format($activePercentage, 1) }}% of residents submitted reports</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Reports per Active Resident</span>
                                <strong>{{ number_format($metrics['usage_statistics']['reports_per_active_resident'] ?? 0, 2) }}</strong>
                            </div>
                            <small class="text-muted">Average reports submitted per active resident</small>
                        </div>
                        
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">New Registrations</span>
                                <strong>{{ $metrics['usage_statistics']['new_resident_registrations'] ?? 0 }}</strong>
                            </div>
                            <small class="text-muted">New residents registered this period</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login Activity Trends -->
        @if(isset($metrics['usage_statistics']['recent_logins']) && $metrics['usage_statistics']['recent_logins'] > 0)
        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Login Activity Trends</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-arrow-in-right text-success fs-3 me-3"></i>
                                    <div>
                                        <div class="fs-4 fw-bold">{{ $metrics['usage_statistics']['recent_logins'] ?? 0 }}</div>
                                        <div class="small text-muted">Total Logins This Period</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-check text-info fs-3 me-3"></i>
                                    <div>
                                        <div class="fs-4 fw-bold">
                                            {{ ($metrics['usage_statistics']['active_users_by_role']['total'] ?? 0) - ($metrics['usage_statistics']['inactive_users_30_days'] ?? 0) }}
                                        </div>
                                        <div class="small text-muted">Active Users (Last 30 Days)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-x text-warning fs-3 me-3"></i>
                                    <div>
                                        <div class="fs-4 fw-bold">{{ $metrics['usage_statistics']['inactive_users_30_days'] ?? 0 }}</div>
                                        <div class="small text-muted">Inactive Users (30+ Days)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    </div>
    <!-- End Dashboard Widgets Container -->

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-person-plus fs-4 d-block mb-2"></i>
                                <strong>Add New User</strong>
                                <div class="small text-muted">Create a new user account</div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.assignments.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-clipboard-check fs-4 d-block mb-2"></i>
                                <strong>Manage Assignments</strong>
                                <div class="small text-muted">View and create assignments</div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.routes.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-map fs-4 d-block mb-2"></i>
                                <strong>Manage Routes</strong>
                                <div class="small text-muted">View and edit collection routes</div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-file-text fs-4 d-block mb-2"></i>
                                <strong>View Reports</strong>
                                <div class="small text-muted">Manage resident reports</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                        <div>
                            <strong>System Online</strong>
                            <div class="small text-muted">All services operational</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
                        <div>
                            <strong>Database Connected</strong>
                            <div class="small text-muted">MariaDB running</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                        <div>
                            <strong>Version 1.0.0</strong>
                            <div class="small text-muted">SWEEP Platform</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts & Notifications Section -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <x-dashboard.alert-panel 
                :alerts="$metrics['alerts'] ?? []"
                :dismissible="true"
                maxHeight="500"
            />
        </div>
    </div>

    <!-- Operational Costs Section -->
    @php
        $operationalCosts = $metrics['operational_costs'] ?? [];
        // Add comparison data if available
        if (isset($metrics['comparisons']['operational_costs'])) {
            $operationalCosts['comparison'] = $metrics['comparisons']['operational_costs'];
        }
    @endphp
    <x-dashboard.operational-costs 
        :costs="$operationalCosts"
        :customizationMode="false"
    />

    <!-- Geographic Distribution Section -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <x-dashboard.geographic-distribution 
                :geoData="$metrics['geographic_distribution'] ?? []"
                :showFilters="true"
            />
        </div>
    </div>

    <!-- Scheduled Reports Section -->
    @php
        $scheduledReports = auth()->user()->scheduledReports()
            ->with(['generatedReports' => function($query) {
                $query->latest()->limit(3);
            }])
            ->active()
            ->latest()
            ->limit(5)
            ->get();
    @endphp
    @if($scheduledReports->isNotEmpty())
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Scheduled Reports
                    </h5>
                    <a href="{{ route('admin.scheduled-reports.index') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Frequency</th>
                                    <th>Format</th>
                                    <th>Next Generation</th>
                                    <th>Recent Reports</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheduledReports as $report)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.scheduled-reports.show', $report) }}" class="text-decoration-none">
                                            {{ $report->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($report->frequency) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ strtoupper($report->format) }}</span>
                                    </td>
                                    <td>
                                        @if($report->next_generation_at)
                                            <small class="text-muted">{{ $report->next_generation_at->diffForHumans() }}</small>
                                        @else
                                            <small class="text-muted">Not scheduled</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($report->generatedReports->isNotEmpty())
                                            <div class="btn-group btn-group-sm" role="group">
                                                @foreach($report->generatedReports as $generated)
                                                    <a href="{{ route('reports.download', $generated) }}" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="{{ $generated->getPeriodDescription() }}">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <small class="text-muted">None yet</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .dashboard-widget {
            transition: all 0.3s ease;
        }
        .dashboard-widget.sortable-ghost {
            opacity: 0.4;
        }
        .dashboard-widget.sortable-drag {
            opacity: 0.8;
        }
        .widget-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .drag-handle {
            padding: 0.25rem 0.5rem;
            cursor: move;
            color: #6c757d;
        }
        .drag-handle:hover {
            color: #495057;
        }
    </style>
    <script>
        let collectionChart = null;
        let recyclingChart = null;
        let currentPeriod = 7;
        let sortableInstance = null;

        // Alpine.js Dashboard Customization Component
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardCustomization', () => ({
                customizationMode: false,
                saving: false,
                widgetVisibility: @json($metrics['preferences']['widget_visibility'] ?? \App\Models\DashboardPreference::getDefaultWidgetVisibility()),
                widgetOrder: @json($metrics['preferences']['widget_order'] ?? \App\Models\DashboardPreference::getDefaultWidgetOrder()),
                
                init() {
                    // Apply saved widget order on initialization
                    this.$nextTick(() => {
                        this.applyWidgetOrder();
                    });
                },
                
                toggleCustomizationMode() {
                    this.customizationMode = !this.customizationMode;
                    
                    if (this.customizationMode) {
                        this.enableDragAndDrop();
                    } else {
                        this.disableDragAndDrop();
                    }
                },
                
                enableDragAndDrop() {
                    const container = document.getElementById('dashboard-widgets');
                    if (!container) return;
                    
                    sortableInstance = Sortable.create(container, {
                        animation: 150,
                        handle: '.drag-handle',
                        draggable: '.dashboard-widget',
                        ghostClass: 'sortable-ghost',
                        dragClass: 'sortable-drag',
                        onEnd: (evt) => {
                            this.updateWidgetOrder();
                        }
                    });
                },
                
                disableDragAndDrop() {
                    if (sortableInstance) {
                        sortableInstance.destroy();
                        sortableInstance = null;
                    }
                },
                
                updateWidgetOrder() {
                    const widgets = document.querySelectorAll('.dashboard-widget');
                    const newOrder = [];
                    
                    widgets.forEach(widget => {
                        const widgetId = widget.getAttribute('data-widget-id');
                        if (widgetId) {
                            newOrder.push(widgetId);
                        }
                    });
                    
                    this.widgetOrder = newOrder;
                },
                
                applyWidgetOrder() {
                    const container = document.getElementById('dashboard-widgets');
                    if (!container) return;
                    
                    const widgets = Array.from(container.querySelectorAll('.dashboard-widget'));
                    
                    // Sort widgets based on saved order
                    this.widgetOrder.forEach((widgetId, index) => {
                        const widget = widgets.find(w => w.getAttribute('data-widget-id') === widgetId);
                        if (widget) {
                            container.appendChild(widget);
                        }
                    });
                },
                
                isWidgetVisible(widgetId) {
                    return this.widgetVisibility[widgetId] !== false;
                },
                
                toggleWidgetVisibility(widgetId) {
                    this.widgetVisibility[widgetId] = !this.isWidgetVisible(widgetId);
                },
                
                async savePreferences() {
                    this.saving = true;
                    
                    try {
                        const response = await fetch('{{ route("dashboard.preferences.save") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                widget_visibility: this.widgetVisibility,
                                widget_order: this.widgetOrder
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            showNotification('Dashboard preferences saved successfully', 'success');
                            this.customizationMode = false;
                            this.disableDragAndDrop();
                        } else {
                            showNotification(data.message || 'Failed to save preferences', 'error');
                        }
                    } catch (error) {
                        console.error('Error saving preferences:', error);
                        showNotification('Failed to save preferences', 'error');
                    } finally {
                        this.saving = false;
                    }
                },
                
                async resetToDefaults() {
                    if (!confirm('Are you sure you want to reset the dashboard to default layout? This will restore all widgets and their original order.')) {
                        return;
                    }
                    
                    this.saving = true;
                    
                    try {
                        // Reset to default values
                        const defaultVisibility = @json(\App\Models\DashboardPreference::getDefaultWidgetVisibility());
                        const defaultOrder = @json(\App\Models\DashboardPreference::getDefaultWidgetOrder());
                        
                        const response = await fetch('{{ route("dashboard.preferences.save") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                widget_visibility: defaultVisibility,
                                widget_order: defaultOrder
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // Update local state
                            this.widgetVisibility = defaultVisibility;
                            this.widgetOrder = defaultOrder;
                            
                            // Apply the default order
                            this.$nextTick(() => {
                                this.applyWidgetOrder();
                            });
                            
                            showNotification('Dashboard reset to defaults successfully', 'success');
                            this.customizationMode = false;
                            this.disableDragAndDrop();
                        } else {
                            showNotification(data.message || 'Failed to reset preferences', 'error');
                        }
                    } catch (error) {
                        console.error('Error resetting preferences:', error);
                        showNotification('Failed to reset preferences', 'error');
                    } finally {
                        this.saving = false;
                    }
                }
            }));
        });

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Charts are now initialized by dashboard-charts.js automatically
            // Just listen for chart period changes
            document.addEventListener('chartPeriodChange', function(e) {
                if (e.detail.chartId === 'collection-performance-chart') {
                    currentPeriod = e.detail.period;
                    // Refresh chart data via AJAX
                    refreshChartData(e.detail.chartId, e.detail.period);
                }
            });
        });

        // Refresh chart data via AJAX
        async function refreshChartData(chartId, period) {
            try {
                const canvas = document.getElementById(chartId);
                if (!canvas) return;
                
                const metricType = canvas.dataset.metricType || 'collection_trends';
                
                const params = new URLSearchParams({
                    period: period
                });
                
                const response = await fetch(`{{ route('dashboard.chart-data', ['metricType' => '__METRIC__']) }}`.replace('__METRIC__', metricType) + '?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && window.dashboardCharts) {
                    // Update chart using the dashboard charts module
                    await window.dashboardCharts.refresh(chartId, { period: period });
                }
            } catch (error) {
                console.error('Error refreshing chart:', error);
            }
        }

        // Refresh dashboard data via AJAX
        function refreshDashboard() {
            const refreshBtn = document.getElementById('refresh-dashboard');
            const originalContent = refreshBtn.innerHTML;
            
            // Show loading state
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Refreshing...';

            // Build query parameters
            const params = new URLSearchParams({
                period: currentPeriod + 'days'
            });

            fetch('{{ route("dashboard.metrics") }}?' + params.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.metrics) {
                    updateDashboardMetrics(data.metrics);
                    updateTimestamp(data.updated_at);
                    
                    // Show success feedback
                    showNotification('Dashboard refreshed successfully', 'success');
                }
            })
            .catch(error => {
                console.error('Error refreshing dashboard:', error);
                showNotification('Failed to refresh dashboard', 'error');
            })
            .finally(() => {
                // Restore button state
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = originalContent;
            });
        }

        // Update dashboard metrics with new data
        function updateDashboardMetrics(metrics) {
            // Update collection status cards
            updateMetricCard('Scheduled Collections', metrics.collection_metrics?.scheduled_today ?? 0);
            updateMetricCard('Completed Collections', metrics.collection_metrics?.completed_today ?? 0);
            updateMetricCard('Completion Rate', (metrics.collection_metrics?.completion_rate_today ?? 0).toFixed(1) + '%');
            updateMetricCard('Issues Reported', metrics.collection_metrics?.issues_today ?? 0);

            // Update pending items cards
            updateMetricCard('Unassigned Routes', metrics.pending_items?.unassigned_routes ?? 0);
            updateMetricCard('Pending Reports', metrics.pending_items?.pending_reports ?? 0);
            updateMetricCard('Trucks in Maintenance', metrics.pending_items?.trucks_in_maintenance ?? 0);
            updateMetricCard('Overdue Reports', metrics.pending_items?.overdue_reports ?? 0);

            // Update recycling metrics cards
            if (metrics.recycling_metrics) {
                updateMetricCard('Total Recyclables', (metrics.recycling_metrics.total_weight ?? 0).toFixed(1) + ' kg');
                updateMetricCard('Recycling Rate', (metrics.recycling_metrics.recycling_rate ?? 0).toFixed(1) + '%');
                updateMetricCard('Recycling Logs', metrics.recycling_metrics.total_logs ?? 0);
                updateMetricCard('Quality Issues', metrics.recycling_metrics.logs_with_quality_issues ?? 0);
            }

            // Update fleet metrics cards
            if (metrics.fleet_metrics) {
                updateMetricCard('Total Trucks', metrics.fleet_metrics.total_trucks ?? 0);
                updateMetricCard('Operational Trucks', metrics.fleet_metrics.operational ?? 0);
                updateMetricCard('Average Utilization', (metrics.fleet_metrics.average_utilization ?? 0).toFixed(1) + '%');
                updateMetricCard('Trucks with Assignments', metrics.fleet_metrics.trucks_with_assignments ?? 0);
            }

            // Update crew performance cards
            if (metrics.crew_performance) {
                updateMetricCard('Active Crew Members', metrics.crew_performance.active_crew_count ?? 0);
                updateMetricCard('Avg Collections per Crew', (metrics.crew_performance.avg_collections_per_crew ?? 0).toFixed(1));
                updateMetricCard('Total Collections', metrics.crew_performance.total_collections ?? 0);
                updateMetricCard('Crew with Issues', (metrics.crew_performance.crew_with_most_issues ?? []).length);
            }

            // Update report statistics cards
            if (metrics.report_statistics) {
                updateMetricCard('Total Reports', metrics.report_statistics.total_reports ?? 0);
                updateMetricCard('Pending Reports', metrics.report_statistics.by_status?.pending ?? 0);
                updateMetricCard('Resolved Reports', metrics.report_statistics.resolved_count ?? 0);
                const avgResTime = metrics.report_statistics.avg_resolution_time_hours;
                updateMetricCard('Avg Resolution Time', avgResTime !== null ? avgResTime.toFixed(1) + ' hrs' : 'N/A');
            }

            // Update route performance cards
            if (metrics.route_performance) {
                updateMetricCard('Total Routes Tracked', metrics.route_performance.total_routes_tracked ?? 0);
                updateMetricCard('Routes with Issues', (metrics.route_performance.routes_with_most_issues ?? []).length);
                updateMetricCard('Low Completion Routes', (metrics.route_performance.routes_with_lowest_completion ?? []).length);
                
                // Calculate average completion rate
                if (metrics.route_performance.all_routes && metrics.route_performance.all_routes.length > 0) {
                    const avgCompletionRate = metrics.route_performance.all_routes.reduce((sum, route) => sum + route.completion_rate, 0) / metrics.route_performance.all_routes.length;
                    updateMetricCard('Avg Completion Rate', avgCompletionRate.toFixed(1) + '%');
                }
            }

            // Update system usage statistics cards
            if (metrics.usage_statistics) {
                updateMetricCard('Total Active Users', metrics.usage_statistics.active_users_by_role?.total ?? 0);
                updateMetricCard('New Residents', metrics.usage_statistics.new_resident_registrations ?? 0);
                updateMetricCard('Recent Logins', metrics.usage_statistics.recent_logins ?? 0);
                updateMetricCard('Inactive Users', metrics.usage_statistics.inactive_users_30_days ?? 0);
            }

            // Refresh all charts with new data
            if (window.dashboardCharts) {
                window.dashboardCharts.refreshAll({ period: currentPeriod + 'days' });
            }
        }

        // Update individual metric card value
        function updateMetricCard(title, value) {
            const cards = document.querySelectorAll('.card-title');
            cards.forEach(card => {
                const subtitle = card.closest('.card-body')?.querySelector('.card-subtitle');
                if (subtitle && subtitle.textContent.trim() === title) {
                    card.textContent = value;
                }
            });
        }

        // Update timestamp display
        function updateTimestamp(timestamp) {
            const timestampEl = document.getElementById('update-timestamp');
            if (timestampEl && timestamp) {
                const date = new Date(timestamp);
                timestampEl.textContent = date.toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
            }
        }

        // Show notification toast
        function showNotification(message, type = 'info') {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
            toast.style.zIndex = '9999';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}-fill me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
    @endpush
</x-app-layout>
