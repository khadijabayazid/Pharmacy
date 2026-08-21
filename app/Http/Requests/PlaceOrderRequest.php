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
        ];
    }

    public function messages()
    {
        return [
            'address.required' => 'The address is required.',
            'prescription_id.exists' => 'The selected prescription is invalid.',
            'items.required' => 'You must provide at least one item in the order.',
            'items.min' => 'You must provide at least one item in the order.',
            'items.*.product_id.exists' => 'The selected product is invalid.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
        ];
    }
}
