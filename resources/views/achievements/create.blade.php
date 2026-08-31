@extends('layouts.admin')

@section('title', 'Record Achievement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achievements.index') }}">Achievement</a></li>
    <li class="breadcrumb-item active">Record</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('achievements.store') }}">
                @include('achievements._form')
            </form>
        </div>
    </div>
@endsection
