@extends('layouts.admin')

@section('title', 'Add Leave Type')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Types</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('leave-types.store') }}">
                @include('leave-types._form')
            </form>
        </div>
    </div>
@endsection
