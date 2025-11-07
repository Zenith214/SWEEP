@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div class="flex-grow-1">
                <strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
