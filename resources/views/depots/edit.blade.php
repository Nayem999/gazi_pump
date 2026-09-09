@extends('layouts.admin')

@section('title', 'Edit Depot')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('depots.index') }}">Depots</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('depots.update', $depot) }}">
                @include('depots._form')
            </form>
        </div>
    </div>
@endsection
