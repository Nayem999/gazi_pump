<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductCategoriesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        return new ProductCategory([
            'name' => $row['name'],
            'code' => $row['code'],
            'parent_id' => isset($row['parent_code']) && $row['parent_code'] !== ''
                ? ProductCategory::whereNull('parent_id')->where('code', $row['parent_code'])->value('id')
                : null,
            'description' => $row['description'] ?? null,
            'status' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'code' => ['required', 'string', 'unique:product_categories,code'],
            // Sub-categories can only nest under an existing top-level
            // category, so the parent must already exist in this table.
            'parent_code' => ['nullable', 'string', Rule::exists('product_categories', 'code')->whereNull('parent_id')],
        ];
    }
}
