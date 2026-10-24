<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationFilterRequest;
use App\Services\AccountAccess;
use App\Services\Reports\ApplicationReport;
use Illuminate\View\View;

final class ReportController
{
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
