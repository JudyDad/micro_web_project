<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Finance\ExecutiveFileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ExecutiveFileController
{
    public function show(Request $request, string $locale, string $application, ExecutiveFileService $files): View
    {
        return view('finance.executive', $files->show($request->user(), $this->id($application)) + ['applicationId' => $this->id($application)]);
    }

    public function save(Request $request, string $locale, string $application, ExecutiveFileService $files): RedirectResponse
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'size:64'], 'file_id' => ['nullable', 'integer', 'min:1'], 'number' => ['nullable', 'string', 'max:50'],
            'begin_date' => ['nullable', 'string', 'max:10'], 'current_date' => ['nullable', 'string', 'max:10'], 'impound_date' => ['nullable', 'string', 'max:10'],
            'impound_type' => ['nullable', 'string', 'max:100'], 'months' => ['nullable', 'string', 'max:4'],
        ]);
        $files->save($request->user(), $this->id($application), ($data['file_id'] ?? null) === null ? null : (int) $data['file_id'], $data, $data['version']);

        return redirect()->route('executive.show', ['locale' => $locale, 'application' => $application])->with('status', __('finance.saved'));
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
