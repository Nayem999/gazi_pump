@extends('layouts.admin')

@section('title', 'Plan Visit')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visit-plans.index') }}">Visit Plans</a></li>
    <li class="breadcrumb-item active">Plan</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('visit-plans.store') }}">
                @include('visit-plans._form')
            </form>
        </div>
    </div>
@endsection
