<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectSearchRequest;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\ProjectReadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectController
{
    public function index(ProjectSearchRequest $request, ProjectReadService $projects, AccountAccess $access, AccountAuditTrail $audit): View
    {
        $search = $request->validated('search') ?? '';
        $selected = array_map('intval', $request->validated('cities') ?? []);
        $rows = $projects->search($request->user(), $selected, $search);
        $audit->record($request->user()->id, 'projects.list', 'succeeded');

        return view('projects.index', ['cities' => $access->cities($request->user()), 'selectedCities' => $selected, 'projects' => $rows, 'search' => $search, 'synthetic' => $rows->getCollection()->contains('publication_kind', 'synthetic')]);
    }

    public function show(Request $request, string $locale, string $record, ProjectReadService $projects, AccountAuditTrail $audit): View
    {
        $project = $projects->find($request->user(), $this->recordId($record));
        $audit->record($request->user()->id, 'projects.view', 'succeeded', city: (int) $project->city_id, subjectType: 'project_record', subjectId: (int) $project->record_id);

        return view('projects.show', ['project' => $project, 'synthetic' => $project->publication_kind === 'synthetic']);
    }

    public function legacyIndex(Request $request, string $locale, int $source, AccountAccess $access, AccountAuditTrail $audit): RedirectResponse
    {
        $city = DB::table('sources')->where('id', $source)->value('city_id');
        abort_if($city === null, 404);
        if (! $access->check($request->user(), (int) $city)) {
            $audit->record($request->user()->id, 'access.denied', 'denied');
            abort(403);
        }

        return redirect()->route('projects.index', ['locale' => $locale, 'cities' => [(int) $city]]);
    }

    public function legacyShow(Request $request, string $locale, int $source, string $record, ProjectReadService $projects): RedirectResponse
    {
        $project = $projects->find($request->user(), $this->recordId($record), $source);

        return redirect()->route('projects.show', ['locale' => $locale, 'record' => $project->record_id]);
    }

    private function recordId(string $record): int
    {
        $id = filter_var($record, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
