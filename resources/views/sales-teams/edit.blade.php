@extends('layouts.admin')

@section('title', 'Edit Sales Team')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales-teams.index') }}">Sales Teams</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('sales-teams.update', $salesTeam) }}">
                @include('sales-teams._form')
            </form>
        </div>
    </div>
@endsection
