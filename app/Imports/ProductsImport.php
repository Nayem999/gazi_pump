<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        return new Product([
            'category_id' => ProductCategory::where('code', $row['category_code'])->value('id'),
            'name' => $row['name'],
            'sku' => $row['sku'],
            'price' => $row['price'],
            'status' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_code' => ['required', 'string', 'exists:product_categories,code'],
            'name' => ['required', 'string'],
            'sku' => ['required', 'string', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
