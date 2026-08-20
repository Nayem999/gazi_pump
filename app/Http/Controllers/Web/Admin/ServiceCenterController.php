<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceCenterRequest;
use App\Http\Requests\Admin\UpdateServiceCenterRequest;
use App\Models\ServiceCenter;
use App\Services\ServiceCenterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceCenterController extends Controller
{
    public function __construct(private readonly ServiceCenterService $serviceCenters) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceCenter::class);

        return view('service-centers.index', [
            'serviceCenters' => $this->serviceCenters->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ServiceCenter::class);

        return view('service-centers.create');
    }

    public function store(StoreServiceCenterRequest $request): RedirectResponse
    {
        $this->serviceCenters->create($request->validated());

        return redirect()->route('service-centers.index')->with('success', 'Service center created successfully.');
    }

    public function edit(ServiceCenter $serviceCenter): View
    {
        $this->authorize('update', $serviceCenter);

        return view('service-centers.edit', ['serviceCenter' => $serviceCenter]);
    }

    public function update(UpdateServiceCenterRequest $request, ServiceCenter $serviceCenter): RedirectResponse
    {
        $this->serviceCenters->update($serviceCenter, $request->validated());

        return redirect()->route('service-centers.index')->with('success', 'Service center updated successfully.');
    }

    public function destroy(ServiceCenter $serviceCenter): RedirectResponse
    {
        $this->authorize('delete', $serviceCenter);

        $this->serviceCenters->delete($serviceCenter);

        return redirect()->route('service-centers.index')->with('success', 'Service center moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $serviceCenter = ServiceCenter::withTrashed()->findOrFail($id);
        $this->authorize('restore', $serviceCenter);

        $this->serviceCenters->restore($id);

        return redirect()->route('service-centers.index')->with('success', 'Service center restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $serviceCenter = ServiceCenter::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $serviceCenter);

        $this->serviceCenters->forceDelete($id);

        return redirect()->route('service-centers.index')->with('success', 'Service center permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('service-centers.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:service_centers,id']]);

        $count = $this->serviceCenters->bulkDelete($request->input('ids'));

        return redirect()->route('service-centers.index')->with('success', "{$count} service center(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('service-centers.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:service_centers,id']]);

        $count = $this->serviceCenters->bulkRestore($request->input('ids'));

        return redirect()->route('service-centers.index')->with('success', "{$count} service center(s) restored.");
    }

    public function toggleStatus(ServiceCenter $serviceCenter): RedirectResponse
    {
        $this->authorize('update', $serviceCenter);

        $this->serviceCenters->update($serviceCenter, ['is_active' => ! $serviceCenter->is_active]);

        return back()->with('success', 'Service center status updated.');
    }
}
