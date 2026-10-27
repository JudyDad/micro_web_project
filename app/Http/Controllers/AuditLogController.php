<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Reports\TableExport;
use App\Services\Staff\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** The audit trail of the administrator's cities, on screen and as an Excel workbook or CSV. */
final class AuditLogController
{
    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        $date = ['nullable', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31'];

        return $request->validate([
            'cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'], 'from' => $date, 'to' => $date,
            'event' => ['nullable', 'string', 'max:64', 'regex:/\A[a-z_.]+\z/'], 'outcome' => ['nullable', 'in:succeeded,denied,failed'], 'actor' => ['nullable', 'string', 'max:100'],
            'format' => ['nullable', 'in:xlsx,csv'],
        ]);
    }

    public function index(Request $request, string $locale, AuditLogService $log): View
    {
        $filters = $this->filters($request);

        return view('admin.audit', $log->page($request->user(), $filters) + ['filters' => $filters, 'selectedCities' => array_map('intval', $filters['cities'] ?? [])]);
    }

    public function export(Request $request, string $locale, AuditLogService $log, TableExport $export): StreamedResponse
    {
        $filters = $this->filters($request);
        $rows = $log->exportRows($request->user(), $filters);

        return $export->stream(TableExport::format($filters['format'] ?? null), 'audit-log-'.now()->format('Ymd-His'), AuditLogService::headings(), $rows, [0, 7], app()->isLocale('ar'));
    }
}
