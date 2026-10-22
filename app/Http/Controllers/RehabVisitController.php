<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Field\RehabVisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RehabVisitController
{
    public function show(Request $request, string $locale, string $application, RehabVisitService $rehab): View
    {
        return view('field.rehab-visit', $rehab->form($request->user(), $this->id($application)) + ['applicationId' => $this->id($application)]);
    }

    public function save(Request $request, string $locale, string $application, RehabVisitService $rehab): RedirectResponse
    {
        $rules = ['version' => ['required', 'string', 'size:64'], 'date' => ['nullable', 'string', 'max:10']];
        foreach (array_keys(RehabVisitService::QUESTIONS) as $n) {
            $rules['q'.$n.'_value'] = ['nullable', 'string', 'max:3'];
            $rules['q'.$n.'_text'] = ['nullable', 'string', 'max:65535'];
        }
        $data = $request->validate($rules);
        $rehab->save($request->user(), $this->id($application), $data, $data['version']);

        return redirect()->route('rehab.show', ['locale' => $locale, 'application' => $application])->with('status', __('field.saved'));
    }

    public function delete(Request $request, string $locale, string $application, RehabVisitService $rehab): RedirectResponse
    {
        $data = $request->validate(['version' => ['required', 'string', 'size:64']]);
        $rehab->delete($request->user(), $this->id($application), $data['version']);

        return redirect()->route('visits.hub', ['locale' => $locale, 'application' => $application])->with('status', __('field.deleted'));
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
