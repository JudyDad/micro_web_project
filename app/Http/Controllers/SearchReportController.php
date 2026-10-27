<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Reports\Search\SearchModes;
use App\Services\Reports\TableExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** The desktop's search reports (`Search_Form` modes): a filtered list on screen and the same rows, all pages, as Excel or CSV. */
final class SearchReportController
{
    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        $date = ['nullable', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:2100-12-31'];
        // An end is compared with its start only when the start is given: a range may be open at either end.
        $endAfter = fn (string $start) => $request->filled($start) ? ['after_or_equal:'.$start] : [];
        $endNotBelow = fn (string $start) => $request->filled($start) ? ['gte:'.$start] : [];

        return $request->validate([
            'mode' => ['nullable', 'in:'.implode(',', SearchModes::keys())], 'cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'],
            'q' => ['nullable', 'string', 'max:100'], 'state' => ['nullable', 'string', 'max:100'], 'donor' => ['nullable', 'string', 'max:200'], 'group' => ['nullable', 'string', 'max:200'],
            'request_from' => $date, 'request_to' => [...$date, ...$endAfter('request_from')], 'loan_from' => $date, 'loan_to' => [...$date, ...$endAfter('loan_from')],
            'meeting_from' => $date, 'meeting_to' => [...$date, ...$endAfter('meeting_from')],
            'fund' => ['sometimes', 'array', 'max:50'], 'fund.*' => ['string', 'max:200'], 'type' => ['sometimes', 'array', 'max:50'], 'type.*' => ['string', 'max:200'],
            'subtype' => ['sometimes', 'array', 'max:100'], 'subtype.*' => ['string', 'max:200'], 'category' => ['sometimes', 'array', 'max:100'], 'category.*' => ['string', 'max:200'],
            'subcategory' => ['sometimes', 'array', 'max:200'], 'subcategory.*' => ['string', 'max:200'], 'career' => ['sometimes', 'array', 'max:300'], 'career.*' => ['string', 'max:200'],
            'partnership' => ['nullable', 'in:individual,partnership'],
            'funded_from' => ['nullable', 'numeric', 'min:0', 'max:1000000000000'], 'funded_to' => ['nullable', 'numeric', 'min:0', 'max:1000000000000', ...$endNotBelow('funded_from')],
            'format' => ['nullable', 'in:xlsx,csv'],
        ]) + ['mode' => 'loans'];
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $mode = SearchModes::make($filters['mode'] ?? 'loans');
        $result = $mode->page($request->user(), $filters);

        return view('reports.search', $result + [
            'mode' => $mode, 'modes' => SearchModes::keys(), 'filters' => $filters, 'selectedCities' => array_map('intval', $filters['cities'] ?? []),
            'options' => $mode->options($result['cities']),
        ]);
    }

    public function export(Request $request, string $locale, TableExport $export): StreamedResponse
    {
        $filters = $this->filters($request);
        $mode = SearchModes::make($filters['mode'] ?? 'loans');
        $rows = $mode->export($request->user(), $filters);

        return $export->stream(TableExport::format($filters['format'] ?? null), 'search-'.$mode->key().'-'.now()->format('Ymd-His'), $mode->headings(), $rows, $mode->numeric(), app()->isLocale('ar'));
    }
}
