@extends('layouts.portal')

@section('title', $product->name)

@section('content')
    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('portal.products.index') }}">Products</a></li>
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-md-5">
                @if ($product->imageUrl())
                    <img src="{{ $product->imageUrl() }}" class="img-fluid rounded" alt="{{ $product->name }}">
                @else
                    <div class="bg-body-secondary d-flex align-items-center justify-content-center rounded" style="height:300px">
                        <i class="ti ti-package fs-1 text-muted"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-7">
                <h1>{{ $product->name }}</h1>
                <p class="text-muted">{{ $product->category?->name }}</p>
                <p>{{ $product->description }}</p>
                <a href="{{ route('portal.contact', ['product_id' => $product->id]) }}" class="btn btn-primary">
                    <i class="ti ti-message-circle me-1"></i>Inquire About This Product
                </a>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <div class="mt-5">
                <h4 class="mb-3">Related Products</h4>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
                    @foreach ($relatedProducts as $related)
                        <div class="col">
                            <div class="card h-100">
                                @if ($related->imageUrl())
                                    <img src="{{ $related->imageUrl() }}" class="card-img-top" style="height:140px;object-fit:cover" alt="{{ $related->name }}">
                                @else
                                    <div class="bg-body-secondary d-flex align-items-center justify-content-center" style="height:140px">
                                        <i class="ti ti-package fs-3 text-muted"></i>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h6 class="card-title small">{{ $related->name }}</h6>
                                    <a href="{{ route('portal.products.show', $related) }}" class="small">View Details &rarr;</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
