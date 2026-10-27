<?php

namespace App\Http\Requests;

use App\Services\Reports\ApplicationFilters;

/** Search, city, stage, multi-select catalog filters (status, fund type, donor, parish, age...), dates and amounts shared by the application list and statistics. */
class ApplicationFilterRequest extends ProjectSearchRequest
{
    private const SINGLE = ['stage', 'date_from', 'date_to', 'fund_from', 'fund_to', 'amount_from', 'amount_to', 'fund_amount_from', 'fund_amount_to'];

    protected function prepareForValidation(): void
    {
        $wrapped = [];
        foreach (['state', ...ApplicationFilters::keys()] as $key) {
            $value = $this->input($key);
            if (is_string($value)) {
                $wrapped[$key] = $value === '' ? [] : [$value];
            }
        }
        $this->merge($wrapped);
    }

    public function rules(): array
    {
        $rules = parent::rules() + [
            'stage' => ['nullable', 'string', 'in:zero,project'], 'format' => ['nullable', 'in:xlsx,csv'],
            'date_from' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:2100-12-31', 'after_or_equal:1900-01-01'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:2100-12-31', 'after_or_equal:date_from'],
            'fund_from' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:2100-12-31', 'after_or_equal:1900-01-01'],
            'fund_to' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:2100-12-31', 'after_or_equal:fund_from'],
        ];
        foreach (['amount_from', 'amount_to', 'fund_amount_from', 'fund_amount_to'] as $key) {
            $rules[$key] = ['nullable', 'numeric', 'min:0', 'max:999999999999'];
        }
        foreach (['state', ...ApplicationFilters::keys()] as $key) {
            $rules[$key] = ['nullable', 'array', 'max:200'];
            $rules[$key.'.*'] = ['string', 'max:150'];
        }

        return $rules;
    }

    /** @return array<string, string|list<string>> single values as strings, multi-select filters as lists of names */
    public function filters(): array
    {
        $safe = $this->safe();
        $out = [];
        foreach (self::SINGLE as $key) {
            $out[$key] = (string) ($safe[$key] ?? '');
        }
        foreach (['state', ...ApplicationFilters::keys()] as $key) {
            $out[$key] = array_values(array_unique(array_filter((array) ($safe[$key] ?? []), fn ($v) => is_string($v) && $v !== '')));
        }

        return $out;
    }
}
