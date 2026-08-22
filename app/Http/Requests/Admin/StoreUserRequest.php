<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * Blank out an empty employee_id so it stores as NULL rather than an
     * empty string — the column is optional but still unique, and multiple
     * empty strings would collide where multiple NULLs would not.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('employee_id') === '') {
            $this->merge(['employee_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users,employee_id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'designation' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'sales_team_id' => ['nullable', 'integer', Rule::exists('sales_teams', 'id')],
            'territory_ids' => ['nullable', 'array'],
            'territory_ids.*' => ['integer', Rule::exists('territories', 'id')],
            'status' => ['boolean'],
            'password' => ['required', Password::min(8)->letters()->numbers(), 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
        ];
    }
}
