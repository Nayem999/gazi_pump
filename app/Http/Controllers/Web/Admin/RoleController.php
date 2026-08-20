<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\PermissionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        return view('roles.index', [
            'roles' => $this->roles->paginate($request->only('search'), 15),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.create', ['permissions' => Permission::all()->groupBy(fn (Permission $p) => PermissionName::moduleOf($p->name))]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated());

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::all()->groupBy(fn (Permission $p) => PermissionName::moduleOf($p->name)),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->update($role, $request->validated());

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $this->roles->delete($role);

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
