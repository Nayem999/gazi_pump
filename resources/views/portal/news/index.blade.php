@extends('layouts.portal')

@section('title', 'News')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Latest News</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @forelse ($articles as $article)
                <div class="col">
                    <div class="card h-100">
                        @if ($article->coverImageUrl())
                            <img src="{{ $article->coverImageUrl() }}" class="card-img-top" style="height:160px;object-fit:cover" alt="{{ $article->title }}">
                        @endif
                        <div class="card-body">
                            <h6 class="card-title">{{ $article->title }}</h6>
                            <p class="text-muted small">{{ $article->published_at?->format('d M Y') }}</p>
                            <p class="card-text small">{{ \Illuminate\Support\Str::limit($article->excerpt, 100) }}</p>
                            <a href="{{ route('portal.news.show', $article) }}" class="small">Read More &rarr;</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No news articles yet.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $articles->onEachSide(1)->links() }}</div>
    </div>
@endsection
