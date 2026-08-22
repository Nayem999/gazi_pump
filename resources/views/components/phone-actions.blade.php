@props(['phone'])

@php
    $digits = preg_replace('/\D/', '', (string) $phone);
    $whatsappNumber = match (true) {
        $digits === '' => null,
        str_starts_with($digits, '880') => $digits,
        str_starts_with($digits, '0') => '880'.substr($digits, 1),
        default => $digits,
    };
@endphp

@if ($phone)
    <span class="d-inline-flex align-items-center gap-1">
        <span>{{ $phone }}</span>
        <button type="button" class="btn btn-sm btn-link p-0 text-secondary" data-copy="{{ $phone }}" title="Copy phone number">
            <i class="ti ti-copy"></i>
        </button>
        @if ($whatsappNumber)
            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener" class="btn btn-sm btn-link p-0 text-success" title="Contact on WhatsApp">
                <i class="ti ti-brand-whatsapp"></i>
            </a>
        @endif
    </span>
@else
    <span class="text-muted">&mdash;</span>
@endif
