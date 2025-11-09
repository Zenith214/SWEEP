@section('title', 'Edit Scheduled Report')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="scheduled-reports" />
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">Edit Scheduled Report</h1>
        <a href="{{ route('admin.scheduled-reports.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.scheduled-reports.update', $scheduledReport) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label for="name" class="form-label">Report Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $scheduledReport->name) }}" required
                        class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="frequency" class="form-label">Frequency</label>
                    <select name="frequency" id="frequency" required
                        class="form-select @error('frequency') is-invalid @enderror">
                        <option value="">Select frequency</option>
                        @foreach ($frequencies as $value => $label)
                            <option value="{{ $value }}" {{ old('frequency', $scheduledReport->frequency) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('frequency')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="format" class="form-label">Format</label>
                    <select name="format" id="format" required
                        class="form-select @error('format') is-invalid @enderror">
                        <option value="">Select format</option>
                        @foreach ($formats as $value => $label)
                            <option value="{{ $value }}" {{ old('format', $scheduledReport->format) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('format')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Metrics to Include</label>
                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        @foreach ($availableMetrics as $value => $label)
                            <div class="form-check">
                                <input type="checkbox" name="metrics[]" id="metric_{{ $value }}" value="{{ $value }}"
                                    {{ (is_array(old('metrics')) && in_array($value, old('metrics'))) || (!old('metrics') && in_array($value, $scheduledReport->metrics)) ? 'checked' : '' }}
                                    class="form-check-input">
                                <label for="metric_{{ $value }}" class="form-check-label">
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('metrics')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Select at least one metric to include in the report.</div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $scheduledReport->is_active) ? 'checked' : '' }}
                            class="form-check-input">
                        <label for="is_active" class="form-check-label">
                            Enable this scheduled report
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.scheduled-reports.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update Scheduled Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
