@props([
    'icon' => 'ti-trending-up',
    'label' => '',
    'value' => 0,
    'color' => 'primary',
    'trend' => null,
])

<div {{ $attributes->merge(['class' => 'stat-card p-3 h-100']) }}>
    <div class="d-flex align-items-center gap-3">
        <div class="stat-card-icon bg-{{ $color }}-subtle text-{{ $color }}">
            <i class="ti {{ $icon }}"></i>
        </div>
        <div>
            <div class="text-muted small">{{ $label }}</div>
            <div class="stat-card-value" @if (is_numeric($value)) data-countup="{{ $value }}" @endif>{{ $value }}</div>
            @if ($trend !== null)
                <div class="small {{ $trend >= 0 ? 'text-success' : 'text-danger' }}">
                    <i class="ti {{ $trend >= 0 ? 'ti-arrow-up' : 'ti-arrow-down' }}"></i>
                    {{ abs($trend) }}%
                </div>
            @endif
        </div>
    </div>
</div>
