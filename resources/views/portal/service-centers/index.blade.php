@extends('layouts.portal')

@section('title', 'Service Centers')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Service Centers</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @forelse ($serviceCenters as $center)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title"><i class="ti ti-map-pin me-1"></i>{{ $center->name }}</h6>
                            <p class="small text-muted mb-1">{{ $center->address }}</p>
                            @if ($center->phone)
                                <p class="small mb-0"><i class="ti ti-phone me-1"></i>{{ $center->phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No service centers listed yet.</p>
            @endforelse
        </div>
    </div>
@endsection
