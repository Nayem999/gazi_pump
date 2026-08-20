@extends('layouts.admin')

@section('title', 'Edit Customer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('customers.update', $customer) }}">
                @include('customers._form')
            </form>
        </div>
    </div>
@endsection
