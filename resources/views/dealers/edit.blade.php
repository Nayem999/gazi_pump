@extends('layouts.admin')

@section('title', 'Edit Dealer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dealers.index') }}">Dealers</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('dealers.update', $dealer) }}" enctype="multipart/form-data">
                @include('dealers._form')
            </form>
        </div>
    </div>
@endsection
