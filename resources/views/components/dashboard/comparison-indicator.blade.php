@props([
    'value' => 0,
    'previousValue' => null,
    'format' => 'number', // number, percentage, currency
    'inverse' => false, // if true, decrease is good (e.g., for issues)
    'showArrow' => true,
    'showPercentage' => true,
])

@php
    $change = 0;
    $percentageChange = 0;
    $isImproving = null;
    $isSignificant = false;
    
    if ($previousValue !== null && $previousValue != 0) {
        $change = $value - $previousValue;
        $percentageChange = (($value - $previousValue) / abs($previousValue)) * 100;
        $isSignificant = abs($percentageChange) > 10;
        
        // Determine if change is improving
        if ($inverse) {
            $isImproving = $change < 0; // Decrease is good
        } else {
            $isImproving = $change > 0; // Increase is good
        }
    } elseif ($previousValue === 0 && $value > 0) {
        $percentageChange = 100;
        $isImproving = !$inverse;
        $isSignificant = true;
    }
    
    // Determine color based on improvement
    $colorClass = 'text-muted';
    $bgClass = 'bg-secondary';
    $iconClass = 'bi-dash';
    
    if ($isImproving !== null) {
        if ($isImproving) {
            $colorClass = 'text-success';
            $bgClass = 'bg-success';
            $iconClass = 'bi-arrow-up';
        } else {
            $colorClass = 'text-danger';
            $bgClass = 'bg-danger';
            $iconClass = 'bi-arrow-down';
        }
    }
    
    // Format the change value
    $formattedChange = match($format) {
        'percentage' => number_format(abs($change), 1) . '%',
        'currency' => '$' . number_format(abs($change), 2),
        default => number_format(abs($change), 0),
    };
@endphp

@if($previousValue !== null)
<div {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-1']) }}>
    @if($showArrow)
    <span class="badge {{ $bgClass }} bg-opacity-10 {{ $colorClass }} d-inline-flex align-items-center gap-1 px-2 py-1">
        @if($change != 0)
            <i class="bi {{ $iconClass }} {{ $isSignificant ? 'fw-bold' : '' }}"></i>
        @else
            <i class="bi bi-dash"></i>
        @endif
        
        @if($showPercentage)
            <span class="{{ $isSignificant ? 'fw-bold' : '' }}">
                {{ number_format(abs($percentageChange), 1) }}%
            </span>
        @else
            <span class="{{ $isSignificant ? 'fw-bold' : '' }}">
                {{ $change >= 0 ? '+' : '-' }}{{ $formattedChange }}
            </span>
        @endif
    </span>
    @endif
    
    @if($isSignificant)
    <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1" 
          title="Significant change (>10%)">
        <i class="bi bi-exclamation-circle"></i>
    </span>
    @endif
</div>
@endif
