@extends('layouts.portal')

@section('title', 'Products')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Our Products</h1>

        <form method="GET" action="{{ route('portal.products.index') }}" class="row g-2 mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-4">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            @forelse ($products as $product)
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
                <p class="text-muted">No products match your search.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $products->onEachSide(1)->links() }}
        </div>
    </div>
@endsection
