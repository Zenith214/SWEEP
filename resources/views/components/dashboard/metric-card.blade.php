@props([
    'title',
    'value',
    'icon' => 'graph-up',
    'bgColor' => 'primary',
    'comparison' => null,
    'comparisonLabel' => 'vs previous period',
    'trend' => null,
    'link' => null,
    'subtitle' => null,
    'loading' => false,
    'drillDownMetric' => null,
    'preserveContext' => true
])

@php
    $colorClasses = [
        'primary' => 'var(--sweep-primary)',
        'secondary' => 'var(--sweep-secondary)',
        'accent' => 'var(--sweep-accent)',
        'success' => 'var(--bs-success)',
        'warning' => 'var(--bs-warning)',
        'danger' => 'var(--bs-danger)',
        'info' => 'var(--bs-info)',
    ];
    
    $bgColorValue = $colorClasses[$bgColor] ?? $colorClasses['primary'];
    
    $trendIcon = match($trend) {
        'up', 'increasing' => 'arrow-up',
        'down', 'decreasing' => 'arrow-down',
        'stable' => 'dash',
        default => null
    };
    
    $trendColor = match($trend) {
        'up', 'increasing' => 'success',
        'down', 'decreasing' => 'danger',
        'stable' => 'secondary',
        default => 'secondary'
    };
@endphp

<div class="card h-100 border-0 shadow-sm {{ $link ? 'metric-card-clickable' : '' }}" 
     role="{{ $link ? 'button' : 'article' }}"
     tabindex="{{ $link ? '0' : '-1' }}"
     @if($link)
         onclick="handleMetricClick(event, '{{ $link }}', {{ $preserveContext ? 'true' : 'false' }})"
         onkeypress="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); handleMetricClick(event, '{{ $link }}', {{ $preserveContext ? 'true' : 'false' }}); }"
         style="cursor: pointer;"
         data-metric="{{ $drillDownMetric }}"
     @endif
     aria-label="{{ $title }}: {{ $value }}{{ $comparison ? ', ' . $comparison . '% ' . $comparisonLabel : '' }}">
    
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="flex-grow-1">
                <h6 class="card-subtitle mb-2 text-muted" id="metric-title-{{ Str::slug($title) }}">
                    {{ $title }}
                </h6>
                @if($subtitle)
                    <p class="small text-muted mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="rounded-circle p-3" 
                 style="background-color: {{ $bgColorValue }}; opacity: 0.1;"
                 aria-hidden="true">
                <i class="bi bi-{{ $icon }} fs-4" style="color: {{ $bgColorValue }};"></i>
            </div>
        </div>
        
        <div class="d-flex align-items-end justify-content-between">
            <div>
                @if($loading)
                    <div class="spinner-border spinner-border-sm text-muted" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                @else
                    <h2 class="card-title mb-0 fw-bold" aria-describedby="metric-title-{{ Str::slug($title) }}">
                        {{ $value }}
                    </h2>
                @endif
                
                @if($comparison !== null && !$loading)
                    <div class="d-flex align-items-center mt-2">
                        @if($trendIcon)
                            <i class="bi bi-{{ $trendIcon }}-circle-fill text-{{ $trendColor }} me-1" 
                               aria-hidden="true"></i>
                        @endif
                        <span class="small text-{{ $trendColor }} fw-semibold" 
                              aria-label="{{ abs($comparison) }}% {{ $trend }} compared to {{ $comparisonLabel }}">
                            {{ $comparison > 0 ? '+' : '' }}{{ number_format($comparison, 1) }}%
                        </span>
                        <span class="small text-muted ms-1">{{ $comparisonLabel }}</span>
                    </div>
                @endif
            </div>
            
            @if($link)
                <i class="bi bi-arrow-right-circle text-muted" aria-hidden="true"></i>
            @endif
        </div>
    </div>
    
    @if($link)
        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>
                    View details
                </small>
                @if($preserveContext)
                    <small class="text-muted" title="Open in new tab">
                        <a href="{{ $link }}" 
                           target="_blank" 
                           onclick="event.stopPropagation(); openInNewTab(event, '{{ $link }}', {{ $preserveContext ? 'true' : 'false' }});"
                           class="text-decoration-none text-muted">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                        </a>
                    </small>
                @endif
            </div>
        </div>
    @endif
</div>

<style>
    .metric-card-clickable {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .metric-card-clickable:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .metric-card-clickable:focus {
        outline: 2px solid var(--sweep-primary);
        outline-offset: 2px;
    }
</style>
