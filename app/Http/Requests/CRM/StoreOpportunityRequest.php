<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpportunityRequest extends FormRequest
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
            'contact_id' => ['required', 'exists:contacts,id'],
            'pipeline_id' => ['required', 'exists:pipelines,id'],
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'value' => ['required', 'numeric', 'min:0'],
            'probability' => ['required', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['required', 'date', 'after_or_equal:today'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:open,won,lost'],
            'lost_reason' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'contact_id.exists' => 'Le contact spécifié n\'existe pas.',
            'pipeline_id.exists' => 'Le pipeline spécifié n\'existe pas.',
            'pipeline_stage_id.exists' => 'L\'étape du pipeline n\'existe pas.',
            'expected_close_date.after_or_equal' => 'La date de clôture doit être aujourd\'hui ou dans le futur.',
            'status.in' => 'Le statut doit être open, won ou lost.',
        ];
    }
}
