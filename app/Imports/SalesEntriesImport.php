<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesEntry;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Bulk backfill of sales recorded on paper. Rows sharing the same
 * employee_id + customer_code + sale_date are grouped into a single sale
 * with one line per row — that's the only way a flat spreadsheet can
 * express a multi-product sale. A line's discount is capped at the
 * configured max-discount percentage rather than the whole row being
 * rejected, matching the other bulk imports in this app.
 */
class SalesEntriesImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * @param  Collection<int, mixed>  $rows
     */
    public function collection(Collection $rows): void
    {
        /** @var Authenticatable|null $currentUser */
        $currentUser = Auth::user();

        $groups = $rows->groupBy(fn ($row) => $row['employee_id'].'|'.$row['customer_code'].'|'.$row['sale_date']);

        foreach ($groups as $groupRows) {
            $first = $groupRows->first();
            $userId = User::where('employee_id', $first['employee_id'])->value('id');
            $customerId = Customer::where('customer_code', $first['customer_code'])->value('id');

            if (! $userId || ! $customerId) {
                continue;
            }

            $lines = $this->buildLines($groupRows);

            if ($lines === []) {
                continue;
            }

            $salesEntry = SalesEntry::create([
                'user_id' => $userId,
                'customer_id' => $customerId,
                'sale_date' => $first['sale_date'],
                'total_amount' => array_sum(array_column($lines, 'total_amount')),
                'remarks' => $first['remarks'] ?? null,
                'created_by' => $currentUser?->getAuthIdentifier(),
            ]);

            $salesEntry->items()->createMany($lines);
        }
    }

    /**
     * @param  Collection<int, mixed>  $groupRows
     * @return array<int, array<string, mixed>>
     */
    private function buildLines(Collection $groupRows): array
    {
        $maxDiscountPercent = (float) config('sfa.sales.max_discount_percent');
        $lines = [];

        foreach ($groupRows as $row) {
            $productId = Product::where('sku', $row['product_sku'])->value('id');

            if (! $productId) {
                continue;
            }

            $quantity = (int) $row['quantity'];
            $unitPrice = (float) $row['unit_price'];
            $subtotal = $quantity * $unitPrice;
            $maxDiscountAmount = round($subtotal * $maxDiscountPercent / 100, 2);
            $discountAmount = min((float) ($row['discount_amount'] ?? 0), $maxDiscountAmount);

            $lines[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'total_amount' => round($subtotal - $discountAmount, 2),
            ];
        }

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'exists:users,employee_id'],
            'customer_code' => ['required', 'string', 'exists:customers,customer_code'],
            'product_sku' => ['required', 'string', 'exists:products,sku'],
            'sale_date' => ['required', 'date'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
