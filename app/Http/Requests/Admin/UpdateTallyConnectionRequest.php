<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TallyApiFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTallyConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('tally_connection')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'connection_name' => ['required', 'string', 'max:255'],
            'tally_company_name' => ['required', 'string', 'max:255'],
            'tally_company_guid' => ['nullable', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'protocol' => ['required', Rule::in(['http', 'https'])],
            'api_format' => ['required', Rule::enum(TallyApiFormat::class)],
            'is_active' => ['boolean'],
        ];
    }
}
