@extends('layouts.portal')

@section('title', 'Home')

@section('content')
    <div class="bg-dark text-white py-5">
        <div class="container py-4 text-center">
            <h1 class="display-5 fw-bold">{{ config('app.name') }}</h1>
            <p class="lead text-white-50">Reliable fuel pumps and equipment, backed by a nationwide dealer and service network.</p>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <a href="{{ route('portal.products.index') }}" class="btn btn-primary btn-lg">Browse Products</a>
                <a href="{{ route('portal.dealer-locator') }}" class="btn btn-outline-light btn-lg">Find a Dealer</a>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Featured Products</h3>
            <a href="{{ route('portal.products.index') }}" class="small">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            @forelse ($featuredProducts as $product)
                <div class="col">
                    <div class="card h-100">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" class="card-img-top" style="height:180px;object-fit:cover" alt="{{ $product->name }}">
                        @else
                            <div class="bg-body-secondary d-flex align-items-center justify-content-center" style="height:180px">
                                <i class="ti ti-package fs-1 text-muted"></i>
                            </div>
                        @endif
                        <div class="card-body">
                            <h6 class="card-title mb-1">{{ $product->name }}</h6>
                            <p class="text-muted small mb-2">{{ $product->category?->name }}</p>
                            <a href="{{ route('portal.products.show', $product) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No products available yet.</p>
            @endforelse
        </div>
    </div>

    @if ($activePromotions->isNotEmpty())
        <div class="bg-body-tertiary py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0">Current Promotions</h3>
                    <a href="{{ route('portal.promotions.index') }}" class="small">View All &rarr;</a>
                </div>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    @foreach ($activePromotions as $promotion)
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $promotion->title }}</h6>
                                    <p class="card-text small text-muted">{{ \Illuminate\Support\Str::limit($promotion->description, 100) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Latest News</h3>
            <a href="{{ route('portal.news.index') }}" class="small">View All &rarr;</a>
        </div>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @forelse ($latestNews as $article)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">{{ $article->title }}</h6>
                            <p class="text-muted small">{{ $article->published_at?->format('d M Y') }}</p>
                            <p class="card-text small">{{ \Illuminate\Support\Str::limit($article->excerpt, 100) }}</p>
                            <a href="{{ route('portal.news.show', $article) }}" class="small">Read More &rarr;</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No news yet.</p>
            @endforelse
        </div>
    </div>
@endsection
