<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApplicationWriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Current city grants and aggregate ownership are checked by the services.
    }

    public function rules(): array
    {
        return [
            'city_id' => ['required', 'integer', 'between:1,5'],
            'create_token' => ['sometimes', 'required', 'string', 'size:64'],
            'version' => ['sometimes', 'required', 'string', 'size:64'],
            'fields' => ['sometimes', 'array', 'max:100'],
            'null_fields' => ['sometimes', 'array', 'max:100'],
            'null_fields.*' => ['string', 'max:100'],
            'references' => ['sometimes', 'array', 'max:30'],
            'references.*' => ['nullable', 'integer', 'min:1'],
            'beneficiary_id' => ['nullable', 'integer', 'min:1'],
            'person_id' => ['nullable', 'integer', 'min:1'],
            'role' => ['sometimes', 'required', 'in:beneficiary,partner'],
            'stage' => ['sometimes', 'required', 'in:preliminary,full'],
            'application_id' => ['nullable', 'integer', 'min:1'],
            'detail_id' => ['nullable', 'integer', 'min:1'],
            'link_id' => ['nullable', 'integer', 'min:1'],
            'parent_id' => ['nullable', 'integer', 'min:1'],
            'kind' => ['sometimes', 'required', 'in:application,people,agriculture,rehab,rehab_answers,powersource,risk,material,loss,illness,asset,aid,help,homepower'],
            'draft_id' => ['nullable', 'string', 'regex:/\A[a-f0-9]{32}\z/'],
            'draft_version' => ['nullable', 'string', 'size:64'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $values = [];
        foreach (['beneficiary_id', 'person_id', 'application_id', 'detail_id', 'link_id', 'parent_id', 'draft_id', 'draft_version'] as $key) {
            if ($this->input($key) === '') {
                $values[$key] = null;
            }
        }
        $references = $this->input('references');
        if (is_array($references)) {
            $values['references'] = array_map(fn ($value) => $value === '' ? null : $value, $references);
        }
        $this->merge($values);
    }

    public function payload(): array
    {
        $fields = $this->validated('fields') ?? [];
        foreach ($this->validated('null_fields') ?? [] as $name) {
            $fields[$name] = null;
        }

        return $fields;
    }
}
