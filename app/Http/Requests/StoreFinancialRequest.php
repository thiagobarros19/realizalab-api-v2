<?php

namespace App\Http\Requests;

use App\Enum\FinancialCategoryEnum;
use App\Enum\FinancialTypeEnum;
use App\Enum\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreFinancialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_at' => ['required', 'date'],
            'type' => ['required', new Enum(FinancialTypeEnum::class)],
            'category' => ['required', new Enum(FinancialCategoryEnum::class)],
            'payment_method' => ['required', new Enum(PaymentMethodEnum::class)],
            'description' => ['nullable', 'string'],
            'financialable_id' => ['nullable', 'string'],
            'financialable_type' => ['nullable', 'string'],
        ];
    }
}
