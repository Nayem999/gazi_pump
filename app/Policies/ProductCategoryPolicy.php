<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;

class ProductCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('product-categories.view');
    }

    public function view(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product-categories.view');
    }

    public function create(User $user): bool
    {
        return $user->can('product-categories.add');
    }

    public function update(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product-categories.edit');
    }

    public function delete(User $user, ProductCategory $productCategory): bool
    {
        if ($productCategory->products()->exists()) {
            return false;
        }

        return $user->can('product-categories.delete');
    }

    public function restore(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product-categories.restore');
    }

    public function forceDelete(User $user, ProductCategory $productCategory): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('product-categories.export');
    }

    public function import(User $user): bool
    {
        return $user->can('product-categories.import');
    }

    public function print(User $user): bool
    {
        return $user->can('product-categories.print');
    }
}
