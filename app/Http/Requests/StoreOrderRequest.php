<?php

namespace App\Http\Requests;

use App\Enum\OrderTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(OrderTypeEnum::class)],
            'patient_id' => ['nullable', 'string', Rule::exists('patients', 'id')],
            'exams' => ['nullable', 'array'],
            'exams.*' => ['required', 'string', Rule::exists('exams', 'id')],
        ];
    }
}
