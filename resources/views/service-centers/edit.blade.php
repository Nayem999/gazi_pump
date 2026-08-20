@extends('layouts.admin')

@section('title', 'Edit Service Center')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-centers.index') }}">Service Centers</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('service-centers.update', $serviceCenter) }}">
                @include('service-centers._form')
            </form>
        </div>
    </div>
@endsection
