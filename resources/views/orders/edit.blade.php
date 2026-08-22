@extends('layouts.admin')

@section('title', 'Edit Order')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('orders.update', $order) }}">
                @include('orders._form')
            </form>
        </div>
    </div>
@endsection
