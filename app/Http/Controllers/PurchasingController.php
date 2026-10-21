<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Finance\PurchasingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PurchasingController
{
    public function show(Request $request, string $locale, string $application, PurchasingService $purchasing): View
    {
        return view('finance.purchasing', $purchasing->show($request->user(), $this->id($application)) + ['applicationId' => $this->id($application)]);
    }

    public function save(Request $request, string $locale, string $application, PurchasingService $purchasing): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'size:64'], 'rows' => ['sometimes', 'array', 'max:300'], 'rows.*' => ['array'],
            'rows.*.id' => ['nullable', 'integer', 'min:1'], 'rows.*.name' => ['nullable', 'string', 'max:300'], 'rows.*.brand' => ['nullable', 'string', 'max:300'],
            'rows.*.category_id' => ['nullable', 'integer', 'min:1'], 'rows.*.quantity' => ['nullable', 'string', 'max:12'], 'rows.*.price' => ['nullable', 'string', 'max:40'],
            'rows.*.currency' => ['nullable', 'string', 'max:10'], 'rows.*.invoice_date' => ['nullable', 'string', 'max:10'],
            'rows.*.invoice_number' => ['nullable', 'string', 'max:100'], 'rows.*.trader' => ['nullable', 'string', 'max:300'],
        ]);
        $purchasing->save($request->user(), $this->id($application), $data['rows'] ?? [], $data['version']);

        return redirect()->route('purchasing.show', ['locale' => $locale, 'application' => $application])->with('status', __('finance.saved'));
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
