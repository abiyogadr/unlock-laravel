<?php

namespace App\Http\Requests\LandingPage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('landing_pages', 'slug')],
            'title'            => ['required', 'string', 'max:255'],
            'bio'              => ['nullable', 'string', 'max:1000'],
            'avatar'           => ['nullable', 'image', 'max:2048'],
            'cover_image'      => ['nullable', 'image', 'max:4096'],
            'template_type'    => ['required', 'string', 'in:bio,card,minimal'],
            'status'           => ['required', 'string', 'in:draft,published'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
