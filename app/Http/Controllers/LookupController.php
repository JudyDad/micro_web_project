<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Reports\LookupService;
use App\Services\Reports\TableExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** The lookup tables, listed with their row counts and exported whole (Excel or CSV). */
final class LookupController
{
    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        return $request->validate(['cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'], 'format' => ['nullable', 'in:xlsx,csv']]);
    }

    public function index(Request $request, string $locale, LookupService $lookups): View
    {
        $filters = $this->filters($request);

        return view('admin.lookups', $lookups->index($request->user(), $filters) + ['filters' => $filters, 'selectedCities' => array_map('intval', $filters['cities'] ?? [])]);
    }

    public function export(Request $request, string $locale, string $table, LookupService $lookups, TableExport $export): StreamedResponse
    {
        $filters = $this->filters($request);
        [$headings, $rows] = $lookups->exportRows($request->user(), $table, $filters);

        return $export->stream(TableExport::format($filters['format'] ?? null), 'lookup-'.$table.'-'.now()->format('Ymd-His'), $headings, $rows, [], app()->isLocale('ar'));
    }
}
