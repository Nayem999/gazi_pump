@extends('layouts.admin')

@section('title', 'Edit Vehicle')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
                @include('vehicles._form')
            </form>
        </div>
    </div>
@endsection
