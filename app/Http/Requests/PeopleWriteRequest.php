<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeopleWriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // The service checks current city grants inside each mutation.
    }

    public function rules(): array
    {
        return [
            'create_token' => ['sometimes', 'required', 'string', 'size:64'],
            'city_id' => ['sometimes', 'required', 'integer', 'between:1,5'],
            'fields' => ['sometimes', 'array', 'max:60'],
            'null_fields' => ['sometimes', 'array', 'max:60'],
            'null_fields.*' => ['string', 'max:100'],
            'references' => ['sometimes', 'array', 'max:15'],
            'references.*' => ['nullable', 'integer', 'min:1'],
            'version' => ['sometimes', 'required', 'string', 'max:128'],
            'family_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'related_person_id' => ['nullable', 'integer', 'min:1'],
            'detail_id' => ['nullable', 'integer', 'min:1'],
            'membership_id' => ['nullable', 'integer', 'min:1'],
            'confirm_duplicate' => ['sometimes', 'boolean'],
        ];
    }

    public function payload(): array
    {
        $fields = $this->validated('fields') ?? [];
        foreach ($this->validated('null_fields') ?? [] as $name) {
            $fields[$name] = null;
        }

        return ApplicationWriteRequest::yearDates($fields);
    }
}
