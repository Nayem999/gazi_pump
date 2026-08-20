@extends('layouts.admin')

@section('title', 'Add Brochure')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('brochures.index') }}">Brochures</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('brochures.store') }}" enctype="multipart/form-data">
                @include('brochures._form')
            </form>
        </div>
    </div>
@endsection
