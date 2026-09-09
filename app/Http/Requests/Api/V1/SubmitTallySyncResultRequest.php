<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\TallySyncErrorCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Authorization here is the tally.agent middleware (a machine credential,
 * not a human user/policy), so authorize() is always true — the middleware
 * already rejected the request before this class runs if the credential
 * was missing/invalid.
 */
class SubmitTallySyncResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'success' => ['required', 'boolean'],
            'response' => ['nullable', 'array'],
            'tally_guid' => ['nullable', 'string', 'max:255'],
            'tally_voucher_number' => ['nullable', 'string', 'max:255'],
            'error_code' => ['required_if:success,false', Rule::enum(TallySyncErrorCode::class)],
            'error_message' => ['required_if:success,false', 'string', 'max:2000'],
        ];
    }
}
