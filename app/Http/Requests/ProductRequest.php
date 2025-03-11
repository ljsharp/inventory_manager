<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'attributes' => 'nullable|array',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.sku' => 'nullable|exists:product_variants,sku',
            'variants.*.attributes' => 'required_with:variants|array',
            'variants.*.price' => 'required|numeric|min:1',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The product name is required.',
            'name.string' => 'The product name must be a valid string.',
            'name.max' => 'The product name cannot exceed 255 characters.',
            'name.unique' => 'A product with this name already exists.',

            'description.string' => 'The description must be a valid text.',

            'category_id.required' => 'Please select a category for the product.',
            'category_id.exists' => 'The selected category does not exist.',

            'price.required' => 'The product price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',
            'variants.*.attributes.required_with' => 'Each variant must have attributes when variants are provided.',
            'variants.*.attributes.array' => 'The attributes must be an array.',
            'variants.*.price.required' => 'Each variant must have a price.',
            'variants.*.price.numeric' => 'The price must be a number.',
            'variants.*.price.min' => 'The price must be at least 1.',
        ];
    }
}
