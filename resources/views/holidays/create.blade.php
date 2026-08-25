@extends('layouts.admin')

@section('title', 'Add Holiday')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holidays</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('holidays.store') }}">
                @include('holidays._form')
            </form>
        </div>
    </div>
@endsection
