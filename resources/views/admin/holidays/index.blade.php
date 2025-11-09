@section('title', 'Holiday Management')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="holidays" />
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Holiday Management</h1>
        <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Holiday
        </a>
    </div>

    <!-- Holidays Table -->
    <div class="card">
        <div class="card-body">
            @if($holidays->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Holiday Name</th>
                                <th>Date</th>
                                <th>Collection Status</th>
                                <th>Reschedule Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($holidays as $holiday)
                                <tr>
                                    <td>
                                        <strong>{{ $holiday->name }}</strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar-event"></i>
                                        {{ $holiday->date->format('F j, Y') }}
                                        <small class="text-muted">({{ $holiday->date->format('l') }})</small>
                                    </td>
                                    <td>
                                        @if($holiday->is_collection_skipped)
                                            <span class="badge" style="background-color: var(--sweep-accent);">
                                                <i class="bi bi-x-circle"></i> Collection Skipped
                                            </span>
                                        @else
                                            <span class="badge" style="background-color: var(--sweep-primary);">
                                                <i class="bi bi-arrow-right-circle"></i> Rescheduled
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($holiday->reschedule_date)
                                            <i class="bi bi-calendar-check"></i>
                                            {{ $holiday->reschedule_date->format('F j, Y') }}
                                            <small class="text-muted">({{ $holiday->reschedule_date->format('l') }})</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.holidays.edit', $holiday) }}" 
                                               class="btn btn-sm btn-outline-secondary"
                                               data-bs-toggle="tooltip"
                                               title="Edit Holiday">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal{{ $holiday->id }}"
                                                    title="Delete Holiday">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Confirmation Modal -->
                                        <div class="modal fade" id="deleteModal{{ $holiday->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete the holiday <strong>{{ $holiday->name }}</strong>?</p>
                                                        <p class="text-muted mb-0">This will remove the holiday exception from all schedules.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form method="POST" action="{{ route('admin.holidays.destroy', $holiday) }}" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete Holiday</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $holidays->firstItem() }} to {{ $holidays->lastItem() }} of {{ $holidays->total() }} holidays
                    </div>
                    <div>
                        {{ $holidays->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No Holidays Defined</h4>
                    <p class="text-muted">
                        Add holidays to manage collection schedule exceptions.
                    </p>
                    <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> Add First Holiday
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize Bootstrap tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    @endpush
</x-app-layout>
