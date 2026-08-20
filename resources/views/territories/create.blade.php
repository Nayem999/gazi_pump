@extends('layouts.admin')

@section('title', 'Add Territory')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('territories.index') }}">Territories</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('territories.store') }}">
                @include('territories._form')
            </form>
        </div>
    </div>
@endsection
