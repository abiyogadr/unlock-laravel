<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentPageRequest extends FormRequest
{
    public function authorize()
    {
        // even guest can see page because they will be redirected to login later
        return true;
    }

    public function rules()
    {
        // If this is a status page (after payment), don't require payment params
        $status = $this->query('status');
        $orderId = $this->query('order_id');
        
        if ($status && $orderId) {
            // Status page - only needs status and order_id
            return [
                'status' => ['required', 'string'],
                'order_id' => ['required', 'string'],
            ];
        }

        // Payment form page - requires type and either course_slug or package_id
        return [
            'type' => ['required', 'in:course,package'],
            'course_slug' => ['nullable', 'required_if:type,course', 'string', 'exists:courses,slug'],
            'package_id' => ['nullable', 'required_if:type,package', 'integer', 'exists:course_packages,id'],
        ];
    }

    public function messages()
    {
        return [
            'package_id.required' => 'Paket harus dipilih.',
        ];
    }
}
