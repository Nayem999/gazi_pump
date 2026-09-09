<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Dealer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Self-service field retailer creation (Phase 7): a Sales Executive
 * registers one of a dealer's downstream shops on the spot, mirroring
 * Order/CollectionEntry's own mobile create shape.
 */
class StoreRetailerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('api.retailers.add') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dealer_id' => ['required', 'integer', Rule::exists('dealers', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'shipping_address' => ['nullable', 'string'],
        ];
    }

    /**
     * Same server-side backstop as StoreOrderRequest — the mobile app is
     * expected to only ever offer dealers from GET /dealers, which is
     * already scoped to the executive's own territory.
     */
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
