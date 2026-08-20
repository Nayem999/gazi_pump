<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\ProductCategoriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductCategoryRequest;
use App\Http\Requests\Admin\UpdateProductCategoryRequest;
use App\Imports\ProductCategoriesImport;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductCategoryController extends Controller
{
    public function __construct(private readonly ProductCategoryService $categories) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProductCategory::class);

        return view('product-categories.index', [
            'categories' => $this->categories->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ProductCategory::class);

        return view('product-categories.create');
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        return redirect()->route('product-categories.index')->with('success', 'Product category created successfully.');
    }

    public function edit(ProductCategory $productCategory): View
    {
        $this->authorize('update', $productCategory);

        return view('product-categories.edit', ['category' => $productCategory]);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory): RedirectResponse
    {
        $this->categories->update($productCategory, $request->validated());

        return redirect()->route('product-categories.index')->with('success', 'Product category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        $this->authorize('delete', $productCategory);

        $this->categories->delete($productCategory);

        return redirect()->route('product-categories.index')->with('success', 'Product category moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $category = ProductCategory::withTrashed()->findOrFail($id);
        $this->authorize('restore', $category);

        $this->categories->restore($id);

        return redirect()->route('product-categories.index')->with('success', 'Product category restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $category = ProductCategory::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $category);

        $this->categories->forceDelete($id);

        return redirect()->route('product-categories.index')->with('success', 'Product category permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('product-categories.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:product_categories,id']]);

        $count = $this->categories->bulkDelete($request->input('ids'));

        return redirect()->route('product-categories.index')->with('success', "{$count} categor(y/ies) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('product-categories.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:product_categories,id']]);

        $count = $this->categories->bulkRestore($request->input('ids'));

        return redirect()->route('product-categories.index')->with('success', "{$count} categor(y/ies) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', ProductCategory::class);

        $categories = $this->categories->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new ProductCategoriesExport($categories), 'product-categories-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', ProductCategory::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new ProductCategoriesImport, $request->file('file'));

        return redirect()->route('product-categories.index')->with('success', 'Product categories imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', ProductCategory::class);

        $categories = $this->categories->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('product-categories.print', ['categories' => $categories])
            ->stream('product-categories-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(ProductCategory $productCategory): RedirectResponse
    {
        $this->authorize('update', $productCategory);

        $this->categories->update($productCategory, ['status' => ! $productCategory->status]);

        return back()->with('success', 'Product category status updated.');
    }
}
