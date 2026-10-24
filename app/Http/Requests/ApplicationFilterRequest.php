<?php

namespace App\Http\Requests;

/** Search, city, stage, lifecycle-state and request-date filters shared by the application list and statistics. */
class ApplicationFilterRequest extends ProjectSearchRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'stage' => ['nullable', 'string', 'in:zero,project'],
            'state' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:2100-12-31', 'after_or_equal:1900-01-01'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:2100-12-31', 'after_or_equal:date_from'],
        ];
    }

    /** @return array{stage: string, state: string, date_from: string, date_to: string} */
    public function filters(): array
    {
        return array_map(fn ($value) => (string) ($value ?? ''), $this->safe()->only(['stage', 'state', 'date_from', 'date_to']) + ['stage' => '', 'state' => '', 'date_from' => '', 'date_to' => '']);
    }
}
