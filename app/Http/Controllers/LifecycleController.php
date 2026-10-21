<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Lifecycle\LifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LifecycleController
{
    public function transition(Request $request, string $locale, string $application, LifecycleService $lifecycle): RedirectResponse
    {
        $data = $request->validate($this->rules() + ['state_id' => ['required', 'integer', 'min:1']]);
        $lifecycle->transition($request->user(), $this->id($application), (int) $data['state_id'], $this->reasonId($data), $data['note'] ?? null, $data['version']);

        return $this->back($locale, $application);
    }

    public function reason(Request $request, string $locale, string $application, LifecycleService $lifecycle): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $lifecycle->updateCurrent($request->user(), $this->id($application), $this->reasonId($data), $data['note'] ?? null, $data['version']);

        return $this->back($locale, $application);
    }

    private function rules(): array
    {
        return ['version' => ['required', 'string', 'size:64'], 'reason_id' => ['nullable', 'integer', 'min:1'], 'note' => ['nullable', 'string', 'max:65535']];
    }

    private function reasonId(array $data): ?int
    {
        return ($data['reason_id'] ?? null) === null ? null : (int) $data['reason_id'];
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }

    private function back(string $locale, string $application): RedirectResponse
    {
        return redirect()->route('applications.show', ['locale' => $locale, 'application' => $application])->withFragment('lifecycle')->with('status', __('lifecycle.saved'));
    }
}
