@props([
    'title',
    'chartId',
    'chartType' => 'line',
    'height' => '300',
    'data' => null,
    'loading' => false,
    'error' => null,
    'description' => null,
    'filters' => false
])

<div class="card h-100 border-0 shadow-sm" role="region" aria-labelledby="chart-title-{{ $chartId }}">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0" id="chart-title-{{ $chartId }}">{{ $title }}</h5>
                @if($description)
                    <p class="small text-muted mb-0 mt-1">{{ $description }}</p>
                @endif
            </div>
            
            @if($filters)
                <div class="btn-group btn-group-sm" role="group" aria-label="Chart time period filter">
                    <button type="button" 
                            class="btn btn-outline-secondary active" 
                            data-period="7"
                            onclick="updateChart('{{ $chartId }}', 7)"
                            aria-pressed="true">
                        7 days
                    </button>
                    <button type="button" 
                            class="btn btn-outline-secondary" 
                            data-period="30"
                            onclick="updateChart('{{ $chartId }}', 30)"
                            aria-pressed="false">
                        30 days
                    </button>
                    <button type="button" 
                            class="btn btn-outline-secondary" 
                            data-period="90"
                            onclick="updateChart('{{ $chartId }}', 90)"
                            aria-pressed="false">
                        90 days
                    </button>
                </div>
            @endif
        </div>
    </div>
    
    <div class="card-body">
        @if($loading)
            <div class="d-flex justify-content-center align-items-center" style="height: {{ $height }}px;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading chart data...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading chart...</p>
                </div>
            </div>
        @elseif($error)
            <div class="alert alert-warning mb-0" role="alert" style="height: {{ $height }}px;">
                <div class="d-flex flex-column justify-content-center align-items-center h-100">
                    <i class="bi bi-exclamation-triangle fs-1 mb-3"></i>
                    <p class="mb-0">{{ $error }}</p>
                </div>
            </div>
        @else
            <div style="position: relative; height: {{ $height }}px;">
                <canvas id="{{ $chartId }}" 
                        role="img" 
                        aria-label="Chart showing {{ $title }}"
                        data-chart-type="{{ $chartType }}"
                        @if($data)
                            data-chart-data="{{ json_encode($data) }}"
                        @endif
                        data-metric-type="{{ str_replace('-chart', '', $chartId) }}">
                    <!-- Fallback content for screen readers -->
                    <p>{{ $title }} chart. Enable JavaScript to view interactive chart.</p>
                </canvas>
            </div>
            
            <!-- Text alternative for accessibility -->
            <div class="visually-hidden" role="region" aria-label="Chart data summary">
                @if($data && isset($data['labels']) && isset($data['values']))
                    <table>
                        <caption>{{ $title }} - Data Table</caption>
                        <thead>
                            <tr>
                                <th scope="col">{{ $data['labelColumn'] ?? 'Period' }}</th>
                                <th scope="col">{{ $data['valueColumn'] ?? 'Value' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['labels'] as $index => $label)
                                <tr>
                                    <th scope="row">{{ $label }}</th>
                                    <td>{{ $data['values'][$index] ?? 'N/A' }}{{ $data['unit'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{ $slot }}
                @endif
            </div>
        @endif
    </div>
</div>

@once
    @push('scripts')
    <script>
        // Chart initialization will be handled by the page-specific JavaScript
        // This component provides the structure and accessibility features
        
        function updateChart(chartId, period) {
            // Update active button state
            const buttons = document.querySelectorAll(`[data-period]`);
            buttons.forEach(btn => {
                btn.classList.remove('active');
                btn.setAttribute('aria-pressed', 'false');
            });
            
            const activeBtn = document.querySelector(`[data-period="${period}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
                activeBtn.setAttribute('aria-pressed', 'true');
            }
            
            // Trigger custom event for chart update
            const event = new CustomEvent('chartPeriodChange', {
                detail: { chartId, period }
            });
            document.dispatchEvent(event);
        }
    </script>
    @endpush
@endonce
