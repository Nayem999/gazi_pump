@extends('layouts.admin')

@section('title', 'Edit Leave Type')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Types</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('leave-types.update', $leaveType) }}">
                @include('leave-types._form')
            </form>
        </div>
    </div>
@endsection
