@extends('layouts.admin')

@section('title', 'Edit Visit Plan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visit-plans.index') }}">Visit Plans</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('visit-plans.update', $visitPlan) }}">
                @include('visit-plans._form')
            </form>
        </div>
    </div>
@endsection
