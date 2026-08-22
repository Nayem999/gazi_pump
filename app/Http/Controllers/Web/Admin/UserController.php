<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\SalesTeam;
use App\Models\Setting;
use App\Models\User;
use App\Services\UserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = $this->users->paginate($request->only(['search', 'role', 'status', 'trashed']), 15);

        return view('users.index', [
            'users' => $users,
            'roles' => Role::pluck('name'),
            'filters' => $request->only(['search', 'role', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => Role::all(),
            'managers' => User::where('status', true)->orderBy('name')->get(),
            'salesTeams' => SalesTeam::orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->users->create($request->validated(), $request->file('photo'));

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['manager', 'roles', 'subordinates', 'salesTeam', 'territories']);

        return view('users.show', ['user' => $user]);
    }

    public function downloadPdf(User $user): mixed
    {
        $this->authorize('view', $user);

        $user->load(['manager', 'roles', 'salesTeam', 'territories']);

        return Pdf::loadView('users.detail-pdf', ['user' => $user, 'setting' => Setting::current()])
            ->stream('user-'.$user->id.'-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user->load(['roles', 'territories']),
            'roles' => Role::all(),
            'managers' => User::where('id', '!=', $user->id)->where('status', true)->orderBy('name')->get(),
            'salesTeams' => SalesTeam::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->users->update($user, $request->validated(), $request->file('photo'));

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->users->delete($user);

        return redirect()->route('users.index')->with('success', 'User moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->authorize('restore', $user);

        $this->users->restore($id);

        return redirect()->route('users.index')->with('success', 'User restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $user);

        $this->users->forceDelete($id);

        return redirect()->route('users.index')->with('success', 'User permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('users.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:users,id']]);

        $ids = array_diff($request->input('ids'), [$request->user()->id]);

        $count = $this->users->bulkDelete($ids);

        return redirect()->route('users.index')->with('success', "{$count} user(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('users.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:users,id']]);

        $count = $this->users->bulkRestore($request->input('ids'));

        return redirect()->route('users.index')->with('success', "{$count} user(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', User::class);

        $users = $this->users->paginate($request->only(['search', 'role', 'status', 'trashed']), PHP_INT_MAX)
            ->getCollection();

        return Excel::download(new UsersExport($users), 'users-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', User::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new UsersImport, $request->file('file'));

        return Redirect::route('users.index')->with('success', 'Users imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', User::class);

        $users = $this->users->paginate($request->only(['search', 'role', 'status', 'trashed']), PHP_INT_MAX)
            ->getCollection();

        $pdf = Pdf::loadView('users.print', ['users' => $users]);

        return $pdf->stream('users-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $this->users->update($user, ['status' => ! $user->status]);

        return back()->with('success', $user->status ? 'User activated successfully.' : 'User deactivated successfully.');
    }
}
