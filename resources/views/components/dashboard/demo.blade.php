{{-- Demo page to showcase dashboard components --}}
{{-- This file can be used for testing and removed before production --}}

<x-app-layout>
    <div class="container-fluid py-4">
        <h1 class="h2 mb-4">Dashboard Components Demo</h1>
        
        <p class="text-muted mb-4">
            This page demonstrates all available dashboard components with example data.
        </p>
        
        <!-- Filter Bar -->
        <section class="mb-5">
            <h2 class="h4 mb-3">Filter Bar</h2>
            <x-dashboard.filter-bar
                action="#"
                :filters="[
                    [
                        'type' => 'select',
                        'name' => 'status',
                        'label' => 'Status',
                        'icon' => 'funnel',
                        'options' => [
                            'active' => 'Active',
                            'pending' => 'Pending',
                            'completed' => 'Completed'
                        ]
                    ]
                ]"
            />
        </section>
        
        <!-- Metric Cards -->
        <section class="mb-5">
            <h2 class="h4 mb-3">Metric Cards</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <x-dashboard.metric-card
                        title="Total Collections"
                        value="1,234"
                        icon="truck"
                        bgColor="primary"
                        :comparison="12.5"
                        trend="up"
                        link="#"
                    />
                </div>
                <div class="col-md-3">
                    <x-dashboard.metric-card
                        title="Pending Reports"
                        value="45"
                        icon="exclamation-triangle"
                        bgColor="warning"
                        :comparison="-8.3"
                        trend="down"
                    />
                </div>
                <div class="col-md-3">
                    <x-dashboard.metric-card
                        title="Active Crew"
                        value="28"
                        icon="people"
                        bgColor="success"
                        :comparison="0"
                        trend="stable"
                    />
                </div>
                <div class="col-md-3">
                    <x-dashboard.metric-card
                        title="Recycling Rate"
                        value="87%"
                        icon="recycle"
                        bgColor="accent"
                        :comparison="5.2"
                        trend="up"
                        subtitle="Monthly average"
                    />
                </div>
            </div>
        </section>
        
        <!-- Alert Panel -->
        <section class="mb-5">
            <h2 class="h4 mb-3">Alert Panel</h2>
            <div class="row">
                <div class="col-md-6">
                    <x-dashboard.alert-panel
                        :alerts="[
                            [
                                'id' => 'alert-1',
                                'type' => 'warning',
                                'title' => 'Unassigned Routes',
                                'message' => 'There are 5 routes without crew assignments for tomorrow.',
                                'count' => 5,
                                'timestamp' => now()->subHours(2),
                                'link' => '#',
                                'linkText' => 'Assign crews'
                            ],
                            [
                                'id' => 'alert-2',
                                'type' => 'danger',
                                'title' => 'Overdue Reports',
                                'message' => 'Some resident reports have exceeded the resolution time target.',
                                'count' => 3,
                                'timestamp' => now()->subHours(5),
                                'link' => '#',
                                'linkText' => 'Review reports'
                            ],
                            [
                                'id' => 'alert-3',
                                'type' => 'info',
                                'title' => 'Maintenance Scheduled',
                                'message' => 'Truck #12 is scheduled for maintenance next week.',
                                'timestamp' => now()->subDay(),
                                'link' => '#'
                            ]
                        ]"
                    />
                </div>
            </div>
        </section>
        
        <!-- Chart Widgets -->
        <section class="mb-5">
            <h2 class="h4 mb-3">Chart Widgets</h2>
            <div class="row g-4">
                <div class="col-md-8">
                    <x-dashboard.chart-widget
                        title="Collection Completion Trends"
                        chartId="demo-trends-chart"
                        chartType="line"
                        :filters="true"
                        description="Daily collection completion rates over time"
                    >
                        Collection completion rates have improved from 85% to 92% over the past 30 days,
                        with an average of 88% completion rate.
                    </x-dashboard.chart-widget>
                </div>
                <div class="col-md-4">
                    <x-dashboard.chart-widget
                        title="Recycling Breakdown"
                        chartId="demo-recycling-chart"
                        chartType="pie"
                        description="Materials collected by type"
                    >
                        Recycling breakdown: Paper 40%, Plastic 30%, Glass 20%, Metal 10%.
                    </x-dashboard.chart-widget>
                </div>
            </div>
        </section>
        
        <!-- Data Table -->
        <section class="mb-5">
            <h2 class="h4 mb-3">Data Table</h2>
            <x-dashboard.data-table
                title="Top Performing Routes"
                :columns="[
                    ['key' => 'route_name', 'label' => 'Route Name', 'sortable' => true],
                    ['key' => 'zone', 'label' => 'Zone', 'sortable' => true],
                    ['key' => 'completion_rate', 'label' => 'Completion Rate', 'type' => 'percentage', 'align' => 'right', 'sortable' => true],
                    ['key' => 'collections', 'label' => 'Collections', 'type' => 'number', 'align' => 'right', 'sortable' => true],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge']
                ]"
                :rows="[
                    [
                        'route_name' => 'Downtown Route A',
                        'zone' => 'Zone 1',
                        'completion_rate' => 98.5,
                        'collections' => 145,
                        'status' => 'Active',
                        'status_color' => 'success',
                        'link' => '#'
                    ],
                    [
                        'route_name' => 'Suburban Route B',
                        'zone' => 'Zone 2',
                        'completion_rate' => 95.2,
                        'collections' => 132,
                        'status' => 'Active',
                        'status_color' => 'success',
                        'link' => '#'
                    ],
                    [
                        'route_name' => 'Industrial Route C',
                        'zone' => 'Zone 3',
                        'completion_rate' => 92.8,
                        'collections' => 98,
                        'status' => 'Active',
                        'status_color' => 'success',
                        'link' => '#'
                    ],
                    [
                        'route_name' => 'Residential Route D',
                        'zone' => 'Zone 1',
                        'completion_rate' => 88.3,
                        'collections' => 156,
                        'status' => 'Review',
                        'status_color' => 'warning',
                        'link' => '#'
                    ]
                ]"
            />
        </section>
        
        <!-- Export Button -->
        <section class="mb-5">
            <h2 class="h4 mb-3">Export Button</h2>
            <div class="d-flex gap-3">
                <x-dashboard.export-button
                    :formats="['pdf', 'csv']"
                    endpoint="#"
                    filename="dashboard-demo"
                />
                
                <x-dashboard.export-button
                    :formats="['pdf']"
                    endpoint="#"
                    filename="dashboard-demo"
                    variant="success"
                    label="Download PDF"
                    size="lg"
                />
            </div>
        </section>
        
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Note:</strong> This is a demo page. The components are functional but use placeholder data.
            See <code>resources/views/components/dashboard/README.md</code> for usage documentation.
        </div>
    </div>
</x-app-layout>
