<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyVoucherRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:course,package'],
            'course_slug' => ['nullable', 'required_if:type,course', 'string', 'exists:courses,slug'],
            'package_id' => ['nullable', 'required_if:type,package', 'integer', 'exists:course_packages,id'],
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Kode voucher wajib diisi.',
            'course_slug.exists' => 'Kursus tidak valid.',
            'package_id.exists' => 'Paket tidak valid.',
        ];
    }
}
