<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTallyConnectionRequest;
use App\Http\Requests\Admin\UpdateTallyConnectionRequest;
use App\Models\TallyConnection;
use App\Services\TallyConnectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Deliberately its own route block rather than $registerManagementRoutes:
 * connection configs have no export/import/print use case (nothing to
 * report on, and importing a spreadsheet of Tally credentials would be a
 * real security hole), plus two bespoke actions ($registerManagementRoutes
 * has no room for extras) — testing a connection and regenerating its Sync
 * Agent credential. Same reasoning shape as Cash Handovers' own block.
 */
class TallyConnectionController extends Controller
{
    public function __construct(private readonly TallyConnectionService $connections) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TallyConnection::class);

        return view('tally-connections.index', [
            'connections' => $this->connections->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TallyConnection::class);

        return view('tally-connections.create');
    }

    public function store(StoreTallyConnectionRequest $request): RedirectResponse
    {
        $result = $this->connections->createWithAgentCredentials($request->validated());

        return redirect()->route('tally-connections.index')
            ->with('success', 'Connection created successfully.')
            ->with('plain_agent_token', $result['plain_token']);
    }

    public function edit(TallyConnection $tallyConnection): View
    {
        $this->authorize('update', $tallyConnection);

        return view('tally-connections.edit', ['connection' => $tallyConnection]);
    }

    public function update(UpdateTallyConnectionRequest $request, TallyConnection $tallyConnection): RedirectResponse
    {
        $this->connections->update($tallyConnection, $request->validated());

        return redirect()->route('tally-connections.index')->with('success', 'Connection updated successfully.');
    }

    public function destroy(TallyConnection $tallyConnection): RedirectResponse
    {
        $this->authorize('delete', $tallyConnection);

        $this->connections->delete($tallyConnection);

        return redirect()->route('tally-connections.index')->with('success', 'Connection moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $tallyConnection = TallyConnection::withTrashed()->findOrFail($id);
        $this->authorize('restore', $tallyConnection);

        $this->connections->restore($id);

        return redirect()->route('tally-connections.index')->with('success', 'Connection restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $tallyConnection = TallyConnection::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $tallyConnection);

        $this->connections->forceDelete($id);

        return redirect()->route('tally-connections.index')->with('success', 'Connection permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('tally-integration.configure'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:tally_connections,id']]);

        $count = $this->connections->bulkDelete($request->input('ids'));

        return redirect()->route('tally-connections.index')->with('success', "{$count} connection(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('tally-integration.configure'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:tally_connections,id']]);

        $count = $this->connections->bulkRestore($request->input('ids'));

        return redirect()->route('tally-connections.index')->with('success', "{$count} connection(s) restored.");
    }

    public function toggleStatus(TallyConnection $tallyConnection): RedirectResponse
    {
        $this->authorize('update', $tallyConnection);

        $this->connections->update($tallyConnection, ['is_active' => ! $tallyConnection->is_active]);

        return back()->with('success', 'Connection status updated.');
    }

    public function testConnection(TallyConnection $tallyConnection): RedirectResponse
    {
        $this->authorize('testConnection', $tallyConnection);

        $result = $this->connections->testConnection($tallyConnection);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function regenerateToken(TallyConnection $tallyConnection): RedirectResponse
    {
        $this->authorize('update', $tallyConnection);

        $plainToken = $this->connections->regenerateAgentToken($tallyConnection);

        return back()
            ->with('success', 'Sync Agent credential regenerated. Copy it now — it will not be shown again.')
            ->with('plain_agent_token', $plainToken);
    }
}
