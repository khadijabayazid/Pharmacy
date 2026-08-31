<?php

namespace App\Http\Requests;

use App\Enums\RejectionReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Override;

class RejectOrderRequest extends ApiFormRequest
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
            'rejection_reason' => ['required', new Enum(RejectionReason::class)],
        ];
    }

    #[Override]
    public function messages():array
    {
        return[
            'rejection_reason.required' => 'يرجى تحديد سبب الرفض.',
            'rejection_reason.enum' => 'سبب الرفض غير واضح'
        ];
    }
}
