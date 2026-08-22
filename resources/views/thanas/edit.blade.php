@extends('layouts.admin')

@section('title', 'Edit Thana')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('thanas.index') }}">Thanas</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('thanas.update', $thana) }}">
                @include('thanas._form')
            </form>
        </div>
    </div>
@endsection
