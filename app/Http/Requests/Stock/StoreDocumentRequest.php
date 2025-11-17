<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
            'type' => ['required', 'in:quote,invoice,credit_note,delivery_note,purchase_order,receipt'],
            'date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'note' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],

            // Lines validation
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'exists:products,id'],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.tva_rate' => ['required', 'in:19,13,7,0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Le type de document n\'est pas valide.',
            'lines.required' => 'Le document doit contenir au moins une ligne.',
            'lines.*.product_id.exists' => 'Un ou plusieurs produits n\'existent pas.',
            'lines.*.tva_rate.in' => 'Le taux de TVA doit être 19%, 13%, 7% ou 0%.',
        ];
    }
}
