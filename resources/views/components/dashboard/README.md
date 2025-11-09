# Dashboard Components

Reusable Blade components for building accessible, interactive dashboards in the SWEEP platform.

## Components

### 1. Metric Card (`<x-dashboard.metric-card />`)

Displays KPIs with comparison indicators and trend information.

**Props:**
- `title` (required): The metric title
- `value` (required): The metric value to display
- `icon`: Bootstrap icon name (default: 'graph-up')
- `bgColor`: Color theme (primary, secondary, accent, success, warning, danger, info)
- `comparison`: Percentage change from previous period
- `comparisonLabel`: Label for comparison (default: 'vs previous period')
- `trend`: Trend direction (up/increasing, down/decreasing, stable)
- `link`: URL for drill-down navigation
- `subtitle`: Additional descriptive text
- `loading`: Show loading state

**Example:**
```blade
<x-dashboard.metric-card
    title="Total Collections"
    value="1,234"
    icon="truck"
    bgColor="primary"
    :comparison="12.5"
    trend="up"
    link="{{ route('admin.collections.index') }}"
/>
```

### 2. Chart Widget (`<x-dashboard.chart-widget />`)

Wrapper for Chart.js visualizations with accessibility features.

**Props:**
- `title` (required): Chart title
- `chartId` (required): Unique ID for the canvas element
- `chartType`: Chart type (line, bar, pie, doughnut, etc.)
- `height`: Chart height in pixels (default: '300')
- `data`: Chart.js data object
- `loading`: Show loading state
- `error`: Error message to display
- `description`: Chart description
- `filters`: Show time period filter buttons

**Example:**
```blade
<x-dashboard.chart-widget
    title="Collection Trends"
    chartId="collection-trends-chart"
    chartType="line"
    height="400"
    :filters="true"
    description="Daily collection completion rates"
>
    <!-- Text alternative for accessibility -->
    Collection completion rates have increased from 85% to 92% over the past 30 days.
</x-dashboard.chart-widget>
```

### 3. Alert Panel (`<x-dashboard.alert-panel />`)

Displays notifications and alerts with dismissal functionality.

**Props:**
- `alerts`: Array of alert objects
- `title`: Panel title (default: 'Alerts & Notifications')
- `dismissible`: Allow dismissing alerts (default: true)
- `maxHeight`: Maximum height in pixels (default: '400')

**Alert Object Structure:**
```php
[
    'id' => 'unique-id',
    'type' => 'danger|warning|success|info',
    'title' => 'Alert Title',
    'message' => 'Alert message text',
    'count' => 5, // Optional badge count
    'timestamp' => '2024-01-15 10:30:00', // Optional
    'link' => '/path/to/details', // Optional
    'linkText' => 'View details' // Optional
]
```

**Example:**
```blade
<x-dashboard.alert-panel
    :alerts="$alerts"
    title="System Alerts"
    :dismissible="true"
/>
```

### 4. Data Table (`<x-dashboard.data-table />`)

Sortable data table with drill-down capabilities.

**Props:**
- `title` (required): Table title
- `columns`: Array of column definitions
- `rows`: Array of data rows
- `sortable`: Enable sorting (default: true)
- `drillDown`: Enable row click navigation (default: true)
- `emptyMessage`: Message when no data (default: 'No data available')
- `maxHeight`: Maximum height for scrolling
- `striped`: Striped rows (default: true)
- `hover`: Hover effect (default: true)

**Column Structure:**
```php
[
    'key' => 'column_key',
    'label' => 'Column Label',
    'sortable' => true,
    'align' => 'left|center|right',
    'type' => 'text|badge|icon|number|currency|percentage|date|datetime'
]
```

**Row Structure:**
```php
[
    'column_key' => 'value',
    'column_key_color' => 'success', // For badge type
    'link' => '/path/to/details' // For drill-down
]
```

**Example:**
```blade
<x-dashboard.data-table
    title="Top Performing Routes"
    :columns="[
        ['key' => 'route_name', 'label' => 'Route', 'sortable' => true],
        ['key' => 'completion_rate', 'label' => 'Completion', 'type' => 'percentage', 'align' => 'right'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'badge']
    ]"
    :rows="$routeData"
/>
```

### 5. Filter Bar (`<x-dashboard.filter-bar />`)

Date range and filter controls for dashboard data.

**Props:**
- `action`: Form action URL
- `method`: HTTP method (default: 'GET')
- `dateRange`: Show date range inputs (default: true)
- `startDate`: Initial start date
- `endDate`: Initial end date
- `filters`: Array of additional filter definitions
- `showReset`: Show reset button (default: true)
- `showApply`: Show apply button (default: true)

**Filter Structure:**
```php
[
    'type' => 'select|text|number',
    'name' => 'filter_name',
    'label' => 'Filter Label',
    'value' => 'current_value',
    'options' => ['value' => 'label'], // For select type
    'placeholder' => 'Placeholder text',
    'icon' => 'bootstrap-icon-name'
]
```

**Example:**
```blade
<x-dashboard.filter-bar
    action="{{ route('dashboard') }}"
    :startDate="request('start_date')"
    :endDate="request('end_date')"
    :filters="[
        [
            'type' => 'select',
            'name' => 'route_id',
            'label' => 'Route',
            'icon' => 'map',
            'value' => request('route_id'),
            'options' => $routes,
            'placeholder' => 'All Routes'
        ]
    ]"
/>
```

### 6. Export Button (`<x-dashboard.export-button />`)

Trigger data export in various formats.

**Props:**
- `formats`: Array of export formats (default: ['pdf', 'csv'])
- `endpoint`: Export endpoint URL (default: '/dashboard/export')
- `filename`: Base filename (default: 'dashboard-export')
- `size`: Button size (sm, md, lg)
- `variant`: Button variant (default: 'outline-primary')
- `label`: Button label (default: 'Export')
- `icon`: Bootstrap icon name (default: 'download')
- `includeFilters`: Include current filters in export (default: true)

**Example:**
```blade
<x-dashboard.export-button
    :formats="['pdf', 'csv', 'excel']"
    endpoint="{{ route('dashboard.export') }}"
    filename="admin-dashboard"
    label="Export Dashboard"
/>
```

## Accessibility Features

All components follow WCAG 2.1 AA standards:

- **Keyboard Navigation**: All interactive elements are keyboard accessible
- **ARIA Labels**: Proper ARIA attributes for screen readers
- **Focus Indicators**: Clear visual focus states
- **Color Contrast**: 4.5:1 minimum contrast ratio
- **Screen Reader Support**: Text alternatives for visual content
- **Semantic HTML**: Proper use of semantic elements and roles

## Usage Example

Complete dashboard page example:

```blade
<x-app-layout>
    <h1 class="h2 mb-4">Dashboard</h1>
    
    <!-- Filters -->
    <x-dashboard.filter-bar
        action="{{ route('dashboard') }}"
        :startDate="$startDate"
        :endDate="$endDate"
    />
    
    <!-- Alerts -->
    <x-dashboard.alert-panel :alerts="$alerts" />
    
    <!-- Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <x-dashboard.metric-card
                title="Total Collections"
                :value="$metrics['total_collections']"
                icon="truck"
                :comparison="$metrics['collections_change']"
                trend="up"
            />
        </div>
        <!-- More metric cards... -->
    </div>
    
    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <x-dashboard.chart-widget
                title="Collection Trends"
                chartId="trends-chart"
                :filters="true"
            />
        </div>
        <div class="col-md-4">
            <x-dashboard.chart-widget
                title="Recycling Breakdown"
                chartId="recycling-chart"
                chartType="pie"
            />
        </div>
    </div>
    
    <!-- Data Table -->
    <x-dashboard.data-table
        title="Recent Collections"
        :columns="$tableColumns"
        :rows="$tableRows"
    />
    
    <!-- Export -->
    <div class="mt-4">
        <x-dashboard.export-button />
    </div>
</x-app-layout>
```
