<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryRequest extends ApiFormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'vehicle_type' => ['required', 'string', 'max:255'],
            'vehicle_number' => ['required', 'string', 'max:255'],
            'is_available' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المندوب مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'vehicle_type.required' => 'نوع المركبة مطلوب.',
            'vehicle_number.required' => 'رقم المركبة مطلوب.',
            'is_available.required' => 'حالة التوفر مطلوبة.',
            'is_available.boolean' => 'حالة التوفر يجب أن تكون قيمة صحيحة.',
        ];
    }
}
