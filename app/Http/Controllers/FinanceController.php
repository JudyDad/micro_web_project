<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Finance\FinanceService;
use App\Services\Finance\PaymentScheduleService;
use App\Services\Reports\TableExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FinanceController
{
    /** @return array<string, mixed> */
    private function paymentFilters(Request $request): array
    {
        return $request->validate([
            'cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'],
            'state' => ['sometimes', Rule::in(['pending', 'paid'])], 'year' => ['nullable', 'integer', 'between:2018,'.(now()->year + 2)],
            'months' => ['sometimes', 'array', 'max:12'], 'months.*' => ['integer', 'between:1,12'], 'group' => ['nullable', 'string', 'max:50'], 'format' => ['nullable', 'in:xlsx,csv'],
        ]);
    }

    public function paymentsExport(Request $request, string $locale, PaymentScheduleService $schedule, TableExport $export): StreamedResponse
    {
        $filters = $this->paymentFilters($request);
        $rows = $schedule->exportRows($request->user(), $filters);

        return $export->stream(TableExport::format($filters['format'] ?? null), 'instalments-'.now()->format('Ymd-His'), ['City', 'Project number', 'Beneficiary', 'Partner', 'Group', 'Currency', 'Amount', 'Due date', 'Paid', 'Received date'], $rows, [1, 6], app()->isLocale('ar'));
    }

    public function payments(Request $request, string $locale, PaymentScheduleService $schedule): View
    {
        $filters = $this->paymentFilters($request);

        return view('finance.payments', $schedule->schedule($request->user(), $filters) + ['filters' => $filters + ['state' => 'pending']]);
    }

    /** Type-ahead matches for opening a project's finance page by project number, project name or beneficiary name: JSON `[{id, label, url}]`. */
    public function projectLookup(Request $request, string $locale, PaymentScheduleService $schedule): JsonResponse
    {
        $input = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $matches = $schedule->lookupProjects($request->user(), (string) ($input['q'] ?? ''))->map(function ($match) use ($locale) {
            $match->url = route('finance.show', ['locale' => $locale, 'application' => $match->id]);

            return $match;
        });

        return response()->json($matches->values());
    }

    public function show(Request $request, string $locale, string $application, FinanceService $finance): View
    {
        return view('finance.show', $finance->show($request->user(), $this->id($application)) + ['applicationId' => $this->id($application)]);
    }

    public function donor(Request $request, string $locale, string $application, FinanceService $finance): RedirectResponse
    {
        $data = $request->validate(['version' => ['required', 'string', 'size:64'], 'donor_id' => ['nullable', 'integer', 'min:1']]);
        $finance->assignDonor($request->user(), $this->id($application), ($data['donor_id'] ?? null) === null ? null : (int) $data['donor_id'], $data['version']);

        return $this->back($locale, $application, 'donor');
    }

    public function group(Request $request, string $locale, string $application, FinanceService $finance): RedirectResponse
    {
        $data = $request->validate(['group_name' => ['required', 'string', 'max:50'], 'group_donor_id' => ['required', 'integer', 'min:1']]);
        $finance->createGroup($request->user(), $this->id($application), trim($data['group_name']), (int) $data['group_donor_id']);

        return $this->back($locale, $application, 'donor');
    }

    public function loan(Request $request, string $locale, string $application, FinanceService $finance): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'size:64'], 'loan_id' => ['nullable', 'integer', 'min:1'], 'group_id' => ['nullable', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'max:10'], 'exchange_rate' => ['nullable', 'string', 'max:40'], 'amount' => ['required', 'string', 'max:40'],
            'date' => ['nullable', 'string', 'max:10'], 'returned' => ['nullable', 'string', 'max:40'], 'payment' => ['nullable', 'string', 'max:40'],
            'note' => ['nullable', 'string', 'max:65535'], 'receipt' => ['nullable', 'string', 'max:50'],
        ]);
        $finance->saveLoan($request->user(), $this->id($application), $data, $data['version']);

        return $this->back($locale, $application, 'loan');
    }

    public function installments(Request $request, string $locale, string $application, FinanceService $finance): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'size:64'], 'loan_id' => ['required', 'integer', 'min:1'],
            'rows' => ['sometimes', 'array', 'max:300'], 'rows.*' => ['array'],
            'rows.*.id' => ['nullable', 'integer', 'min:1'], 'rows.*.amount' => ['nullable', 'string', 'max:40'], 'rows.*.due_date' => ['nullable', 'string', 'max:10'],
            'rows.*.paid' => ['nullable', 'in:0,1'], 'rows.*.received_date' => ['nullable', 'string', 'max:10'], 'rows.*.note' => ['nullable', 'string', 'max:65535'],
        ]);
        $finance->savePayments($request->user(), $this->id($application), (int) $data['loan_id'], $data['rows'] ?? [], $data['version']);

        return $this->back($locale, $application, 'loan-'.$data['loan_id']);
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }

    private function back(string $locale, string $application, string $fragment): RedirectResponse
    {
        return redirect()->route('finance.show', ['locale' => $locale, 'application' => $application])->withFragment($fragment)->with('status', __('finance.saved'));
    }
}
