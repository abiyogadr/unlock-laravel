<?php

namespace App\Http\Requests\LandingPage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && $this->route('landing_page')->user_id === auth()->id();
    }

    public function rules(): array
    {
        $pageId = $this->route('landing_page')->id;

        return [
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('landing_pages', 'slug')->ignore($pageId)],
            'title'            => ['required', 'string', 'max:255'],
            'bio'              => ['nullable', 'string', 'max:1000'],
            'avatar'           => ['nullable', 'image', 'max:2048'],
            'remove_avatar'    => ['nullable', 'boolean'],
            'cover_image'      => ['nullable', 'image', 'max:4096'],
            'remove_cover'     => ['nullable', 'boolean'],
            'template_type'    => ['required', 'string', 'in:bio,card,minimal'],
            'status'           => ['required', 'string', 'in:draft,published'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
