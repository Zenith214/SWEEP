@props([
    'items' => []
])

@if(count($items) > 0)
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
        @foreach($items as $index => $item)
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">
                    @if(isset($item['icon']))
                        <i class="bi bi-{{ $item['icon'] }} me-1"></i>
                    @endif
                    {{ $item['label'] }}
                </li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ $item['url'] }}" class="text-decoration-none">
                        @if(isset($item['icon']))
                            <i class="bi bi-{{ $item['icon'] }} me-1"></i>
                        @endif
                        {{ $item['label'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif

<style>
    .breadcrumb {
        margin-bottom: 0;
    }
    
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #6c757d;
    }
    
    .breadcrumb-item a {
        color: var(--sweep-primary);
        transition: color 0.2s ease;
    }
    
    .breadcrumb-item a:hover {
        color: var(--sweep-accent);
    }
    
    .breadcrumb-item.active {
        color: #6c757d;
    }
</style>
