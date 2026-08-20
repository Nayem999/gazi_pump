@extends('layouts.admin')

@section('title', 'Record Visit')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visits.index') }}">Customer Visits</a></li>
    <li class="breadcrumb-item active">Record</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('visits.store') }}">
                @include('visits._form')
            </form>
        </div>
    </div>
@endsection
