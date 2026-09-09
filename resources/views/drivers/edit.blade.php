@extends('layouts.admin')

@section('title', 'Edit Driver')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">Drivers</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('drivers.update', $driver) }}">
                @include('drivers._form')
            </form>
        </div>
    </div>
@endsection
