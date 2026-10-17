<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Source/capability middleware runs before controller access.
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:200'], 'page' => ['sometimes', 'integer', 'min:1', 'max:1000000']];
    }
}
