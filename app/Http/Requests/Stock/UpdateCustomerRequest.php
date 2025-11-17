<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer');

        return [
            'type' => ['sometimes', 'required', 'in:individual,company'],
            'code' => ['sometimes', 'required', 'string', 'max:50', 'unique:customers,code,' . $customerId],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:customers,email,' . $customerId],
            'phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'country' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:50', 'required_if:type,company'],
            'payment_terms' => ['sometimes', 'required', 'in:immediate,net_15,net_30,net_60,net_90'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Le type doit être individual ou company.',
            'code.unique' => 'Ce code client existe déjà.',
            'email.unique' => 'Cette adresse email existe déjà.',
            'tax_id.required_if' => 'Le numéro de TVA est obligatoire pour les entreprises.',
            'payment_terms.in' => 'Les conditions de paiement doivent être immediate, net_15, net_30, net_60 ou net_90.',
            'credit_limit.min' => 'La limite de crédit doit être positive.',
            'discount_rate.max' => 'Le taux de remise ne peut pas dépasser 100%.',
        ];
    }
}
