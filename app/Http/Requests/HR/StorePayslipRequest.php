<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StorePayslipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'worked_days' => ['required', 'integer', 'min:0', 'max:31'],
            'worked_hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],

            // Optional earnings and deductions
            'earnings' => ['nullable', 'array'],
            'earnings.*.description' => ['required', 'string', 'max:255'],
            'earnings.*.amount' => ['required', 'numeric', 'min:0'],

            'deductions' => ['nullable', 'array'],
            'deductions.*.description' => ['required', 'string', 'max:255'],
            'deductions.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.exists' => 'L\'employé spécifié n\'existe pas.',
            'month.between' => 'Le mois doit être entre 1 et 12.',
            'worked_days.max' => 'Le nombre de jours travaillés ne peut pas dépasser 31.',
        ];
    }
}
