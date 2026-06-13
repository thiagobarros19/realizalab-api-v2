<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['sometimes', 'required', 'ulid', 'exists:orders,id'],
            'exam_id' => ['sometimes', 'required', 'ulid', 'exists:exams,id'],
            'exam_name' => ['sometimes', 'required', 'string', 'max:255'],
            'exam_price' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
