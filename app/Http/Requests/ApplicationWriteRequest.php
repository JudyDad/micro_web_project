<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\Applications\PaperOrder;
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
            'beneficiary_mode' => ['sometimes', 'required', 'in:new,existing'],
            'beneficiary_id' => ['nullable', 'integer', 'min:1'],
            // One-form intake: the applicant's own record and family book travel with the project.
            'person' => ['sometimes', 'array'],
            'person.fields' => ['sometimes', 'array', 'max:60'],
            'person.null_fields' => ['sometimes', 'array', 'max:60'],
            'person.null_fields.*' => ['string', 'max:100'],
            'person.references' => ['sometimes', 'array', 'max:15'],
            'person.references.*' => ['nullable', 'integer', 'min:1'],
            'person.confirm_duplicate' => ['sometimes', 'boolean'],
            // The paper form's one save on the application page: the beneficiary's link row travels with the project.
            'link' => ['sometimes', 'array'],
            'link.fields' => ['sometimes', 'array', 'max:40'],
            'link.null_fields' => ['sometimes', 'array', 'max:40'],
            'link.null_fields.*' => ['string', 'max:100'],
            'family' => ['sometimes', 'array'],
            'family.id' => ['nullable', 'integer', 'min:1'],
            'family.number' => ['nullable', 'string', 'max:50'],
            'family.confirm_duplicate' => ['sometimes', 'boolean'],
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
        $person = $this->input('person');
        if (is_array($person)) {
            if (is_array($person['references'] ?? null)) {
                $person['references'] = array_map(fn ($value) => $value === '' ? null : $value, $person['references']);
            }
            $values['person'] = $person;
        }
        $family = $this->input('family');
        if (is_array($family)) {
            foreach (['id', 'number'] as $key) {
                if (($family[$key] ?? null) === '') {
                    $family[$key] = null;
                }
            }
            $values['family'] = $family;
        }
        $this->merge($values);
    }

    public function payload(): array
    {
        return $this->withNulls($this->validated('fields') ?? [], $this->validated('null_fields') ?? []);
    }

    /** The applicant's own fields for one-form intake, with explicit nulls applied. */
    public function personPayload(): array
    {
        return $this->withNulls($this->validated('person.fields') ?? [], $this->validated('person.null_fields') ?? []);
    }

    /** The beneficiary's link-row fields (story, has/has-not flags) posted with the paper form, with explicit nulls applied. */
    public function linkPayload(): array
    {
        return $this->withNulls($this->validated('link.fields') ?? [], $this->validated('link.null_fields') ?? []);
    }

    /** The beneficiary section as the intake service reads it: an existing person, or a new one to create. */
    public function personSpec(): array
    {
        $mode = $this->validated('beneficiary_mode') ?? ($this->validated('beneficiary_id') ? 'existing' : 'new');

        return [
            'id' => $mode === 'existing' ? ($this->validated('beneficiary_id') ? (int) $this->validated('beneficiary_id') : 0) : null,
            'fields' => $mode === 'existing' ? [] : $this->personPayload(),
            'references' => $mode === 'existing' ? [] : ($this->validated('person.references') ?? []),
            'confirm_duplicate' => $this->boolean('person.confirm_duplicate'),
        ];
    }

    public function familySpec(): array
    {
        return [
            'id' => $this->validated('family.id') ? (int) $this->validated('family.id') : null,
            'number' => $this->validated('family.number'),
            'confirm_duplicate' => $this->boolean('family.confirm_duplicate'),
        ];
    }

    private function withNulls(array $fields, array $nullFields): array
    {
        foreach ($nullFields as $name) {
            $fields[$name] = null;
        }
        // A form without "not recorded" boxes (the create form, as the desktop): an empty box is simply not recorded.
        if ($this->boolean('blank_is_null')) {
            $fields = array_map(fn ($value) => $value === '' ? null : $value, $fields);
        }

        return self::yearDates($fields);
    }

    /** Birth dates are entered as a year, as the desktop (`P_DOB_textBox` MaxLength 4): a four-digit value is stored as the first of January. */
    public static function yearDates(array $fields): array
    {
        foreach (PaperOrder::YEAR_DATES as $key) {
            if (isset($fields[$key]) && preg_match('/\A\d{4}\z/', (string) $fields[$key])) {
                $fields[$key] .= '-01-01';
            }
        }

        return $fields;
    }
}
