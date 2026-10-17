<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_id' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'username' => ['required', 'string', 'max:200'],
            'password' => ['required', 'string', 'max:1024'],
        ];
    }
}
