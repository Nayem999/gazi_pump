@extends('layouts.admin')

@section('title', 'Add Leave Entitlement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('leave-balances.index') }}">Leave Entitlements</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('leave-balances.store') }}">
                @include('leave-balances._form')
            </form>
        </div>
    </div>
@endsection
