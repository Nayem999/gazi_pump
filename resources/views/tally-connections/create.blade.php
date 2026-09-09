@extends('layouts.admin')

@section('title', 'Add Tally Connection')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tally-integration.dashboard') }}">Tally Integration</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tally-connections.index') }}">Connections</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('tally-connections.store') }}">
                @include('tally-connections._form')
            </form>
        </div>
    </div>
@endsection
