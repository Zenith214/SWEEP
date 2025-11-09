@props([
    'costs' => [],
    'customizationMode' => false,
])

<div class="mb-4 dashboard-widget" 
     data-widget-id="operational_costs"
     x-show="isWidgetVisible('operational_costs')"
     x-transition>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h5 mb-0">
            <i class="bi bi-currency-dollar"></i> Operational Costs Summary
        </h3>
        <div x-show="customizationMode" class="widget-controls">
            <button type="button" 
                    class="btn btn-sm btn-outline-secondary me-2"
                    @click="toggleWidgetVisibility('operational_costs')"
                    aria-label="Hide widget">
                <i class="bi bi-eye-slash"></i> Hide
            </button>
            <span class="drag-handle" style="cursor: move;">
                <i class="bi bi-grip-vertical"></i>
            </span>
        </div>
    </div>

    @if(isset($costs['available']) && $costs['available'] === false)
        <!-- Cost data not available -->
        <div class="alert alert-info" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Cost Tracking Not Available</strong>
                    <p class="mb-0 small">
                        {{ $costs['message'] ?? 'Operational cost tracking is not yet configured for this system.' }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Cost metrics cards -->
        <div class="row g-3">
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Total Costs"
                    :value="'$' . number_format($costs['total_costs'] ?? 0, 2)"
                    icon="currency-dollar"
                    bgColor="primary"
                    subtitle="Current period"
                    :comparison="$costs['comparison']['total_costs']['percentage_change'] ?? null"
                    comparisonLabel="vs previous period"
                    :trend="$costs['comparison']['total_costs']['trend'] ?? null"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Cost per Collection"
                    :value="'$' . number_format($costs['cost_per_collection'] ?? 0, 2)"
                    icon="calculator"
                    bgColor="info"
                    subtitle="Average cost"
                    :comparison="$costs['comparison']['cost_per_collection']['percentage_change'] ?? null"
                    comparisonLabel="vs previous period"
                    :trend="$costs['comparison']['cost_per_collection']['trend'] ?? null"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Fuel Costs"
                    :value="'$' . number_format($costs['cost_breakdown']['fuel'] ?? 0, 2)"
                    icon="fuel-pump"
                    bgColor="warning"
                    subtitle="Fuel expenses"
                    :comparison="$costs['comparison']['fuel_costs']['percentage_change'] ?? null"
                    comparisonLabel="vs previous period"
                    :trend="$costs['comparison']['fuel_costs']['trend'] ?? null"
                />
            </div>
            
            <div class="col-md-3">
                <x-dashboard.metric-card
                    title="Maintenance Costs"
                    :value="'$' . number_format($costs['cost_breakdown']['maintenance'] ?? 0, 2)"
                    icon="wrench"
                    bgColor="danger"
                    subtitle="Maintenance expenses"
                    :comparison="$costs['comparison']['maintenance_costs']['percentage_change'] ?? null"
                    comparisonLabel="vs previous period"
                    :trend="$costs['comparison']['maintenance_costs']['trend'] ?? null"
                />
            </div>
        </div>

        <!-- Cost Breakdown Details -->
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Cost Breakdown by Category</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($costs['cost_breakdown']) && array_sum($costs['cost_breakdown']) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalCosts = array_sum($costs['cost_breakdown']);
                                        @endphp
                                        @foreach($costs['cost_breakdown'] as $category => $amount)
                                            @if($amount > 0)
                                            <tr>
                                                <td>
                                                    <i class="bi 
                                                        @if($category === 'fuel') bi-fuel-pump text-warning
                                                        @elseif($category === 'maintenance') bi-wrench text-danger
                                                        @elseif($category === 'labor') bi-people text-primary
                                                        @else bi-currency-dollar text-secondary
                                                        @endif
                                                    "></i>
                                                    {{ ucfirst($category) }}
                                                </td>
                                                <td class="text-end fw-bold">${{ number_format($amount, 2) }}</td>
                                                <td class="text-end">
                                                    {{ $totalCosts > 0 ? number_format(($amount / $totalCosts) * 100, 1) : 0 }}%
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                        <tr class="table-active fw-bold">
                                            <td>Total</td>
                                            <td class="text-end">${{ number_format($totalCosts, 2) }}</td>
                                            <td class="text-end">100%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <p class="mb-0">No cost data available for this period</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                @if(isset($costs['trend_data']) && count($costs['trend_data']) > 0)
                    <x-dashboard.chart-widget
                        title="Cost Trends Over Time"
                        chartId="cost-trends-chart"
                        chartType="line"
                        height="300"
                        description="Daily operational costs over the selected period"
                        :data="$costs['trend_data']"
                    >
                        Operational cost trends showing daily expenses.
                    </x-dashboard.chart-widget>
                @else
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Cost Trends Over Time</h5>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div class="text-center text-muted">
                                <i class="bi bi-graph-up fs-1 d-block mb-2"></i>
                                <p class="mb-0">No trend data available</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Cost Comparison Summary -->
        @if(isset($costs['comparison']) && !empty($costs['comparison']))
        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Period Comparison</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-currency-dollar fs-3 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">Total Costs Change</div>
                                        <div class="fw-bold">
                                            <x-dashboard.comparison-indicator 
                                                :value="$costs['comparison']['total_costs']['percentage_change'] ?? 0"
                                                :trend="$costs['comparison']['total_costs']['trend'] ?? 'stable'"
                                                :inverse="true"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-calculator fs-3 text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">Cost per Collection Change</div>
                                        <div class="fw-bold">
                                            <x-dashboard.comparison-indicator 
                                                :value="$costs['comparison']['cost_per_collection']['percentage_change'] ?? 0"
                                                :trend="$costs['comparison']['cost_per_collection']['trend'] ?? 'stable'"
                                                :inverse="true"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-fuel-pump fs-3 text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">Fuel Costs Change</div>
                                        <div class="fw-bold">
                                            <x-dashboard.comparison-indicator 
                                                :value="$costs['comparison']['fuel_costs']['percentage_change'] ?? 0"
                                                :trend="$costs['comparison']['fuel_costs']['trend'] ?? 'stable'"
                                                :inverse="true"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>
