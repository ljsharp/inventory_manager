<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockTransferRequest extends FormRequest
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
            'source_warehouse_id' => ['required', 'exists:warehouses,id'],
            'destination_warehouse_id' => ['required', 'exists:warehouses,id', 'different:source_warehouse_id'],
            'transfers' => ['required', 'array'],
            'transfers.*.product_id' => ['required', 'exists:products,id'],
            'transfers.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'transfers.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_warehouse_id.required' => 'The source warehouse is required for each transfer.',
            'source_warehouse_id.exists' => 'The selected source warehouse does not exist.',
            'destination_warehouse_id.required' => 'The destination warehouse is required for each transfer.',
            'destination_warehouse_id.exists' => 'The selected destination warehouse does not exist.',
            'destination_warehouse_id.different' => 'The source and destination warehouses must be different for each transfer.',
            'transfers.required' => 'Transfers data is required.',
            'transfers.array' => 'Transfers must be an array.',
            'transfers.*.product_id.required' => 'The product is required for each transfer.',
            'transfers.*.product_id.exists' => 'The selected product does not exist.',
            'transfers.*.product_variant_id.exists' => 'The selected product variant does not exist.',
            'transfers.*.quantity.required' => 'The quantity is required for each transfer.',
            'transfers.*.quantity.integer' => 'The quantity must be a valid number.',
            'transfers.*.quantity.min' => 'The quantity must be at least 1.',
        ];
    }
}
