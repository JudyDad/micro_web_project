<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationFilterRequest;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Reports\ApplicationReport;
use App\Services\Reports\TableExport;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportController
{
    /** The statistics page's own numbers (same filters, same city scope and coverage) as one Excel sheet or CSV. */
    public function export(ApplicationFilterRequest $request, string $locale, ApplicationReport $report, AccountAuditTrail $audit, TableExport $export, AccountAccess $access): StreamedResponse
    {
        // The desktop let only administrators, managers and data entry export statistics: reports.export and applications.manage.
        $allowed = $access->citiesWithAll($request->user(), ['applications.view', 'applications.manage', 'reports.export'])->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selected = array_map('intval', $request->validated('cities') ?? []);
        if ($allowed === [] || array_diff($selected, $allowed) !== []) {
            $audit->record($request->user()->id, 'access.denied', 'denied');
            abort(403);
        }
        $statistics = $report->statistics($request->user(), $selected !== [] ? $selected : $allowed, $request->filters());
        $audit->record($request->user()->id, 'reports.exported', 'succeeded');

        return $export->stream(TableExport::format($request->validated('format')), 'statistics-'.now()->format('Ymd-His'), ['Section', 'Category', 'City', 'Records'], $report->exportRows($statistics), [3], app()->isLocale('ar'));
    }

    public function statistics(ApplicationFilterRequest $request, ApplicationReport $report, AccountAccess $access): View
    {
        $cities = $access->cities($request->user(), 'applications.view');
        $selected = array_map('intval', $request->validated('cities') ?? []);
        $filters = $request->filters();
        $statistics = $report->statistics($request->user(), $selected, $filters);

        return view('reports.statistics', [
            'cities' => $cities, 'selectedCities' => $selected, 'filters' => $filters, 'statistics' => $statistics,
            'stateOptions' => $report->stateOptions($cities->pluck('id')->map(fn ($id) => (int) $id)->all()),
        ]);
    }
}
