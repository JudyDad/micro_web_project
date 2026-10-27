<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Reports\DetailExportService;
use App\Services\Reports\TableExport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Exports of the details grids: a project's state history and a beneficiary's family members. */
final class DetailExportController
{
    public function states(Request $request, string $locale, string $application, DetailExportService $details, TableExport $export): StreamedResponse
    {
        $format = $request->validate(['format' => ['nullable', 'in:xlsx,csv']])['format'] ?? null;
        [$headings, $rows] = $details->stateHistory($request->user(), (int) $application);

        return $export->stream(TableExport::format($format), 'project-'.(int) $application.'-states-'.now()->format('Ymd-His'), $headings, $rows, [1], app()->isLocale('ar'));
    }

    public function family(Request $request, string $locale, string $person, DetailExportService $details, TableExport $export): StreamedResponse
    {
        $format = $request->validate(['format' => ['nullable', 'in:xlsx,csv']])['format'] ?? null;
        [$headings, $rows] = $details->familyMembers($request->user(), (int) $person);

        return $export->stream(TableExport::format($format), 'person-'.(int) $person.'-family-'.now()->format('Ymd-His'), $headings, $rows, [1, 2], app()->isLocale('ar'));
    }
}
