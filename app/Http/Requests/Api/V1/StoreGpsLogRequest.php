<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Accepts a batch of pings in one request (not just a single point) since
 * field reps in low-connectivity areas queue several readings on the device
 * and sync them together once a signal is available.
 */
class StoreGpsLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('api.gps-logs.add') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'logs' => ['required', 'array', 'min:1', 'max:200'],
            'logs.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'logs.*.lng' => ['required', 'numeric', 'between:-180,180'],
            'logs.*.recorded_at' => ['required', 'date'],
            'logs.*.accuracy' => ['nullable', 'numeric', 'min:0'],
            'logs.*.speed' => ['nullable', 'numeric', 'min:0'],
            'logs.*.battery_level' => ['nullable', 'integer', 'between:0,100'],
        ];
    }
}
