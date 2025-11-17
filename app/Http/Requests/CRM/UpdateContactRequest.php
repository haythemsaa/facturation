<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
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
        $contactId = $this->route('contact');

        return [
            'type' => ['sometimes', 'required', 'in:lead,prospect,customer'],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:100'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:contacts,email,' . $contactId],
            'phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'assigned_to_id' => ['nullable', 'exists:users,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'source' => ['nullable', 'in:website,referral,social_media,cold_call,event,other'],
            'status' => ['sometimes', 'required', 'in:new,contacted,qualified,lost'],
            'note' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Un contact avec cet email existe déjà.',
            'type.in' => 'Le type de contact doit être lead, prospect ou customer.',
            'status.in' => 'Le statut doit être new, contacted, qualified ou lost.',
        ];
    }
}
