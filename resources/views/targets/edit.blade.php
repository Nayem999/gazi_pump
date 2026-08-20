@extends('layouts.admin')

@section('title', 'Edit Target')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('targets.index') }}">Targets</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('targets.update', $target) }}">
                @include('targets._form')
            </form>
        </div>
    </div>
@endsection
