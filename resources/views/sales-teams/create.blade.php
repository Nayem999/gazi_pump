@extends('layouts.admin')

@section('title', 'Add Sales Team')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales-teams.index') }}">Sales Teams</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('sales-teams.store') }}">
                @include('sales-teams._form')
            </form>
        </div>
    </div>
@endsection
