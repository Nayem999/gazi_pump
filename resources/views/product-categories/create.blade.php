@extends('layouts.admin')

@section('title', 'Add Product Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-categories.index') }}">Product Categories</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('product-categories.store') }}">
                @include('product-categories._form')
            </form>
        </div>
    </div>
@endsection
