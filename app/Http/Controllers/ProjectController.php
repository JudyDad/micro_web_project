<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectSearchRequest;
use App\Models\WebPrincipal;
use App\Services\AuditTrail;
use App\Services\AuthorizeSource;
use App\Services\ProjectReadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectController
{
    public function cities(Request $request, AuthorizeSource $authorize): View
    {
        /** @var WebPrincipal $principal */
        $principal = $request->user();
        $cities = $authorize->check($principal, $principal->source_id)
            ? DB::table('sources as s')->join('cities as c', 'c.id', '=', 's.city_id')->where('s.id', $principal->source_id)->select(['s.id', 'c.name_en', 'c.name_ar'])->get()
            : collect();

        return view('cities.index', ['cities' => $cities, 'synthetic' => $this->synthetic($principal->source_id)]);
    }

    public function index(ProjectSearchRequest $request, string $locale, int $source, ProjectReadService $projects, AuditTrail $audit): View
    {
        $search = $request->validated('search') ?? '';
        $rows = $projects->search($request->user(), $source, $search);
        $audit->record($source, $request->user()->id, 'projects.list', 'succeeded');

        return view('projects.index', ['city' => $this->city($source), 'projects' => $rows, 'search' => $search, 'synthetic' => $this->synthetic($source)]);
    }

    public function show(Request $request, string $locale, int $source, string $record, ProjectReadService $projects, AuditTrail $audit): View
    {
        $record = filter_var($record, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($record === false, 404);
        $project = $projects->find($request->user(), $source, $record);
        $audit->record($source, $request->user()->id, 'projects.view', 'succeeded', 'project_record', $record);

        return view('projects.show', ['city' => $this->city($source), 'project' => $project, 'synthetic' => $this->synthetic($source)]);
    }

    private function city(int $source): object
    {
        return DB::table('sources as s')->join('cities as c', 'c.id', '=', 's.city_id')->where('s.id', $source)->select(['s.id', 'c.name_en', 'c.name_ar'])->firstOrFail();
    }

    private function synthetic(int $source): bool
    {
        return DB::table('source_publications')->where('source_id', $source)->value('publication_kind') === 'synthetic';
    }
}
