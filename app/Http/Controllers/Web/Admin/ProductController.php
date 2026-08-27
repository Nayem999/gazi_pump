<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesTeam;
use App\Services\ProductService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        return view('products.index', [
            'products' => $this->products->paginate($request->only(['search', 'category_id', 'sales_team_id', 'status', 'trashed']), 15, $request->user()),
            'categories' => ProductCategory::orderBy('name')->get(),
            'salesTeams' => SalesTeam::where('status', true)->orderBy('name')->get(),
            'filters' => $request->only(['search', 'category_id', 'sales_team_id', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('products.create', [
            'categories' => ProductCategory::orderedForSelect(),
            'salesTeams' => SalesTeam::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->products->create($request->validated(), $request->file('image'));

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.edit', [
            'product' => $product,
            'categories' => ProductCategory::orderedForSelect(),
            'salesTeams' => SalesTeam::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->validated(), $request->file('image'));

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return redirect()->route('products.index')->with('success', 'Product moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $this->authorize('restore', $product);

        $this->products->restore($id);

        return redirect()->route('products.index')->with('success', 'Product restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $product);

        $this->products->forceDelete($id);

        return redirect()->route('products.index')->with('success', 'Product permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('products.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:products,id']]);

        $count = $this->products->bulkDelete($request->input('ids'));

        return redirect()->route('products.index')->with('success', "{$count} product(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('products.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:products,id']]);

        $count = $this->products->bulkRestore($request->input('ids'));

        return redirect()->route('products.index')->with('success', "{$count} product(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Product::class);

        $products = $this->products->paginate($request->only(['search', 'category_id', 'sales_team_id', 'status', 'trashed']), PHP_INT_MAX, $request->user())->getCollection();

        return Excel::download(new ProductsExport($products), 'products-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Product::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new ProductsImport, $request->file('file'));

        return redirect()->route('products.index')->with('success', 'Products imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Product::class);

        $products = $this->products->paginate($request->only(['search', 'category_id', 'sales_team_id', 'status', 'trashed']), PHP_INT_MAX, $request->user())->getCollection();

        return Pdf::loadView('products.print', ['products' => $products])
            ->stream('products-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->products->update($product, ['status' => ! $product->status]);

        return back()->with('success', 'Product status updated.');
    }
}
