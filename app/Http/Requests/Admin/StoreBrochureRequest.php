<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Brochure;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrochureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Brochure::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'is_published' => ['boolean'],
        ];
    }
}
