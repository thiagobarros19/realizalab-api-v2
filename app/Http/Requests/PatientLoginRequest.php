<?php

namespace App\Http\Requests;

use App\Rules\CpfRule;
use Illuminate\Foundation\Http\FormRequest;

class PatientLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'string', new CpfRule],
            'birthday' => ['required', 'date'],
        ];
    }

    public function normalizedDocument(): string
    {
        return preg_replace('/\D/', '', (string) $this->input('document'));
    }
}
