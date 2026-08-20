@extends('layouts.admin')

@section('title', 'Add Customer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('customers.store') }}">
                @include('customers._form')
            </form>
        </div>
    </div>
@endsection
