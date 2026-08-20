@extends('layouts.admin')

@section('title', 'Record Sale')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales-entries.index') }}">Sales Entry</a></li>
    <li class="breadcrumb-item active">Record</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('sales-entries.store') }}">
                @include('sales-entries._form')
            </form>
        </div>
    </div>
@endsection
