<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AccountAccess;
use App\Services\Reports\TableExport;
use App\Services\Staff\NotificationInboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class NotificationController
{
    public function index(Request $request, string $locale, NotificationInboxService $inbox): View
    {
        $filters = $request->validate(['cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1']]);

        return view('staff.notifications', $inbox->inbox($request->user(), $filters) + ['filters' => $filters]);
    }

    /** Everything in the box, as an Excel workbook or CSV (the box's own authorization and city scope apply). */
    public function export(Request $request, string $locale, NotificationInboxService $inbox, TableExport $export): StreamedResponse
    {
        $filters = $request->validate(['cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'], 'format' => ['nullable', 'in:xlsx,csv']]);
        $rows = $inbox->exportRows($request->user(), $filters);

        return $export->stream(TableExport::format($filters['format'] ?? null), 'notifications-'.now()->format('Ymd-His'), ['City', 'Date', 'Type', 'Project number', 'Title', 'Message', 'Sender'], $rows, [3], app()->isLocale('ar'));
    }

    public function seen(Request $request, string $locale, NotificationInboxService $inbox): RedirectResponse
    {
        $data = $request->validate(['ids' => ['required', 'array', 'min:1', 'max:500'], 'ids.*' => ['integer', 'min:1']]);
        $count = $inbox->markSeen($request->user(), array_map('intval', $data['ids']));

        return redirect()->route('notifications.index', ['locale' => $locale])->with('status', __('staff.notifications.marked', ['count' => $count]));
    }

    public function create(Request $request, string $locale, NotificationInboxService $inbox): View
    {
        $cities = $inbox->sendCities($request->user());
        abort_if($cities->isEmpty(), 403);
        $city = (int) $request->integer('city', (int) $cities->first()->id);
        abort_unless($cities->contains('id', $city), 403);

        return view('staff.message', ['cities' => $cities, 'city' => $city, 'recipients' => $inbox->recipients($request->user(), $city), 'admin' => app(AccountAccess::class)->check($request->user(), $city, 'accounts.manage')]);
    }

    public function send(Request $request, string $locale, NotificationInboxService $inbox): RedirectResponse
    {
        $data = $request->validate([
            'city' => ['required', 'integer', 'min:1'], 'to' => ['required', 'string', 'max:20'], 'date' => ['nullable', 'string', 'max:10'],
            'title' => ['nullable', 'string', 'max:255'], 'body' => ['nullable', 'string', 'max:65535'], 'project' => ['nullable', 'string', 'max:19'],
        ]);
        $count = $inbox->send($request->user(), $data);

        return redirect()->route('notifications.create', ['locale' => $locale, 'city' => $data['city']])->with('status', __('staff.notifications.sent', ['count' => $count]));
    }
}
