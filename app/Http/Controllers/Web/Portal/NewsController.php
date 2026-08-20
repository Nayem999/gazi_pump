<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('portal.news.index', [
            'articles' => News::where('is_published', true)->latest('published_at')->paginate(9),
        ]);
    }

    public function show(News $article): View
    {
        abort_unless($article->is_published, 404);

        return view('portal.news.show', ['article' => $article]);
    }
}
