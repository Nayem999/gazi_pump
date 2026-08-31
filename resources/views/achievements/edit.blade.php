@extends('layouts.admin')

@section('title', 'Edit Achievement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('achievements.index') }}">Achievement</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('achievements.update', $entry) }}">
                @include('achievements._form')
            </form>
        </div>
    </div>
@endsection
