@extends('layouts.admin')

@section('title', 'Record Order')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active">Record</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('orders.store') }}">
                @include('orders._form')
            </form>
        </div>
    </div>
@endsection
