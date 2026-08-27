<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'sometimes|filled|string|max:255',
            'phone' => 'sometimes|filled|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'name.filled' => 'الاسم لا يمكن أن يكون فارغًا.',
            'name.string' => 'الاسم يجب أن يكون نصًا.',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفًا.',

            'phone.filled' => 'رقم الهاتف لا يمكن أن يكون فارغًا.',
            'phone.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 20 حرفًا.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('name') && ! $this->filled('phone')) {
                $validator->errors()->add('name', 'يجب إرسال الاسم أو رقم الهاتف على الأقل.');
            }
        });
    }
}

