@props([
    'title',
    'columns' => [],
    'rows' => [],
    'sortable' => true,
    'drillDown' => true,
    'emptyMessage' => 'No data available',
    'maxHeight' => null,
    'striped' => true,
    'hover' => true
])

<div class="card border-0 shadow-sm" role="region" aria-labelledby="table-title-{{ Str::slug($title) }}">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0" id="table-title-{{ Str::slug($title) }}">{{ $title }}</h5>
    </div>
    
    <div class="card-body p-0">
        @if(count($rows) === 0)
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1 mb-3 d-block" aria-hidden="true"></i>
                <p class="text-muted mb-0">{{ $emptyMessage }}</p>
            </div>
        @else
            <div class="table-responsive" @if($maxHeight) style="max-height: {{ $maxHeight }}px; overflow-y: auto;" @endif>
                <table class="table mb-0 {{ $striped ? 'table-striped' : '' }} {{ $hover ? 'table-hover' : '' }}"
                       role="table"
                       aria-label="{{ $title }}">
                    <thead class="table-light sticky-top">
                        <tr role="row">
                            @foreach($columns as $column)
                                @php
                                    $columnKey = $column['key'] ?? '';
                                    $columnLabel = $column['label'] ?? '';
                                    $columnSortable = $sortable && ($column['sortable'] ?? true);
                                    $columnAlign = $column['align'] ?? 'left';
                                @endphp
                                
                                <th scope="col" 
                                    class="text-{{ $columnAlign }} {{ $columnSortable ? 'sortable' : '' }}"
                                    @if($columnSortable)
                                        role="columnheader"
                                        aria-sort="none"
                                        tabindex="0"
                                        onclick="sortTable('{{ Str::slug($title) }}', '{{ $columnKey }}')"
                                        onkeypress="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); sortTable('{{ Str::slug($title) }}', '{{ $columnKey }}'); }"
                                        style="cursor: pointer; user-select: none;"
                                        data-column="{{ $columnKey }}"
                                    @endif>
                                    {{ $columnLabel }}
                                    @if($columnSortable)
                                        <i class="bi bi-arrow-down-up ms-1 text-muted sort-icon" aria-hidden="true"></i>
                                    @endif
                                </th>
                            @endforeach
                            
                            @if($drillDown)
                                <th scope="col" class="text-end" style="width: 50px;">
                                    <span class="visually-hidden">Actions</span>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $rowIndex => $row)
                            <tr role="row"
                                @if($drillDown && isset($row['link']))
                                    class="cursor-pointer"
                                    onclick="window.location.href='{{ $row['link'] }}'"
                                    tabindex="0"
                                    onkeypress="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='{{ $row['link'] }}'; }"
                                    style="cursor: pointer;"
                                @endif>
                                @foreach($columns as $column)
                                    @php
                                        $columnKey = $column['key'] ?? '';
                                        $columnAlign = $column['align'] ?? 'left';
                                        $cellValue = $row[$columnKey] ?? '';
                                        $cellType = $column['type'] ?? 'text';
                                    @endphp
                                    
                                    <td class="text-{{ $columnAlign }}" role="cell">
                                        @if($cellType === 'badge')
                                            @php
                                                $badgeColor = $row[$columnKey . '_color'] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">{{ $cellValue }}</span>
                                        @elseif($cellType === 'icon')
                                            <i class="bi bi-{{ $cellValue }}" aria-label="{{ $cellValue }}"></i>
                                        @elseif($cellType === 'number')
                                            {{ number_format($cellValue) }}
                                        @elseif($cellType === 'currency')
                                            ${{ number_format($cellValue, 2) }}
                                        @elseif($cellType === 'percentage')
                                            {{ number_format($cellValue, 1) }}%
                                        @elseif($cellType === 'date')
                                            {{ \Carbon\Carbon::parse($cellValue)->format('M d, Y') }}
                                        @elseif($cellType === 'datetime')
                                            {{ \Carbon\Carbon::parse($cellValue)->format('M d, Y g:i A') }}
                                        @else
                                            {{ $cellValue }}
                                        @endif
                                    </td>
                                @endforeach
                                
                                @if($drillDown && isset($row['link']))
                                    <td class="text-end" role="cell">
                                        <i class="bi bi-chevron-right text-muted" aria-hidden="true"></i>
                                    </td>
                                @elseif($drillDown)
                                    <td></td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    
    @if(count($rows) > 0)
        <div class="card-footer bg-white border-top text-muted small">
            Showing {{ count($rows) }} {{ Str::plural('item', count($rows)) }}
        </div>
    @endif
</div>

@once
    @push('styles')
    <style>
        .sortable:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .sortable:focus {
            outline: 2px solid var(--sweep-primary);
            outline-offset: -2px;
        }
        
        .sortable .sort-icon {
            font-size: 0.75rem;
            transition: transform 0.2s ease;
        }
        
        .sortable[aria-sort="ascending"] .sort-icon {
            transform: rotate(180deg);
        }
        
        .sortable[aria-sort="descending"] .sort-icon {
            transform: rotate(0deg);
        }
        
        .cursor-pointer {
            cursor: pointer;
        }
        
        tbody tr.cursor-pointer:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        tbody tr.cursor-pointer:focus {
            outline: 2px solid var(--sweep-primary);
            outline-offset: -2px;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script>
        function sortTable(tableId, columnKey) {
            const table = document.querySelector(`#table-title-${tableId}`).closest('.card').querySelector('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const header = table.querySelector(`th[data-column="${columnKey}"]`);
            const currentSort = header.getAttribute('aria-sort');
            
            // Reset all headers
            table.querySelectorAll('th[aria-sort]').forEach(th => {
                th.setAttribute('aria-sort', 'none');
            });
            
            // Determine new sort direction
            const newSort = currentSort === 'ascending' ? 'descending' : 'ascending';
            header.setAttribute('aria-sort', newSort);
            
            // Get column index
            const columnIndex = Array.from(header.parentElement.children).indexOf(header);
            
            // Sort rows
            rows.sort((a, b) => {
                const aValue = a.children[columnIndex].textContent.trim();
                const bValue = b.children[columnIndex].textContent.trim();
                
                // Try to parse as numbers
                const aNum = parseFloat(aValue.replace(/[^0-9.-]/g, ''));
                const bNum = parseFloat(bValue.replace(/[^0-9.-]/g, ''));
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return newSort === 'ascending' ? aNum - bNum : bNum - aNum;
                }
                
                // String comparison
                return newSort === 'ascending' 
                    ? aValue.localeCompare(bValue)
                    : bValue.localeCompare(aValue);
            });
            
            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
    @endpush
@endonce
