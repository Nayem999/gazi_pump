<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use App\Models\CollectionEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreCollectionEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CollectionEntry::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'collection_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
            // Doubles as the cheque number (existing use) and the bank/MFS
            // transaction ID (new) — required for the latter two methods,
            // and checked for duplicates among other bank/MFS entries so
            // the same transaction can't be recorded twice.
            'reference_no' => [
                Rule::requiredIf(fn () => in_array($this->input('payment_method'), [
                    PaymentMethod::BankTransfer->value, PaymentMethod::MobileBanking->value,
                ], true)),
                'nullable', 'string', 'max:100',
                Rule::unique('collection_entries', 'reference_no')->where(
                    fn ($query) => $query->whereIn('payment_method', [PaymentMethod::BankTransfer->value, PaymentMethod::MobileBanking->value])
                ),
            ],
            'cheque_image' => [
                Rule::requiredIf(fn () => $this->input('payment_method') === PaymentMethod::Cheque->value),
                'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096',
            ],
            // A collection can only ever be recorded through the "Send OTP
            // -> dealer confirms -> submit" flow — no collection is taken
            // without it. Excel import is the one exception: it builds
            // CollectionEntry rows directly (App\Imports\CollectionEntriesImport)
            // and never goes through this request at all.
            'otp_id' => ['required', 'integer'],
            'otp_code' => ['required', 'string', 'size:6'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Rule::requiredIf() resolves to the plain "required" rule
            // string, not "required_if" — this key must match that.
            'cheque_image.required' => 'A cheque image is required when the payment method is Cheque.',
            'reference_no.required' => 'A transaction ID is required for Bank Transfer and Mobile Banking payments.',
            'reference_no.unique' => 'This transaction ID has already been recorded against another collection.',
            'otp_id.required' => 'Send an OTP to the dealer before recording this collection.',
            'otp_code.required' => 'Enter the OTP the dealer read back to you.',
        ];
    }
}
