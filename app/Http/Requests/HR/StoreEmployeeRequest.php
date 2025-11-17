<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'employee_number' => ['required', 'string', 'max:50', 'unique:employees,employee_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'cin' => ['required', 'string', 'max:20', 'unique:employees,cin'],
            'cnss_number' => ['nullable', 'string', 'max:20', 'unique:employees,cnss_number'],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female'],
            'marital_status' => ['required', 'in:single,married,divorced,widowed'],
            'children_count' => ['required', 'integer', 'min:0', 'max:20'],
            'is_family_head' => ['required', 'boolean'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:employees,email'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'hire_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:active,inactive,terminated,suspended'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_number.unique' => 'Ce numéro d\'employé existe déjà.',
            'cin.unique' => 'Ce numéro CIN existe déjà.',
            'cnss_number.unique' => 'Ce numéro CNSS existe déjà.',
            'email.unique' => 'Cet email est déjà utilisé.',
        ];
    }
}
