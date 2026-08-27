<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;
use Override;

class UpdateCategoryRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
             'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($categoryId),
             ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'اسم التصنيف مطلوب.',
            'name.string' => 'اسم التصنيف يجب أن يكون نصًا.',
            'name.max' => 'اسم التصنيف يجب ألا يتجاوز 100 حرف.',
            'name.unique' => 'اسم التصنيف مستخدم مسبقًا.',
        ];
    }
}
