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
        return $user->can('products.view') && $this->isVisible($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->can('products.add');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.edit') && $this->isVisible($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.delete') && $this->isVisible($user, $product);
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->can('products.restore') && $this->isVisible($user, $product);
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

    /**
     * Whether this product falls within the viewer's own sales team — reuses
     * Product::scopeOwnedByTeam() so list and single-record checks can never
     * drift out of sync with each other. withTrashed() so restore/
     * forceDelete (checked against an already soft-deleted record) don't
     * always fail.
     */
    private function isVisible(User $user, Product $product): bool
    {
        return Product::withTrashed()->ownedByTeam($user)->whereKey($product->id)->exists();
    }
}
