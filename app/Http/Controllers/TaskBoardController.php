<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Staff\TaskBoardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TaskBoardController
{
    public function index(Request $request, string $locale, TaskBoardService $board): View
    {
        $filters = $request->validate([
            'kind' => ['nullable', 'in:f,a'], 'cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'], 'q' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'integer', 'min:0', 'max:1000'], 'donor' => ['nullable', 'string', 'max:200'], 'type' => ['nullable', 'string', 'max:200'],
            'tasks' => ['sometimes', 'array', 'max:60'], 'tasks.*' => ['nullable', 'in:0,1'],
        ]);
        $filters['tasks'] = array_filter($filters['tasks'] ?? [], fn ($flag) => $flag !== null);

        return view('staff.task-board', $board->board($request->user(), $filters));
    }

    public function toggle(Request $request, string $locale, TaskBoardService $board): RedirectResponse
    {
        $data = $request->validate(['project' => ['required', 'integer', 'min:1'], 'task' => ['required', 'integer', 'min:1', 'max:1000'], 'expected' => ['required', 'in:0,1'], 'done' => ['required', 'in:0,1']]);
        $board->toggle($request->user(), (int) $data['project'], (int) $data['task'], (int) $data['expected'], (int) $data['done']);

        return back()->with('status', __('staff.tasks.saved'));
    }
}
