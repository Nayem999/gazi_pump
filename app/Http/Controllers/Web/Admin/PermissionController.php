<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\PermissionName;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = Permission::withCount('roles')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => PermissionName::moduleOf($permission->name));

        return view('permissions.index', ['groupedPermissions' => $permissions]);
    }
}
