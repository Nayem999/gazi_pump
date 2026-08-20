@props([
    'action' => '',
    'method' => 'GET',
])

<form method="{{ $method }}" action="{{ $action }}" class="card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            {{ $slot }}
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1"></i>Filter
                </button>
                <a href="{{ $action }}" class="btn btn-outline-secondary">
                    <i class="ti ti-circle-x me-1"></i>Reset
                </a>
            </div>
        </div>
    </div>
</form>
