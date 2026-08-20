@extends('layouts.admin')

@section('title', 'Edit Visit')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visits.index') }}">Customer Visits</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('visits.update', $visit) }}">
                @include('visits._form')
            </form>
        </div>
    </div>
@endsection
