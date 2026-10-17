<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // The service checks every requested city against current grants.
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:200'], 'cities' => ['sometimes', 'array', 'max:5'], 'cities.*' => ['required', 'integer', 'distinct', 'min:1', 'max:5'], 'page' => ['sometimes', 'integer', 'min:1', 'max:1000000']];
    }
}
