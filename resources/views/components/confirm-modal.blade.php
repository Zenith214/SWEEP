@props([
    'id' => 'confirmModal',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmClass' => 'btn-danger',
    'formId' => null,
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    {{ $title }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">{{ $message }}</p>
                {{ $slot }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ $cancelText }}
                </button>
                @if($formId)
                    <button type="button" class="btn {{ $confirmClass }}" onclick="document.getElementById('{{ $formId }}').submit();">
                        {{ $confirmText }}
                    </button>
                @else
                    <button type="button" class="btn {{ $confirmClass }}" id="{{ $id }}ConfirmBtn">
                        {{ $confirmText }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
