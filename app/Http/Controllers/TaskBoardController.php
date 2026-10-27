<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Reports\TableExport;
use App\Services\Staff\TaskBoardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class TaskBoardController
{
    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        $filters = $request->validate([
            'kind' => ['nullable', 'in:f,a'], 'cities' => ['sometimes', 'array', 'max:20'], 'cities.*' => ['integer', 'min:1'], 'q' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'integer', 'min:0', 'max:1000'], 'donor' => ['nullable', 'string', 'max:200'], 'type' => ['nullable', 'string', 'max:200'],
            'tasks' => ['sometimes', 'array', 'max:60'], 'tasks.*' => ['nullable', 'in:0,1'], 'format' => ['nullable', 'in:xlsx,csv'],
        ]);
        $filters['tasks'] = array_filter($filters['tasks'] ?? [], fn ($flag) => $flag !== null);

        return $filters;
    }

    /** Every project of the board's current filters (all pages), one column per task, as an Excel workbook or CSV. */
    public function export(Request $request, string $locale, TaskBoardService $board, TableExport $export): StreamedResponse
    {
        $filters = $this->filters($request);
        [$taskColumns, $rows, $kind] = $board->exportRows($request->user(), $filters);
        $headings = array_merge(['City', 'Project number', 'Beneficiary', 'Mobile', 'Project name', 'Status', 'Donor', 'Type'], $kind === 'f' ? ['Loan date'] : [], $taskColumns);

        return $export->stream(TableExport::format($filters['format'] ?? null), ($kind === 'f' ? 'task-board-funded-' : 'task-board-applications-').now()->format('Ymd-His'), $headings, $rows, [1], app()->isLocale('ar'));
    }

    public function index(Request $request, string $locale, TaskBoardService $board): View
    {
        $filters = $this->filters($request);

        return view('staff.task-board', $board->board($request->user(), $filters));
    }

    public function toggle(Request $request, string $locale, TaskBoardService $board): RedirectResponse
    {
        $data = $request->validate(['project' => ['required', 'integer', 'min:1'], 'task' => ['required', 'integer', 'min:1', 'max:1000'], 'expected' => ['required', 'in:0,1'], 'done' => ['required', 'in:0,1']]);
        $board->toggle($request->user(), (int) $data['project'], (int) $data['task'], (int) $data['expected'], (int) $data['done']);

        return back()->with('status', __('staff.tasks.saved'));
    }
}
