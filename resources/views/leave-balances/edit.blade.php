@extends('layouts.admin')

@section('title', 'Edit Leave Entitlement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leave-balances.index') }}">Leave Entitlements</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('leave-balances.update', $leaveBalance) }}">
                @include('leave-balances._form')
            </form>
        </div>
    </div>
@endsection
