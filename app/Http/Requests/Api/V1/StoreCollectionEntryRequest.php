<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Self-service field collection entry: a Sales Executive records a payment
 * received from a customer. There's no `user_id` input — the executive is
 * always the authenticated user.
 */
class StoreCollectionEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('api.collection-entries.add') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'collection_date' => ['nullable', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
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
            // Both optional: present only when this submission is going
            // through the "Send OTP -> dealer confirms -> submit" flow.
            'otp_id' => ['nullable', 'integer'],
            'otp_code' => ['nullable', 'string', 'size:6'],
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
        ];
    }
}
