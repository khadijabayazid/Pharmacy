<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'كلمة البحث مطلوبة.',
            'q.string' => 'كلمة البحث يجب أن تكون نصًا.',
            'q.min' => 'كلمة البحث يجب أن تحتوي على حرف واحد على الأقل.',
        ];
    }
}
