@props([
    'icon' => 'ti-file',
    'iconColor' => 'primary',
    'image' => null,
    'title' => '',
    'titleUrl' => null,
    'subtitle' => null,
    'statusLabel' => null,
    'statusColor' => 'secondary',
])

<div {{ $attributes->merge(['class' => 'card h-100 hover-lift']) }}>
    <div class="card-body d-flex flex-column">
        <div class="d-flex align-items-start gap-3 mb-2">
            @if ($image)
                <img src="{{ $image }}" class="rounded flex-shrink-0" style="width:48px;height:48px;object-fit:cover">
            @else
                <div class="stat-card-icon bg-{{ $iconColor }}-subtle text-{{ $iconColor }} flex-shrink-0" style="width:48px;height:48px;font-size:1.1rem">
                    <i class="ti {{ $icon }}"></i>
                </div>
            @endif
            <div class="flex-grow-1 min-w-0">
                <h6 class="mb-0 text-truncate" title="{{ $title }}">
                    @if ($titleUrl)
                        <a href="{{ $titleUrl }}">{{ $title }}</a>
                    @else
                        {{ $title }}
                    @endif
                </h6>
                @if ($subtitle)
                    <div class="text-muted small text-truncate">{{ $subtitle }}</div>
                @endif
            </div>
            @if ($statusLabel)
                <span class="badge text-bg-{{ $statusColor }} flex-shrink-0">{{ $statusLabel }}</span>
            @endif
        </div>

        @isset($meta)
            <div class="small text-muted mb-3">
                {{ $meta }}
            </div>
        @endisset

        <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
            <div>{{ $checkbox ?? '' }}</div>
            <div class="btn-group btn-group-sm">
                {{ $actions ?? '' }}
            </div>
        </div>
    </div>
</div>
