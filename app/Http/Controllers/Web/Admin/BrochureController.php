<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrochureRequest;
use App\Http\Requests\Admin\UpdateBrochureRequest;
use App\Models\Brochure;
use App\Services\BrochureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BrochureController extends Controller
{
    public function __construct(private readonly BrochureService $brochures) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Brochure::class);

        return view('brochures.index', [
            'brochures' => $this->brochures->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Brochure::class);

        return view('brochures.create');
    }

    public function store(StoreBrochureRequest $request): RedirectResponse
    {
        $this->brochures->create($request->validated(), $request->file('file'), $request->file('cover_image'));

        return redirect()->route('brochures.index')->with('success', 'Brochure created successfully.');
    }

    public function edit(Brochure $brochure): View
    {
        $this->authorize('update', $brochure);

        return view('brochures.edit', ['brochure' => $brochure]);
    }

    public function update(UpdateBrochureRequest $request, Brochure $brochure): RedirectResponse
    {
        $this->brochures->update($brochure, $request->validated(), $request->file('file'), $request->file('cover_image'));

        return redirect()->route('brochures.index')->with('success', 'Brochure updated successfully.');
    }

    public function destroy(Brochure $brochure): RedirectResponse
    {
        $this->authorize('delete', $brochure);

        $this->brochures->delete($brochure);

        return redirect()->route('brochures.index')->with('success', 'Brochure moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $brochure = Brochure::withTrashed()->findOrFail($id);
        $this->authorize('restore', $brochure);

        $this->brochures->restore($id);

        return redirect()->route('brochures.index')->with('success', 'Brochure restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $brochure = Brochure::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $brochure);

        $this->brochures->forceDelete($id);

        return redirect()->route('brochures.index')->with('success', 'Brochure permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('brochures.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:brochures,id']]);

        $count = $this->brochures->bulkDelete($request->input('ids'));

        return redirect()->route('brochures.index')->with('success', "{$count} brochure(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('brochures.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:brochures,id']]);

        $count = $this->brochures->bulkRestore($request->input('ids'));

        return redirect()->route('brochures.index')->with('success', "{$count} brochure(s) restored.");
    }

    public function toggleStatus(Brochure $brochure): RedirectResponse
    {
        $this->authorize('update', $brochure);

        $this->brochures->update($brochure, ['is_published' => ! $brochure->is_published]);

        return back()->with('success', 'Brochure status updated.');
    }
}
