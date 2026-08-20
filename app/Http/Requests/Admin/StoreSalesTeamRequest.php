<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\SalesTeam;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SalesTeam::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:sales_teams,code'],
            'description' => ['nullable', 'string'],
            'status' => ['boolean'],
        ];
    }
}
