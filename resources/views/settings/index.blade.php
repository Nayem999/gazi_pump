@extends('layouts.admin')

@section('title', 'Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
    <div class="mb-4">
        <h5 class="mb-1">Settings</h5>
        <p class="text-muted mb-0">
            Everything that is set up once and then left alone. Only the screens you have access to are listed.
        </p>
    </div>

    @foreach ($sections as $section)
        <div class="mb-4">
            <div class="text-uppercase text-muted small fw-semibold mb-2">{{ $section['label'] }}</div>

            <div class="row g-3">
                @foreach ($section['items'] as $item)
                    <div class="col-md-6 col-xl-4">
                        {{-- The whole card is the link, so the target is the
                             card rather than a few words inside it. --}}
                        <a href="{{ route($item['route']) }}" class="card h-100 text-decoration-none text-reset settings-tile">
                            <div class="card-body d-flex gap-3">
                                <div class="settings-tile-icon flex-shrink-0">
                                    <i class="ti {{ $item['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $item['label'] }}</div>
                                    @isset($item['description'])
                                        <div class="text-muted small">{{ $item['description'] }}</div>
                                    @endisset
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="text-muted small">
        Not finding something? It may need a permission your role does not have &mdash;
        <a href="{{ route('guide.index') }}">the guide</a> explains how access is decided.
    </div>
@endsection
