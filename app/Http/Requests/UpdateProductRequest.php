<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            'name'=> 'sometimes|string|max:255',
            'slug'=> [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($this->product), // يتجاهل المنتج الحالي
            ],
            'description'=> 'sometimes|string',
            'price'=> 'sometimes|numeric|min:0',
            'stock'=> 'sometimes|integer|min:0',
            'sku'=> [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products')->ignore($this->product),
            ],
            'is_active'=> 'sometimes|boolean',
        ];
    }
}
