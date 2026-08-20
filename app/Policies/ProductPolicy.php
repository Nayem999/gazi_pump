<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view');
    }

    public function create(User $user): bool
    {
        return $user->can('products.add');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.edit');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.delete');
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->can('products.restore');
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function export(User $user): bool
    {
        return $user->can('products.export');
    }

    public function import(User $user): bool
    {
        return $user->can('products.import');
    }

    public function print(User $user): bool
    {
        return $user->can('products.print');
    }
}
