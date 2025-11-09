@section('title', 'Recycling Targets')

<x-app-layout>
    <x-slot name="sidebar">
        <x-admin-sidebar active="recycling-targets" />
    </x-slot>

    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-1">
                    <i class="bi bi-bullseye"></i> Recycling Targets
                </h1>
                <p class="text-muted mb-0">Set and track monthly recycling goals</p>
            </div>
        </div>

        <!-- Current Month Targets -->
        @if(count($currentTargets) > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-month"></i> Current Month Targets ({{ now()->format('F Y') }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($currentTargets as $target)
                            @php
                                $progressClass = 'secondary';
                                $progressBg = 'bg-secondary';
                                if ($target['is_achieved']) {
                                    $progressClass = 'success';
                                    $progressBg = 'bg-success';
                                } elseif ($target['progress_percentage'] >= 80) {
                                    $progressClass = 'warning';
                                    $progressBg = 'bg-warning';
                                }
                            @endphp
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="mb-1">
                                                    @if($target['material_type'])
                                                        <span class="badge material-badge material-{{ $target['material_type'] }}">
                                                            {{ ucfirst($target['material_type']) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-dark">
                                                            Total Recyclables
                                                        </span>
                                                    @endif
                                                </h6>
                                                <small class="text-muted">
                                                    Target: {{ number_format($target['target_weight'], 0) }} kg
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                @if($target['is_achieved'])
                                                    <i class="bi bi-check-circle-fill text-success fs-3"></i>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small text-muted">Progress</span>
                                                <span class="fw-bold">{{ number_format($target['actual_weight'], 0) }} kg</span>
                                            </div>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar {{ $progressBg }}" 
                                                     role="progressbar" 
                                                     style="width: {{ min($target['progress_percentage'], 100) }}%"
                                                     aria-valuenow="{{ $target['progress_percentage'] }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    {{ number_format($target['progress_percentage'], 0) }}%
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                @if($target['is_achieved'])
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-trophy"></i> Achieved
                                                    </span>
                                                @elseif($target['progress_percentage'] >= 80)
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-hourglass-split"></i> Almost There
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-arrow-right"></i> In Progress
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editTargetModal{{ $target['target_id'] }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form action="{{ route('admin.recycling.targets.destroy', $target['target_id']) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this target?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Target Modal -->
                                <div class="modal fade" id="editTargetModal{{ $target['target_id'] }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Target</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.recycling.targets.update', $target['target_id']) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Material Type</label>
                                                        <input type="text" class="form-control" 
                                                               value="{{ $target['material_type'] ? ucfirst($target['material_type']) : 'Total Recyclables' }}" 
                                                               disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Month</label>
                                                        <input type="text" class="form-control" 
                                                               value="{{ \Carbon\Carbon::parse($target['month'])->format('F Y') }}" 
                                                               disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="target_weight{{ $target['target_id'] }}" class="form-label">Target Weight (kg) *</label>
                                                        <input type="number" step="0.01" min="0.01" 
                                                               class="form-control @error('target_weight') is-invalid @enderror" 
                                                               id="target_weight{{ $target['target_id'] }}" 
                                                               name="target_weight" 
                                                               value="{{ old('target_weight', $target['target_weight']) }}" 
                                                               required>
                                                        @error('target_weight')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bi bi-save"></i> Update Target
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle"></i>
                No targets set for the current month. Create your first target below.
            </div>
        @endif

        <!-- Create New Target Form -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Create New Target
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.recycling.targets.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="month" class="form-label">Month *</label>
                            <input type="month" 
                                   class="form-control @error('month') is-invalid @enderror" 
                                   id="month" 
                                   name="month" 
                                   value="{{ old('month', now()->format('Y-m')) }}" 
                                   required>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select the target month</small>
                        </div>

                        <div class="col-md-4">
                            <label for="material_type" class="form-label">Material Type *</label>
                            <select class="form-select @error('material_type') is-invalid @enderror" 
                                    id="material_type" 
                                    name="material_type" 
                                    required>
                                <option value="">Select Material Type</option>
                                <option value="" {{ old('material_type') === '' ? 'selected' : '' }}>All Materials (Total)</option>
                                <option value="plastic" {{ old('material_type') === 'plastic' ? 'selected' : '' }}>Plastic</option>
                                <option value="paper" {{ old('material_type') === 'paper' ? 'selected' : '' }}>Paper</option>
                                <option value="glass" {{ old('material_type') === 'glass' ? 'selected' : '' }}>Glass</option>
                                <option value="metal" {{ old('material_type') === 'metal' ? 'selected' : '' }}>Metal</option>
                                <option value="cardboard" {{ old('material_type') === 'cardboard' ? 'selected' : '' }}>Cardboard</option>
                                <option value="organic" {{ old('material_type') === 'organic' ? 'selected' : '' }}>Organic</option>
                            </select>
                            @error('material_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Choose specific material or total</small>
                        </div>

                        <div class="col-md-4">
                            <label for="target_weight" class="form-label">Target Weight (kg) *</label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0.01" 
                                   class="form-control @error('target_weight') is-invalid @enderror" 
                                   id="target_weight" 
                                   name="target_weight" 
                                   value="{{ old('target_weight') }}" 
                                   placeholder="e.g., 1000.00" 
                                   required>
                            @error('target_weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter target in kilograms</small>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Create Target
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- All Targets History -->
        @if(count($allTargets) > 0)
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> All Targets
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Material Type</th>
                                    <th class="text-end">Target Weight (kg)</th>
                                    <th class="text-end">Current Progress</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allTargets as $target)
                                    @php
                                        $progress = $target->getCurrentProgress();
                                        $isAchieved = $target->isAchieved();
                                        $progressClass = 'secondary';
                                        if ($isAchieved) {
                                            $progressClass = 'success';
                                        } elseif ($progress >= 80) {
                                            $progressClass = 'warning';
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $target->month->format('F Y') }}</strong>
                                        </td>
                                        <td>
                                            @if($target->material_type)
                                                <span class="badge material-badge material-{{ $target->material_type }}">
                                                    {{ ucfirst($target->material_type) }}
                                                </span>
                                            @else
                                                <span class="badge bg-dark">
                                                    Total Recyclables
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ number_format($target->target_weight, 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <div class="progress me-2" style="width: 100px; height: 20px;">
                                                    <div class="progress-bar bg-{{ $progressClass }}" 
                                                         role="progressbar" 
                                                         style="width: {{ min($progress, 100) }}%">
                                                    </div>
                                                </div>
                                                <span>{{ number_format($progress, 0) }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($isAchieved)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Achieved
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-hourglass"></i> In Progress
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editAllTargetModal{{ $target->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('admin.recycling.targets.destroy', $target->id) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this target?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Target Modal for All Targets -->
                                    <div class="modal fade" id="editAllTargetModal{{ $target->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Target</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.recycling.targets.update', $target->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Material Type</label>
                                                            <input type="text" class="form-control" 
                                                                   value="{{ $target->material_type ? ucfirst($target->material_type) : 'Total Recyclables' }}" 
                                                                   disabled>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Month</label>
                                                            <input type="text" class="form-control" 
                                                                   value="{{ $target->month->format('F Y') }}" 
                                                                   disabled>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="target_weight_all_{{ $target->id }}" class="form-label">Target Weight (kg) *</label>
                                                            <input type="number" step="0.01" min="0.01" 
                                                                   class="form-control @error('target_weight') is-invalid @enderror" 
                                                                   id="target_weight_all_{{ $target->id }}" 
                                                                   name="target_weight" 
                                                                   value="{{ old('target_weight', $target->target_weight) }}" 
                                                                   required>
                                                            @error('target_weight')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-save"></i> Update Target
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        /* Material Type Badge Colors */
        .material-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }

        .material-plastic {
            background-color: #3B82F6;
            color: white;
        }

        .material-paper {
            background-color: #92400E;
            color: white;
        }

        .material-glass {
            background-color: #10B981;
            color: white;
        }

        .material-metal {
            background-color: #6B7280;
            color: white;
        }

        .material-cardboard {
            background-color: #D97706;
            color: white;
        }

        .material-organic {
            background-color: #84CC16;
            color: white;
        }
    </style>
    @endpush
</x-app-layout>
