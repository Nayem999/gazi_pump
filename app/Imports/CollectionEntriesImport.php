<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\PaymentMethod;
use App\Models\CollectionEntry;
use App\Models\Customer;
use App\Models\User;
use App\Services\CollectionEntryService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Bulk backfill of collections recorded on paper. A row's amount is capped
 * at the customer's current outstanding balance (plus tolerance) rather than
 * the whole row being rejected — a spreadsheet import has no per-row
 * feedback channel back to the person who is importing it.
 */
class CollectionEntriesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function __construct(private readonly CollectionEntryService $collectionEntries) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): ?Model
    {
        /** @var Authenticatable|null $currentUser */
        $currentUser = Auth::user();

        $userId = User::where('employee_id', $row['employee_id'])->value('id');
        $customerId = Customer::where('customer_code', $row['customer_code'])->value('id');

        if (! $userId || ! $customerId) {
            return null;
        }

        $balance = $this->collectionEntries->outstandingBalance($customerId);
        $tolerancePercent = (float) config('sfa.collections.overpayment_tolerance_percent');
        $maxAllowed = round(max($balance, 0) * (1 + $tolerancePercent / 100), 2);
        $amount = min((float) $row['amount'], $maxAllowed);

        if ($amount <= 0) {
            return null;
        }

        return new CollectionEntry([
            'user_id' => $userId,
            'customer_id' => $customerId,
            'collection_date' => $row['collection_date'],
            'amount' => $amount,
            'payment_method' => $row['payment_method'],
            'reference_no' => $row['reference_no'] ?? null,
            'remarks' => $row['remarks'] ?? null,
            'created_by' => $currentUser?->getAuthIdentifier(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string', 'exists:users,employee_id'],
            'customer_code' => ['required', 'string', 'exists:customers,customer_code'],
            'collection_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ];
    }
}
