<?php

namespace App\Http\Requests;

use App\Enum\FinancialCategoryEnum;
use App\Enum\FinancialTypeEnum;
use App\Enum\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateFinancialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'paid_at' => ['sometimes', 'required', 'date'],
            'type' => ['sometimes', 'required', new Enum(FinancialTypeEnum::class)],
            'category' => ['sometimes', 'required', new Enum(FinancialCategoryEnum::class)],
            'payment_method' => ['sometimes', 'required', new Enum(PaymentMethodEnum::class)],
            'description' => ['sometimes', 'nullable', 'string'],
            'financialable_id' => ['sometimes', 'nullable', 'string'],
            'financialable_type' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
