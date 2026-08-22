<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\PerformanceGrade;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.edit') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'company_logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'company_email' => ['nullable', 'email', 'max:255'],

            'attendance_office_start_time' => ['required', 'date_format:H:i'],
            'attendance_late_grace_minutes' => ['required', 'integer', 'min:0', 'max:120'],

            'visit_gps_radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],

            'order_max_discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],

            'collection_overpayment_tolerance_percent' => ['required', 'numeric', 'min:0', 'max:100'],

            'target_grade_a_min' => ['required', 'integer', 'min:0', 'max:100'],
            'target_grade_b_min' => ['required', 'integer', 'min:0', 'max:100'],
            'target_grade_c_min' => ['required', 'integer', 'min:0', 'max:100'],
            'target_grade_d_min' => ['required', 'integer', 'min:0', 'max:100'],

            'low_performance_grades' => ['required', 'array', 'min:1'],
            'low_performance_grades.*' => [Rule::enum(PerformanceGrade::class)],

            'target_reminder_days_before_month_end' => ['required', 'integer', 'min:1', 'max:28'],
            'target_reminder_min_pct' => ['required', 'numeric', 'min:0', 'max:100'],

            'live_gps_stale_after_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $a = (int) $this->input('target_grade_a_min');
            $b = (int) $this->input('target_grade_b_min');
            $c = (int) $this->input('target_grade_c_min');
            $d = (int) $this->input('target_grade_d_min');

            if (! ($a >= $b && $b >= $c && $c >= $d)) {
                $validator->errors()->add(
                    'target_grade_a_min',
                    'Grade thresholds must be in descending order: A ≥ B ≥ C ≥ D.',
                );
            }
        });
    }
}
