<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'ulid', 'exists:orders,id'],
            'exam_id' => ['required', 'ulid', 'exists:exams,id'],
            'exam_name' => ['required', 'string', 'max:255'],
            'exam_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
