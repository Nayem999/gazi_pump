<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Announcement::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'audience' => ['required', Rule::enum(AnnouncementAudience::class)],
            'audience_role' => ['required_if:audience,role', 'nullable', 'string', Rule::exists('roles', 'name')],
            'audience_territory_id' => ['required_if:audience,territory', 'nullable', 'integer', Rule::exists('territories', 'id')],
            'audience_user_id' => ['required_if:audience,user', 'nullable', 'integer', Rule::exists('users', 'id')],
            'audience_dealer_id' => ['required_if:audience,dealer', 'nullable', 'integer', Rule::exists('dealers', 'id')],
        ];
    }
}
