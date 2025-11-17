<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product');

        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'code' => ['sometimes', 'required', 'string', 'max:50', 'unique:products,code,' . $productId],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode,' . $productId],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'required', 'in:product,service,consumable'],
            'unit' => ['sometimes', 'required', 'string', 'max:50'],
            'purchase_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'minimum_price' => ['nullable', 'numeric', 'min:0'],
            'tva_rate' => ['sometimes', 'required', 'in:19,13,7,0'],
            'stock_alert_threshold' => ['nullable', 'numeric', 'min:0'],
            'track_stock' => ['boolean'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Ce code produit existe déjà.',
            'barcode.unique' => 'Ce code-barres existe déjà.',
            'tva_rate.in' => 'Le taux de TVA doit être 19%, 13%, 7% ou 0%.',
        ];
    }
}
