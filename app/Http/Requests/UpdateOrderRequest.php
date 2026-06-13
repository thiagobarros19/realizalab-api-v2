<?php

namespace App\Http\Requests;

use App\Enum\OrderTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'required', new Enum(OrderTypeEnum::class)],
            'patient_id' => ['sometimes', 'nullable', 'ulid', Rule::exists('patients', 'id')],
            'exams' => ['sometimes', 'nullable', 'array'],
            'exams.*' => ['required', 'ulid', Rule::exists('exams', 'id')],
        ];
    }
}
