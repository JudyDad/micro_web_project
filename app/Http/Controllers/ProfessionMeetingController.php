<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Field\ProfessionMeetingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProfessionMeetingController
{
    private const FIELDS = ['career_id', 'date', 'place', 'invited', 'attendance', 'oc', 'os', 'ot', 'tx', 'cc', 'cr', 'season', 'equipment', 'challenge', 'brands', 'part', 'attendees', 'minutes'];

    public function index(Request $request, string $locale, ProfessionMeetingService $meetings): View
    {
        $filters = $request->validate([
            'cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'], 'year' => ['nullable', 'integer', 'between:2000,'.(now()->year + 5)],
        ]);

        return view('field.meetings', $meetings->index($request->user(), $filters) + ['filters' => $filters, 'deleted' => $meetings->deleted($request->user())]);
    }

    public function create(Request $request, string $locale, ProfessionMeetingService $meetings): View
    {
        $city = filter_var($request->query('city'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($city === false, 404);

        return view('field.meeting', $meetings->form($request->user(), null, $city));
    }

    public function show(Request $request, string $locale, string $meeting, ProfessionMeetingService $meetings): View
    {
        return view('field.meeting', $meetings->form($request->user(), $this->id($meeting), null));
    }

    public function save(Request $request, string $locale, ProfessionMeetingService $meetings): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'size:64'], 'meeting_id' => ['nullable', 'integer', 'min:1'], 'city_id' => ['nullable', 'integer', 'min:1'],
            'career_id' => ['nullable', 'integer', 'min:1'], 'date' => ['nullable', 'string', 'max:10'], 'place' => ['nullable', 'string', 'max:300'],
            'invited' => ['nullable', 'string', 'max:8'], 'attendance' => ['nullable', 'string', 'max:8'], 'brands' => ['nullable', 'string', 'max:65535'],
            'oc' => ['nullable', 'array', 'max:500'], 'os' => ['nullable', 'array', 'max:500'], 'ot' => ['nullable', 'array', 'max:500'], 'tx' => ['nullable', 'array', 'max:100'],
            'cc' => ['nullable', 'array', 'max:10'], 'cr' => ['nullable', 'array', 'max:10'], 'season' => ['nullable', 'array', 'max:200'], 'equipment' => ['nullable', 'array', 'max:200'],
            'challenge' => ['nullable', 'array', 'max:200'], 'part' => ['nullable', 'array', 'max:20'], 'attendees' => ['nullable', 'array', 'max:200'], 'minutes' => ['nullable', 'array', 'max:20'],
        ]);
        $id = $meetings->save($request->user(), ($data['meeting_id'] ?? null) === null ? null : (int) $data['meeting_id'], ($data['city_id'] ?? null) === null ? null : (int) $data['city_id'], array_intersect_key($request->all(), array_flip(self::FIELDS)), $data['version']);

        return redirect()->route('meetings.show', ['locale' => $locale, 'meeting' => $id])->with('status', __('field.saved'));
    }

    public function delete(Request $request, string $locale, string $meeting, ProfessionMeetingService $meetings): RedirectResponse
    {
        $data = $request->validate(['version' => ['required', 'string', 'size:64']]);
        $meetings->delete($request->user(), $this->id($meeting), $data['version']);

        return redirect()->route('meetings.index', ['locale' => $locale])->with('status', __('field.deleted'));
    }

    public function restore(Request $request, string $locale, string $meeting, ProfessionMeetingService $meetings): RedirectResponse
    {
        $meetings->restore($request->user(), $this->id($meeting));

        return redirect()->route('meetings.index', ['locale' => $locale])->with('status', __('field.restored'));
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
