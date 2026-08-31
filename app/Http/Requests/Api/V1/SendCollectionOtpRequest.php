<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\PaymentMethod;
use App\Models\Dealer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class SendCollectionOtpRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $dealerId = $this->input('dealer_id');

            if ($dealerId && ! Dealer::query()->visibleTo($this->user())->whereKey($dealerId)->exists()) {
                $validator->errors()->add('dealer_id', 'This dealer is outside your assigned territories.');
            }
        });
    }
}
