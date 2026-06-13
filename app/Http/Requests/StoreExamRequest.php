<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
            'price_sus' => ['required', 'numeric', 'min:0'],
            'price_particular' => ['required', 'numeric', 'min:0'],
            'partner_id' => ['required', 'ulid', 'exists:partners,id'],
        ];
    }
}
