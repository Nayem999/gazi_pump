<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('product_category')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('product_categories', 'code')->ignore($this->route('product_category')->id)],
            // Only a top-level category can be picked as a parent, so
            // sub-categories are capped at one level deep.
            'parent_id' => ['nullable', Rule::exists('product_categories', 'id')->whereNull('parent_id')],
            'description' => ['nullable', 'string'],
            'status' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $parentId = $this->input('parent_id');
            $category = $this->route('product_category');

            if (! $parentId) {
                return;
            }

            if ((int) $parentId === $category->id) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');

                return;
            }

            if ($category->children()->exists()) {
                $validator->errors()->add('parent_id', 'A category that already has sub-categories cannot itself become a sub-category.');
            }
        });
    }
}
