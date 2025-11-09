@props(['metric', 'context' => 'data'])

@php
    use App\Helpers\DashboardErrorHelper;
    
    $errorInfo = DashboardErrorHelper::formatErrorForDisplay($metric, $context);
@endphp

@if($errorInfo['has_error'] ?? false)
    <div {{ $attributes->merge(['class' => 'rounded-lg border p-4 ' . DashboardErrorHelper::getContainerClass(true)]) }}>
        <div class="flex items-start">
            <svg class="h-5 w-5 {{ DashboardErrorHelper::getIconClass(true) }} mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1">
                <h4 class="font-semibold mb-1">Unable to Load Data</h4>
                <p class="text-sm mb-2">{{ $errorInfo['error_message'] }}</p>
                
                @if(!empty($errorInfo['suggestions']))
                    <div class="text-sm mt-2">
                        <p class="font-medium mb-1">Suggestions:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errorInfo['suggestions'] as $suggestion)
                                <li>{{ $suggestion }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@elseif($errorInfo['is_empty'] ?? false)
    <div {{ $attributes->merge(['class' => 'rounded-lg border p-4 ' . DashboardErrorHelper::getContainerClass(false)]) }}>
        <div class="flex items-start">
            <svg class="h-5 w-5 {{ DashboardErrorHelper::getIconClass(false) }} mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1">
                <h4 class="font-semibold mb-1">No Data Available</h4>
                <p class="text-sm mb-2">{{ $errorInfo['empty_message'] }}</p>
                
                @if(!empty($errorInfo['suggestions']))
                    <div class="text-sm mt-2">
                        <p class="font-medium mb-1">Try:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errorInfo['suggestions'] as $suggestion)
                                <li>{{ $suggestion }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
