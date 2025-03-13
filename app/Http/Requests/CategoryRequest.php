<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:categories,name,' . $this->get('id'),
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a valid string.',
            'name.max' => 'The category name cannot exceed 255 characters.',
            'name.unique' => 'This category already exists.',
            'description.string' => 'The description must be a valid string.',
            'description.max' => 'The description cannot exceed 1000 characters.',
        ];
    }
}
