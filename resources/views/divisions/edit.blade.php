@extends('layouts.admin')

@section('title', 'Edit Division')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('divisions.index') }}">Divisions</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('divisions.update', $division) }}">
                @include('divisions._form')
            </form>
        </div>
    </div>
@endsection
