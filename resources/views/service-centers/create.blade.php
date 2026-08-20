@extends('layouts.admin')

@section('title', 'Add Service Center')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-centers.index') }}">Service Centers</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('service-centers.store') }}">
                @include('service-centers._form')
            </form>
        </div>
    </div>
@endsection
