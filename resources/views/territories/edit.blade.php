@extends('layouts.admin')

@section('title', 'Edit Territory')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('territories.index') }}">Territories</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('territories.update', $territory) }}">
                @include('territories._form')
            </form>
        </div>
    </div>
@endsection
