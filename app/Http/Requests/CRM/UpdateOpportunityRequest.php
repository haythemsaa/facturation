<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpportunityRequest extends FormRequest
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
            'contact_id' => ['sometimes', 'required', 'exists:contacts,id'],
            'pipeline_id' => ['sometimes', 'required', 'exists:pipelines,id'],
            'pipeline_stage_id' => ['sometimes', 'required', 'exists:pipeline_stages,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'value' => ['sometimes', 'required', 'numeric', 'min:0'],
            'probability' => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['sometimes', 'required', 'date'],
            'actual_close_date' => ['nullable', 'date'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'status' => ['sometimes', 'required', 'in:open,won,lost'],
            'lost_reason' => ['nullable', 'string', 'required_if:status,lost'],
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
            'status.in' => 'Le statut doit être open, won ou lost.',
            'lost_reason.required_if' => 'La raison de la perte est obligatoire si le statut est "lost".',
        ];
    }
}
