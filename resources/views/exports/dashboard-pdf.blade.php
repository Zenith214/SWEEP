<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            background-color: #1e40af;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24pt;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 11pt;
            opacity: 0.9;
        }
        
        .meta-info {
            background-color: #f3f4f6;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #1e40af;
        }
        
        .meta-info p {
            margin: 5px 0;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #1e40af;
        }
        
        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .metric-row {
            display: table-row;
        }
        
        .metric-cell {
            display: table-cell;
            width: 50%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .metric-label {
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .metric-value {
            font-size: 18pt;
            font-weight: bold;
            color: #1e40af;
        }
        
        .metric-comparison {
            font-size: 9pt;
            margin-top: 5px;
        }
        
        .comparison-positive {
            color: #059669;
        }
        
        .comparison-negative {
            color: #dc2626;
        }
        
        .comparison-neutral {
            color: #6b7280;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #e5e7eb;
        }
        
        td {
            padding: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">SWEEP - Solid Waste Evaluation and Efficiency Platform</div>
    </div>
    
    <!-- Meta Information -->
    <div class="meta-info">
        <p><strong>Generated:</strong> {{ $generatedAt->format('F j, Y g:i A') }}</p>
        @if(isset($metrics['metadata']))
        <p><strong>Period:</strong> {{ $metrics['metadata']['period_start'] }} to {{ $metrics['metadata']['period_end'] }}</p>
        @endif
        @if(isset($metrics['comparisons']['period_info']))
        <p><strong>Comparison Period:</strong> {{ $metrics['comparisons']['period_info']['previous_start'] }} to {{ $metrics['comparisons']['period_info']['previous_end'] }}</p>
        @endif
    </div>
    
    <!-- Collection Metrics -->
    @if(isset($metrics['collection_metrics']) && (!isset($preferences['widget_visibility']['collection_status']) || $preferences['widget_visibility']['collection_status']))
    <div class="section">
        <div class="section-title">Collection Metrics</div>
        <div class="metrics-grid">
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">Scheduled Collections</div>
                    <div class="metric-value">{{ $metrics['collection_metrics']['scheduled_today'] ?? 0 }}</div>
                    @if(isset($metrics['comparisons']['collections']['total']))
                    <div class="metric-comparison comparison-{{ $metrics['comparisons']['collections']['total']['is_improving'] ? 'positive' : 'negative' }}">
                        {{ $metrics['comparisons']['collections']['total']['percentage_change'] > 0 ? '+' : '' }}{{ number_format($metrics['comparisons']['collections']['total']['percentage_change'], 1) }}% vs previous period
                    </div>
                    @endif
                </div>
                <div class="metric-cell">
                    <div class="metric-label">Completed Collections</div>
                    <div class="metric-value">{{ $metrics['collection_metrics']['completed_today'] ?? 0 }}</div>
                </div>
            </div>
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">Completion Rate</div>
                    <div class="metric-value">{{ number_format($metrics['collection_metrics']['completion_rate_today'] ?? 0, 1) }}%</div>
                    @if(isset($metrics['comparisons']['collections']['completion_rate']))
                    <div class="metric-comparison comparison-{{ $metrics['comparisons']['collections']['completion_rate']['is_improving'] ? 'positive' : 'negative' }}">
                        {{ $metrics['comparisons']['collections']['completion_rate']['percentage_change'] > 0 ? '+' : '' }}{{ number_format($metrics['comparisons']['collections']['completion_rate']['percentage_change'], 1) }}% vs previous period
                    </div>
                    @endif
                </div>
                <div class="metric-cell">
                    <div class="metric-label">Issues Reported</div>
                    <div class="metric-value">{{ $metrics['collection_metrics']['issues_today'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Recycling Metrics -->
    @if(isset($metrics['recycling_metrics']) && (!isset($preferences['widget_visibility']['recycling_metrics']) || $preferences['widget_visibility']['recycling_metrics']))
    <div class="section">
        <div class="section-title">Recycling Performance</div>
        <div class="metrics-grid">
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">Total Recyclables</div>
                    <div class="metric-value">{{ number_format($metrics['recycling_metrics']['total_weight'] ?? 0, 1) }} kg</div>
                    @if(isset($metrics['comparisons']['recycling']['total_weight']))
                    <div class="metric-comparison comparison-{{ $metrics['comparisons']['recycling']['total_weight']['is_improving'] ? 'positive' : 'negative' }}">
                        {{ $metrics['comparisons']['recycling']['total_weight']['percentage_change'] > 0 ? '+' : '' }}{{ number_format($metrics['comparisons']['recycling']['total_weight']['percentage_change'], 1) }}% vs previous period
                    </div>
                    @endif
                </div>
                <div class="metric-cell">
                    <div class="metric-label">Recycling Rate</div>
                    <div class="metric-value">{{ number_format($metrics['recycling_metrics']['recycling_rate'] ?? 0, 1) }}%</div>
                    @if(isset($metrics['comparisons']['recycling']['recycling_rate']))
                    <div class="metric-comparison comparison-{{ $metrics['comparisons']['recycling']['recycling_rate']['is_improving'] ? 'positive' : 'negative' }}">
                        {{ $metrics['comparisons']['recycling']['recycling_rate']['percentage_change'] > 0 ? '+' : '' }}{{ number_format($metrics['comparisons']['recycling']['recycling_rate']['percentage_change'], 1) }}% vs previous period
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        @if(isset($metrics['recycling_metrics']['material_breakdown']) && count($metrics['recycling_metrics']['material_breakdown']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Material Type</th>
                    <th style="text-align: right;">Weight (kg)</th>
                    <th style="text-align: right;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($metrics['recycling_metrics']['material_breakdown'] as $material)
                <tr>
                    <td>{{ ucfirst($material['material_type']) }}</td>
                    <td style="text-align: right;">{{ number_format($material['weight'], 2) }}</td>
                    <td style="text-align: right;">{{ number_format($material['percentage'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
    
    <!-- Fleet Metrics -->
    @if(isset($metrics['fleet_metrics']) && (!isset($preferences['widget_visibility']['fleet_utilization']) || $preferences['widget_visibility']['fleet_utilization']))
    <div class="section">
        <div class="section-title">Fleet Utilization</div>
        <div class="metrics-grid">
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">Total Trucks</div>
                    <div class="metric-value">{{ $metrics['fleet_metrics']['total_trucks'] ?? 0 }}</div>
                </div>
                <div class="metric-cell">
                    <div class="metric-label">Operational Trucks</div>
                    <div class="metric-value">{{ $metrics['fleet_metrics']['operational'] ?? 0 }}</div>
                </div>
            </div>
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">Average Utilization</div>
                    <div class="metric-value">{{ number_format($metrics['fleet_metrics']['average_utilization'] ?? 0, 1) }}%</div>
                    @if(isset($metrics['comparisons']['fleet']['utilization']))
                    <div class="metric-comparison comparison-{{ $metrics['comparisons']['fleet']['utilization']['is_improving'] ? 'positive' : 'negative' }}">
                        {{ $metrics['comparisons']['fleet']['utilization']['percentage_change'] > 0 ? '+' : '' }}{{ number_format($metrics['comparisons']['fleet']['utilization']['percentage_change'], 1) }}% vs previous period
                    </div>
                    @endif
                </div>
                <div class="metric-cell">
                    <div class="metric-label">Trucks with Assignments</div>
                    <div class="metric-value">{{ $metrics['fleet_metrics']['trucks_with_assignments'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <div class="page-break"></div>
    
    <!-- Crew Performance -->
    @if(isset($metrics['crew_performance']) && (!isset($preferences['widget_visibility']['crew_performance']) || $preferences['widget_visibility']['crew_performance']))
    <div class="section">
        <div class="section-title">Crew Performance</div>
        <div class="metrics-grid">
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">Active Crew Members</div>
                    <div class="metric-value">{{ $metrics['crew_performance']['active_crew_count'] ?? 0 }}</div>
                </div>
                <div class="metric-cell">
                    <div class="metric-label">Avg Collections per Crew</div>
                    <div class="metric-value">{{ number_format($metrics['crew_performance']['avg_collections_per_crew'] ?? 0, 1) }}</div>
                </div>
            </div>
        </div>
        
        @if(isset($metrics['crew_performance']['top_performers']) && count($metrics['crew_performance']['top_performers']) > 0)
        <h4 style="margin: 15px 0 10px 0;">Top Performers</h4>
        <table>
            <thead>
                <tr>
                    <th>Crew Member</th>
                    <th style="text-align: right;">Collections</th>
                    <th style="text-align: right;">Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($metrics['crew_performance']['top_performers'], 0, 5) as $performer)
                <tr>
                    <td>{{ $performer['user_name'] }}</td>
                    <td style="text-align: right;">{{ $performer['completed'] }}/{{ $performer['total_collections'] }}</td>
                    <td style="text-align: right;">
                        <span class="badge badge-success">{{ number_format($performer['completion_rate'], 1) }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
    
    <!-- Report Statistics -->
    @if(isset($metrics['report_statistics']) && (!isset($preferences['widget_visibility']['report_statistics']) || $preferences['widget_visibility']['report_statistics']))
    <div class="section">
        <div class="section-title">Report Statistics</div>
        <div class="metrics-grid">
            <div class="metric-row">
                <div class="metric-cell">
                    <div class="metric-label">Total Reports</div>
                    <div class="metric-value">{{ $metrics['report_statistics']['total_reports'] ?? 0 }}</div>
                    @if(isset($metrics['comparisons']['reports']['total']))
                    <div class="metric-comparison comparison-{{ $metrics['comparisons']['reports']['total']['is_improving'] ? 'positive' : 'negative' }}">
                        {{ $metrics['comparisons']['reports']['total']['percentage_change'] > 0 ? '+' : '' }}{{ number_format($metrics['comparisons']['reports']['total']['percentage_change'], 1) }}% vs previous period
                    </div>
                    @endif
                </div>
                <div class="metric-cell">
                    <div class="metric-label">Avg Resolution Time</div>
                    <div class="metric-value">
                        {{ isset($metrics['report_statistics']['avg_resolution_time_hours']) && $metrics['report_statistics']['avg_resolution_time_hours'] !== null ? number_format($metrics['report_statistics']['avg_resolution_time_hours'], 1) . ' hrs' : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($metrics['report_statistics']['by_status']))
        <h4 style="margin: 15px 0 10px 0;">Report Status Breakdown</h4>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th style="text-align: right;">Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($metrics['report_statistics']['by_status'] as $status => $count)
                <tr>
                    <td>{{ ucfirst($status) }}</td>
                    <td style="text-align: right;">{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
    
    <!-- Route Performance -->
    @if(isset($metrics['route_performance']) && (!isset($preferences['widget_visibility']['route_performance']) || $preferences['widget_visibility']['route_performance']))
    <div class="section">
        <div class="section-title">Route Performance</div>
        
        @if(isset($metrics['route_performance']['routes_with_lowest_completion']) && count($metrics['route_performance']['routes_with_lowest_completion']) > 0)
        <h4 style="margin: 15px 0 10px 0;">Routes with Lowest Completion Rates</h4>
        <table>
            <thead>
                <tr>
                    <th>Route Name</th>
                    <th>Zone</th>
                    <th style="text-align: right;">Completion Rate</th>
                    <th style="text-align: right;">Collections</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($metrics['route_performance']['routes_with_lowest_completion'], 0, 5) as $route)
                <tr>
                    <td>{{ $route['route_name'] }}</td>
                    <td>{{ $route['zone'] }}</td>
                    <td style="text-align: right;">
                        <span class="badge badge-danger">{{ number_format($route['completion_rate'], 1) }}%</span>
                    </td>
                    <td style="text-align: right;">{{ $route['completed'] }}/{{ $route['total_collections'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p>SWEEP Dashboard Report - Generated on {{ $generatedAt->format('F j, Y g:i A') }}</p>
        <p>Page <span class="pagenum"></span></p>
    </div>
</body>
</html>
