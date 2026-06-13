<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:255'],
            'cost' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price_sus' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price_particular' => ['sometimes', 'required', 'numeric', 'min:0'],
            'partner_id' => ['sometimes', 'required', 'ulid', 'exists:partners,id'],
        ];
    }
}
