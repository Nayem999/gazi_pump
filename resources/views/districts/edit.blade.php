@extends('layouts.admin')

@section('title', 'Edit District')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('districts.index') }}">Districts</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('districts.update', $district) }}">
                @include('districts._form')
            </form>
        </div>
    </div>
@endsection
