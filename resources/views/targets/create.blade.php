@extends('layouts.admin')

@section('title', 'Assign Target')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('targets.index') }}">Targets</a></li>
    <li class="breadcrumb-item active">Assign</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('targets.store') }}">
                @include('targets._form')
            </form>
        </div>
    </div>
@endsection
