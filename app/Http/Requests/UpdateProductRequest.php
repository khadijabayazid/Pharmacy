<?php

namespace App\Http\Requests;

use App\Enums\ProductDetailType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends ApiFormRequest
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
        $productId = $this->route('product')->id;

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:150',
             Rule::unique('products', 'name')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'required', 'integer' ,'min:0'],
            'is_required_prescription' => ['sometimes', 'required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp','max:2048'],
            'details' => ['nullable', 'array'],
            'details.*.type' => ['required', new Enum(ProductDetailType::class)],
            'details.*.content' => ['required', 'string'],
        ];
    }

     public function messages()
    {
        return[
            'category_id.required' => 'التصنيف مطلوب.',
            'category_id.integer' => 'التصنيف غير صالح.',
            'category_id.exists' => 'التصنيف المحدد غير موجود.',

            'name.unique' => 'يوجد منتج بنفس الاسم مسبقًا.',
            'name.required' => 'اسم المنتج مطلوب.',
            'name.string' => 'اسم المنتج يجب أن يكون نصًا.',
            'name.max' => 'اسم المنتج يجب ألا يتجاوز 150 حرفًا.',

            'description.string' => 'الوصف يجب أن يكون نصًا.',

            'price.required' => 'السعر مطلوب.',
            'price.numeric' => 'السعر يجب أن يكون رقمًا.',
            'price.min' => 'السعر لا يمكن أن يكون سالبًا.',

            'quantity.required' => 'الكمية مطلوبة.',
            'quantity.integer' => 'الكمية يجب أن تكون رقمًا صحيحًا.',
            'quantity.min' => 'الكمية لا يمكن أن تكون سالبة.',

            'is_required_prescription.required' => 'يجب تحديد ما إذا كان المنتج يتطلب وصفة طبية.',
            'is_required_prescription.boolean' => 'القيمة يجب أن تكون صحيحة أو خاطئة.',

            'image.image' => 'الملف المرفوع يجب أن يكون صورة.',
            'image.mimes' => 'صيغة الصورة يجب أن تكون jpg أو jpeg أو png أو webp.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.',

            'details.array' => 'صيغة التفاصيل غير صحيحة.',
            'details.*.type.required' => 'نوع التفصيل مطلوب.',
            'details.*.type' => 'نوع التفصيل المحدد غير صالح.',
            'details.*.content.required' => 'محتوى التفصيل مطلوب.',
            'details.*.content.string' => 'محتوى التفصيل يجب أن يكون نصًا.',

        ];
    }
}
