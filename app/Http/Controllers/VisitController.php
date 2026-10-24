<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Field\VisitKinds;
use App\Services\Field\VisitScheduleService;
use App\Services\Field\VisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class VisitController
{
    public function schedule(Request $request, string $locale, VisitScheduleService $schedule): View
    {
        $filters = $request->validate([
            'cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'], 'year' => ['nullable', 'integer', 'between:2000,'.(now()->year + 5)],
            'months' => ['sometimes', 'array', 'max:12'], 'months.*' => ['integer', 'between:1,12'], 'kind' => ['nullable', 'in:V,O'],
            'number' => ['nullable', 'in:Opening,M1,M2,M3,M4,M5,Closing'], 'seen' => ['nullable', 'in:0,1'],
        ]);

        return view('field.schedule', $schedule->schedule($request->user(), $filters) + ['filters' => $filters]);
    }

    public function hub(Request $request, string $locale, string $application, VisitService $visits): View
    {
        return view('field.visits', $visits->hub($request->user(), $this->id($application)) + ['applicationId' => $this->id($application)]);
    }

    public function show(Request $request, string $locale, string $application, string $kind, VisitService $visits): View
    {
        return view('field.visit', $visits->form($request->user(), $this->id($application), $kind) + ['applicationId' => $this->id($application)]);
    }

    public function save(Request $request, string $locale, string $application, string $kind, VisitService $visits): RedirectResponse
    {
        $rules = [
            'version' => ['required', 'string', 'size:64'], 'date' => ['nullable', 'string', 'max:10'], 'vehicle' => ['nullable', 'string', 'max:255'],
            'street_id' => ['nullable', 'integer', 'min:1'], 'place_note' => ['nullable', 'string', 'max:255'], 'indicators' => ['nullable', 'string', 'max:65535'],
            'other_comments' => ['nullable', 'string', 'max:65535'],
        ];
        for ($n = 1; $n <= VisitService::QUESTION_SLOTS; $n++) {
            $rules['q'.$n.'_value'] = ['nullable', 'string', 'max:3'];
            $rules['q'.$n.'_text'] = ['nullable', 'string', 'max:65535'];
        }
        for ($n = 1; $n <= VisitService::OFFICE_SLOTS; $n++) {
            $rules['office'.$n.'_value'] = ['nullable', 'string', 'max:3'];
            $rules['office'.$n.'_text'] = ['nullable', 'string', 'max:65535'];
        }
        $data = $request->validate($rules);
        abort_unless(VisitKinds::has($kind), 404);
        $visits->save($request->user(), $this->id($application), $kind, $data, $data['version']);

        return redirect()->route('visits.show', ['locale' => $locale, 'application' => $application, 'kind' => $kind])->with('status', __('field.saved'));
    }

    public function delete(Request $request, string $locale, string $application, string $kind, VisitService $visits): RedirectResponse
    {
        $data = $request->validate(['version' => ['required', 'string', 'size:64']]);
        abort_unless(VisitKinds::has($kind), 404);
        $visits->delete($request->user(), $this->id($application), $kind, $data['version']);

        return redirect()->route('visits.hub', ['locale' => $locale, 'application' => $application])->with('status', __('field.deleted'));
    }

    public function restore(Request $request, string $locale, string $application, string $visit, VisitService $visits): RedirectResponse
    {
        $visits->restore($request->user(), $this->id($application), $this->id($visit));

        return redirect()->route('visits.hub', ['locale' => $locale, 'application' => $application])->with('status', __('field.restored'));
    }

    public function scheduleSeen(Request $request, string $locale, string $notice, VisitScheduleService $schedule): RedirectResponse
    {
        $data = $request->validate(['seen' => ['required', 'in:0,1']]);
        $schedule->setSeen($request->user(), $this->id($notice), (int) $data['seen']);

        return back()->with('status', __('field.saved'));
    }

    public function scheduleNote(Request $request, string $locale, string $notice, VisitScheduleService $schedule): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:65535']]);
        $schedule->setNote($request->user(), $this->id($notice), (string) ($data['note'] ?? ''));

        return back()->with('status', __('field.saved'));
    }

    public function scheduleReschedule(Request $request, string $locale, string $notice, VisitScheduleService $schedule): RedirectResponse
    {
        $data = $request->validate(['date' => ['required', 'string', 'max:10'], 'reason' => ['required', 'string', 'max:1000'], 'shift' => ['nullable', 'in:1']]);
        $count = $schedule->reschedule($request->user(), $this->id($notice), $data['date'], $data['reason'], ($data['shift'] ?? null) === '1');

        return back()->with('status', __('field.schedule.rescheduled', ['count' => $count]));
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
