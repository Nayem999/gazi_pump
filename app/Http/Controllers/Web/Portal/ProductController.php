<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::where('status', true)
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->with('category')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('portal.products.index', [
            'products' => $products,
            'categories' => ProductCategory::where('status', true)->orderBy('name')->get(),
            'filters' => $request->only(['category_id', 'search']),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status, 404);

        return view('portal.products.show', [
            'product' => $product->load('category'),
            'relatedProducts' => Product::where('status', true)
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->limit(4)
                ->get(),
        ]);
    }
}
