@extends('layouts.admin')

@section('title', 'Edit Leave Request')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leave-requests.index') }}">Leave Requests</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('leave-requests.update', $leaveRequest) }}">
                @include('leave-requests._form')
            </form>
        </div>
    </div>
@endsection
