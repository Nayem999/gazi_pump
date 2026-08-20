@extends('layouts.portal')

@section('title', $article->title)

@section('content')
    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('portal.news.index') }}">News</a></li>
                <li class="breadcrumb-item active">{{ $article->title }}</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1>{{ $article->title }}</h1>
                <p class="text-muted">{{ $article->published_at?->format('F d, Y') }}</p>
                @if ($article->coverImageUrl())
                    <img src="{{ $article->coverImageUrl() }}" class="img-fluid rounded mb-4" alt="{{ $article->title }}">
                @endif
                <div class="lh-lg">{!! nl2br(e($article->body)) !!}</div>
            </div>
        </div>
    </div>
@endsection
