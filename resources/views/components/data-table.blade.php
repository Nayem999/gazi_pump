@props([
    'title' => '',
    'createUrl' => null,
    'createLabel' => 'Add New',
    'exportUrl' => null,
    'importUrl' => null,
    'printUrl' => null,
    'paginator' => null, // pass a LengthAwarePaginator to switch to server-side mode
    'total' => null, // pass a currency amount to show a "Total: ..." badge next to the title
    'totalLabel' => 'Total',
])

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="mb-0">
            {{ $title }}
            @if (! is_null($total))
                <span class="badge text-bg-primary ms-2 align-middle">{{ $totalLabel }}: ৳ {{ number_format((float) $total, 2) }}</span>
            @endif
        </h5>
        <div class="d-flex flex-wrap gap-2">
            @isset($cards)
                <div class="btn-group btn-group-sm" role="group" data-view-toggle aria-label="Display mode">
                    <button type="button" class="btn btn-outline-secondary" data-view-mode="list" title="List view"><i class="ti ti-list"></i></button>
                    <button type="button" class="btn btn-outline-secondary" data-view-mode="grid" title="Grid view"><i class="ti ti-layout-grid"></i></button>
                    <button type="button" class="btn btn-outline-secondary" data-view-mode="card" title="Card view"><i class="ti ti-layout-cards"></i></button>
                </div>
            @endisset
            @if ($importUrl)
                @if (str_starts_with($importUrl, '#'))
                    <a href="{{ $importUrl }}" data-bs-toggle="modal" data-bs-target="{{ $importUrl }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-upload me-1"></i>Import</a>
                @else
                    <a href="{{ $importUrl }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-upload me-1"></i>Import</a>
                @endif
            @endif
            @if ($exportUrl)
                <a href="{{ $exportUrl }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-download me-1"></i>Export</a>
            @endif
            @if ($printUrl)
                <a href="{{ $printUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="ti ti-printer me-1"></i>Print</a>
            @endif
            @if ($createUrl)
                <a href="{{ $createUrl }}" class="btn btn-primary btn-sm"><i class="ti ti-plus me-1"></i>{{ $createLabel }}</a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div data-view-panel="list">
            <div class="table-responsive">
                <table {{ $attributes->merge(['class' => 'table table-hover align-middle w-100 '.($paginator ? '' : 'data-table')]) }}>
                    <thead class="table-light">
                        {{ $thead }}
                    </thead>
                    <tbody>
                        {{ $slot }}
                    </tbody>
                </table>
            </div>
        </div>

        @isset($cards)
            <div data-view-panel="grid" class="d-none">
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                    {{ $cards }}
                </div>
            </div>

            <div data-view-panel="card" class="d-none">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                    {{ $cards }}
                </div>
            </div>
        @endisset

        @if ($paginator)
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                <div class="text-muted small">
                    Showing {{ $paginator->firstItem() ?? 0 }}&ndash;{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
                </div>
                {{ $paginator->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</div>
