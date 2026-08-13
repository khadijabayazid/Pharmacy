<?php

namespace App\Http\Requests;

use App\Enums\ProductDetailType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories, id'],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'required', 'integer' ,'min:0'],
            'is_required_prescription' => ['sometimes', 'required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jbg,jpeg,png,webp','max:2048'],
            'details' => ['nullable', 'array'],
            'details.*.type' => ['required', new Enum(ProductDetailType::class)],
            'details.*.content' => ['required', 'string'],
        ];
    }

     public function messages()
    {
        return[
            'category_id.exists' => 'The selected category is invalid.',
            'price.min' => 'The price cannot be negative.',
            'image.image' => 'The uploaded file must be an image.',
            'details.*.type' => 'The selected detail type is invalid.',

        ];
    }
}
