@extends('layouts.admin')

@section('title', 'Edit FAQ')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('faqs.index') }}">FAQs</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('faqs.update', $faq) }}">
                @include('faqs._form')
            </form>
        </div>
    </div>
@endsection
