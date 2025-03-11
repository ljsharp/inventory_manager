<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
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
            'id' => 'nullable|exists:warehouses,id',
            'name' => 'required|string|max:255|unique:warehouses,name,' . $this->get('id'),
            'location' => 'required|string|max:500',
            'contact_info' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Warehouse name is required.',
            'name.unique' => 'A warehouse with this name already exists.',
            'location.required' => 'Warehouse location is required.',
            'capacity.required' => 'Capacity is required and must be a positive number.',
        ];
    }
}
