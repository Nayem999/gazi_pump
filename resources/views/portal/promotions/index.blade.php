@extends('layouts.portal')

@section('title', 'Promotions')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Current Promotions</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @forelse ($promotions as $promotion)
                <div class="col">
                    <div class="card h-100">
                        @if ($promotion->imageUrl())
                            <img src="{{ $promotion->imageUrl() }}" class="card-img-top" style="height:160px;object-fit:cover" alt="{{ $promotion->title }}">
                        @endif
                        <div class="card-body">
                            <h6 class="card-title">{{ $promotion->title }}</h6>
                            @if ($promotion->starts_at || $promotion->ends_at)
                                <p class="text-muted small">
                                    {{ $promotion->starts_at?->format('M d, Y') }}
                                    @if ($promotion->ends_at) &ndash; {{ $promotion->ends_at->format('M d, Y') }} @endif
                                </p>
                            @endif
                            <p class="card-text small">{{ $promotion->description }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No active promotions right now.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $promotions->onEachSide(1)->links() }}</div>
    </div>
@endsection
