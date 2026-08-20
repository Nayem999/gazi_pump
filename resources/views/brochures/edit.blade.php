@extends('layouts.admin')

@section('title', 'Edit Brochure')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('brochures.index') }}">Brochures</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('brochures.update', $brochure) }}" enctype="multipart/form-data">
                @include('brochures._form')
            </form>
        </div>
    </div>
@endsection
