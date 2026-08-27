<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProductCategory::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:product_categories,code'],
            // Only a top-level category can be picked as a parent, so
            // sub-categories are capped at one level deep.
            'parent_id' => ['nullable', Rule::exists('product_categories', 'id')->whereNull('parent_id')],
            'description' => ['nullable', 'string'],
            'status' => ['boolean'],
        ];
    }
}
