<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\District;
use App\Models\Thana;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTerritoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('territory')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'thana_id' => ['required', 'integer', 'exists:thanas,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('territories', 'code')->ignore($this->route('territory')->id)],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'center_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'center_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'boundary' => ['nullable', 'json'],
            'status' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled(['division_id', 'district_id', 'thana_id'])) {
                return;
            }

            $district = District::find($this->input('district_id'));

            if ($district && $district->division_id !== (int) $this->input('division_id')) {
                $validator->errors()->add('district_id', 'The selected district does not belong to the selected division.');
            }

            $thana = Thana::find($this->input('thana_id'));

            if ($thana && $thana->district_id !== (int) $this->input('district_id')) {
                $validator->errors()->add('thana_id', 'The selected thana does not belong to the selected district.');
            }
        });
    }
}
