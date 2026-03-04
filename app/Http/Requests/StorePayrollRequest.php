<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'prime' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'advance' => 'nullable|numeric|min:0',
            'retention' => 'nullable|numeric|min:0',
            'adjustment_notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|in:cash,transfer,check',
            'comments' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee ID is required',
            'employee_id.exists' => 'The selected employee does not exist',
            'month.required' => 'Month is required',
            'month.min' => 'Month must be between 1 and 12',
            'month.max' => 'Month must be between 1 and 12',
            'year.required' => 'Year is required',
            'prime.numeric' => 'Prime must be a numeric value',
            'bonus.numeric' => 'Bonus must be a numeric value',
            'advance.numeric' => 'Advance must be a numeric value',
            'retention.numeric' => 'Retention must be a numeric value',
            'payment_method.in' => 'Payment method must be cash, transfer, or check',
        ];
    }
}
