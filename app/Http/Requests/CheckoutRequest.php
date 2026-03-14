<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'type' => ['required', 'in:course,package'],
            'course_id' => ['required','integer','exists:courses,id'],
            'package_id' => ['nullable','integer','exists:packages,id'],
            'payment_method' => ['required','in:idr,ustar'],
            'voucher_code' => ['nullable','string'],
        ];
    }
}
