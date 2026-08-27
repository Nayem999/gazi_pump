<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\CashHandover;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCashHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CashHandover::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'handover_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
