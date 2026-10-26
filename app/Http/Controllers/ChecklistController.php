<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Assessments\ChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ChecklistController
{
    public function show(Request $request, string $locale, string $application, string $person, ChecklistService $checklists): View
    {
        $header = $request->validate(['header' => ['nullable', 'integer', 'min:1']])['header'] ?? null;

        return view('checklists.show', $checklists->show($request->user(), $this->id($application), $this->id($person), $header === null ? null : (int) $header) + [
            'applicationId' => $this->id($application), 'fieldNames' => $checklists->fieldNames(), 'referenceNames' => $checklists->referenceNames(),
        ]);
    }

    /** Printable copy of one saved checklist. Like the desktop's print it needs the assessment-manage grant, and it prints what is stored. */
    public function print(Request $request, string $locale, string $application, string $person, ChecklistService $checklists, AccountAccess $access, AccountAuditTrail $audit): View|RedirectResponse
    {
        $header = $request->validate(['header' => ['nullable', 'integer', 'min:1']])['header'] ?? null;
        $data = $checklists->show($request->user(), $this->id($application), $this->id($person), $header === null ? null : (int) $header);
        $city = (int) $data['project']->city_id;
        if (! $access->check($request->user(), $city, 'assessments.manage')) {
            $audit->record($request->user()->id, 'access.denied', 'denied', $city);
            abort(403);
        }
        if ($data['needsHeaderChoice']) {
            return redirect()->route('checklists.show', ['locale' => $locale, 'application' => $application, 'person' => $person]);
        }
        $audit->record($request->user()->id, 'assessments.printed', 'succeeded', $city, subjectType: 'application_entity', subjectId: $this->id($application));

        return view('checklists.print', $data + ['applicationId' => $this->id($application)]);
    }

    public function save(Request $request, string $locale, string $application, string $person, ChecklistService $checklists): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'size:64'], 'header_id' => ['nullable', 'integer', 'min:1'],
            'fields' => ['sometimes', 'array', 'max:60'], 'fields.*' => ['nullable', 'string', 'max:4294967295'],
            'references' => ['sometimes', 'array', 'max:10'], 'references.*' => ['nullable', 'integer', 'min:1'],
            'members' => ['sometimes', 'array', 'max:200'], 'members.*' => ['array'],
        ]);
        // A blank new member row (no member chosen, no id) is the form's empty slot, not an entry.
        $members = array_values(array_filter($data['members'] ?? [], fn ($row) => filled($row['id'] ?? null) || filled($row['member_id'] ?? null)));
        $members = array_map(fn ($row) => array_map(fn ($value) => $value === '' ? null : $value, $row), $members);
        $headerId = $checklists->save($request->user(), $this->id($application), $this->id($person), ($data['header_id'] ?? null) === null ? null : (int) $data['header_id'],
            $data['fields'] ?? [], $data['references'] ?? [], $members, $data['version']);

        return redirect()->route('checklists.show', ['locale' => $locale, 'application' => $application, 'person' => $person, 'header' => $headerId])->with('status', __('lifecycle.checklist_saved'));
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
