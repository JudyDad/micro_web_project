<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Field\MonitoringVisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MonitoringVisitController
{
    public function hub(Request $request, string $locale, string $application, MonitoringVisitService $visits): View
    {
        return view('field.me-hub', $visits->hub($request->user(), $this->id($application)) + ['applicationId' => $this->id($application)]);
    }

    public function create(Request $request, string $locale, string $application, string $person, MonitoringVisitService $visits): View
    {
        return view('field.me-visit', $visits->form($request->user(), $this->id($application), $this->id($person), null) + ['applicationId' => $this->id($application)]);
    }

    public function show(Request $request, string $locale, string $application, string $visit, MonitoringVisitService $visits): View
    {
        return view('field.me-visit', $visits->form($request->user(), $this->id($application), null, $this->id($visit)) + ['applicationId' => $this->id($application)]);
    }

    public function save(Request $request, string $locale, string $application, MonitoringVisitService $visits): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'size:64'], 'person_id' => ['required', 'integer', 'min:1'], 'visit_id' => ['nullable', 'integer', 'min:1'],
            'number' => ['nullable', 'string', 'max:3'], 'date' => ['nullable', 'string', 'max:10'], 'profit' => ['nullable', 'string', 'max:40'],
            'ans1' => ['nullable', 'string', 'max:65535'], 'indicators' => ['nullable', 'string', 'max:65535'], 'notes' => ['nullable', 'string', 'max:65535'],
        ]);
        // Answers and their notes are keyed by catalog question id: a{id} (answer id) and n{id} (note).
        $answers = collect($request->all())->filter(fn ($value, $key) => is_string($key) && preg_match('/\A[an][1-9][0-9]{0,18}\z/', $key) === 1 && (is_string($value) || is_int($value)))->all();
        $id = $visits->save($request->user(), $this->id($application), (int) $data['person_id'], ($data['visit_id'] ?? null) === null ? null : (int) $data['visit_id'], $data + $answers, $data['version']);

        return redirect()->route('me.show', ['locale' => $locale, 'application' => $application, 'visit' => $id])->with('status', __('field.saved'));
    }

    public function delete(Request $request, string $locale, string $application, string $visit, MonitoringVisitService $visits): RedirectResponse
    {
        $data = $request->validate(['version' => ['required', 'string', 'size:64']]);
        $visits->delete($request->user(), $this->id($application), $this->id($visit), $data['version']);

        return redirect()->route('me.hub', ['locale' => $locale, 'application' => $application])->with('status', __('field.deleted'));
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
