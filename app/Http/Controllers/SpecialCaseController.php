<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Field\SpecialCaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SpecialCaseController
{
    public function show(Request $request, string $locale, string $application, SpecialCaseService $cases): View
    {
        return view('field.special-cases', $cases->show($request->user(), $this->id($application)) + ['applicationId' => $this->id($application)]);
    }

    public function save(Request $request, string $locale, string $application, SpecialCaseService $cases): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'size:64'], 'case_id' => ['nullable', 'integer', 'min:1'], 'type_id' => ['nullable', 'integer', 'min:1'],
            'date' => ['nullable', 'string', 'max:10'], 'reason' => ['nullable', 'string', 'max:65535'], 'description' => ['nullable', 'string', 'max:65535'], 'action' => ['nullable', 'string', 'max:65535'],
        ]);
        $id = $cases->save($request->user(), $this->id($application), $data, $data['version']);

        return redirect()->route('special-cases.show', ['locale' => $locale, 'application' => $application])->withFragment('case-'.$id)->with('status', __('field.saved'));
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
