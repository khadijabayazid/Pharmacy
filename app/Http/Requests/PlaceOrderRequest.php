<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isCustomer();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:255'],
            'prescription_id' => ['nullable', 'integer', 'exists:prescriptions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'prescription_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'address.required' => 'الموقع مطلوب.',
            'address.string' => 'الموقع يجب أن يكون نصًا.',
            'address.max' => 'الموقع يجب ألا يتجاوز 255 حرفًا.',

            'items.required' => 'يجب إضافة منتج واحد على الأقل للطلب.',
            'items.min' => 'يجب إضافة منتج واحد على الأقل للطلب.',
            'itmes.*.product_id.required' => 'المنتج مطلوب.',
            'items.*.product_id.exists' => 'أحد المنتجات المحددة غير موجود.',
            'items.*.quantity.' => 'الكمية مطلوبة.',
            'items.*.quantity.min' => 'الكمية يجب أن تكون واحدًا على الأقل.',
            'items.*.price.required' => 'سعر المنتج مطلوب.',
            'items.*.price.numeric' => 'سعر المنتج يجب أن يكون رقمًا.',

            'prescription_image.image' => 'يجب أن تكون الوصفة صورة.',
            'prescription_image.mimes' => 'صيغة الصورة يجب أن تكون jpg أو jpeg أو png أو webp.',
            'prescription_image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',
        ];
    }
}
