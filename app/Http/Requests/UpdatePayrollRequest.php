<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prime' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'advance' => 'nullable|numeric|min:0',
            'retention' => 'nullable|numeric|min:0',
            'adjustment_notes' => 'nullable|string|max:1000',
            'comments' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|in:cash,transfer,check',
        ];
    }

    public function messages(): array
    {
        return [
            'prime.numeric' => 'Prime must be a numeric value',
            'bonus.numeric' => 'Bonus must be a numeric value',
            'advance.numeric' => 'Advance must be a numeric value',
            'retention.numeric' => 'Retention must be a numeric value',
            'payment_method.in' => 'Payment method must be cash, transfer, or check',
        ];
    }
}
