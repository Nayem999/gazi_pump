<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Dealer;
use App\Models\Territory;
use App\Models\User;
use App\Services\AnnouncementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementService $announcements) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Announcement::class);

        return view('announcements.index', [
            'announcements' => $this->announcements->paginate($request->only(['search', 'audience', 'trashed']), 15),
            'filters' => $request->only(['search', 'audience', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('announcements.create', [
            'roles' => Role::orderBy('name')->pluck('name'),
            'territories' => Territory::orderBy('name')->get(),
            'users' => User::where('status', true)->orderBy('name')->get(),
            'dealers' => Dealer::orderBy('name')->get(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $announcement = $this->announcements->send($request->user(), $request->validated());

        return redirect()->route('announcements.index')
            ->with('success', "Announcement sent to {$announcement->recipient_count} recipient(s).");
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $this->announcements->delete($announcement);

        return redirect()->route('announcements.index')->with('success', 'Announcement moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $announcement = Announcement::withTrashed()->findOrFail($id);
        $this->authorize('restore', $announcement);

        $this->announcements->restore($id);

        return redirect()->route('announcements.index')->with('success', 'Announcement restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $announcement = Announcement::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $announcement);

        $this->announcements->forceDelete($id);

        return redirect()->route('announcements.index')->with('success', 'Announcement permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('announcements.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:announcements,id']]);

        $count = $this->announcements->bulkDelete($request->input('ids'));

        return redirect()->route('announcements.index')->with('success', "{$count} announcement(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('announcements.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:announcements,id']]);

        $count = $this->announcements->bulkRestore($request->input('ids'));

        return redirect()->route('announcements.index')->with('success', "{$count} announcement(s) restored.");
    }
}
