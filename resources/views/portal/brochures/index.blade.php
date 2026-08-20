@extends('layouts.portal')

@section('title', 'Brochures')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Brochures</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @forelse ($brochures as $brochure)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><i class="ti ti-file-type-pdf me-1"></i>{{ $brochure->title }}</h6>
                            <a href="{{ route('portal.brochures.download', $brochure) }}" class="btn btn-sm btn-outline-primary mt-auto">
                                <i class="ti ti-download me-1"></i>Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No brochures available yet.</p>
            @endforelse
        </div>
    </div>
@endsection
