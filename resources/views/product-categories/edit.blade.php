@extends('layouts.admin')

@section('title', 'Edit Product Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-categories.index') }}">Product Categories</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('product-categories.update', $category) }}">
                @include('product-categories._form')
            </form>
        </div>
    </div>
@endsection
