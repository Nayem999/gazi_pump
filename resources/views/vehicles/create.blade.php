@extends('layouts.admin')

@section('title', 'Add Vehicle')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('vehicles.store') }}">
                @include('vehicles._form')
            </form>
        </div>
    </div>
@endsection
