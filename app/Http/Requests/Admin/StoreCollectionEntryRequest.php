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
            'reference_no' => ['nullable', 'string', 'max:100'],
            'cheque_image' => [
                Rule::requiredIf(fn () => $this->input('payment_method') === PaymentMethod::Cheque->value),
                'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096',
            ],
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
        ];
    }
}
