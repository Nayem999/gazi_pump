<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCollectionEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('collection_entry')) ?? false;
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
            // Only forces a (re-)upload if the entry would otherwise end up
            // with no cheque image at all — editing an existing cheque
            // collection without touching this field keeps its current
            // image rather than demanding it be re-selected every time.
            'cheque_image' => [
                Rule::requiredIf(fn () => $this->input('payment_method') === PaymentMethod::Cheque->value
                    && ! $this->hasFile('cheque_image')
                    && ! $this->route('collection_entry')?->cheque_image),
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
