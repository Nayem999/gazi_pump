@extends('layouts.admin')

@section('title', 'Edit Collection')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('collection-entries.index') }}">Collection Entry</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('collection-entries.update', $collectionEntry) }}">
                @include('collection-entries._form')
            </form>
        </div>
    </div>
@endsection
