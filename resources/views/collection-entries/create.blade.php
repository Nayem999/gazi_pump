@extends('layouts.admin')

@section('title', 'Record Collection')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('collection-entries.index') }}">Collection Entry</a></li>
    <li class="breadcrumb-item active">Record</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('collection-entries.store') }}" enctype="multipart/form-data">
                @include('collection-entries._form')
            </form>
        </div>
    </div>
@endsection
