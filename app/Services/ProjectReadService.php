<?php

namespace App\Services;

use App\Models\WebPrincipal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProjectReadService
{
    public function __construct(private SourceCatalog $catalog, private AuthorizeSource $authorize) {}

    private function query(WebPrincipal $principal, int $source): Builder
    {
        abort_unless($this->authorize->check($principal, $source), 403);
        $snapshot = DB::table('source_records')->where('id', $principal->source_record_id)->where('source_id', $source)->where('table_id', 117)->value('snapshot_id');

        return DB::table($this->catalog->table($source, 'microproject').' as p')
            ->join('source_records as r', function ($join) {
                $join->on('r.id', '=', 'p.record_id')->on('r.source_id', '=', 'p.origin_source_id')->on('r.table_id', '=', 'p.origin_table_id');
            })
            ->join('source_publications as pub', function ($join) {
                $join->on('pub.source_id', '=', 'r.source_id')->on('pub.snapshot_id', '=', 'r.snapshot_id');
            })
            ->where('r.source_id', $source)->where('r.table_id', $this->catalog->tableId('microproject'))->where('r.snapshot_id', $snapshot)
            ->whereIn('pub.publication_kind', app()->environment(['local', 'testing']) ? ['synthetic', 'validated'] : ['validated']);
    }

    public function search(WebPrincipal $principal, int $source, string $search): LengthAwarePaginator
    {
        $query = $this->query($principal, $source);
        if ($search !== '') {
            $literal = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
            $query->where(function (Builder $query) use ($literal, $search) {
                $query->whereRaw("p.MP_Name LIKE ? ESCAPE '!'", [$literal]);
                if (ctype_digit($search) && strlen($search) <= 10 && (int) $search <= 2147483647) {
                    $query->orWhere('p.MP_ID', (int) $search);
                }
            });
        }

        return $query->select(['r.id as record_id', 'p.MP_ID', 'p.MP_Name', 'p.MP_DateOfRequest', 'p.ApplicationState'])
            ->orderBy('p.MP_ID')->orderBy('r.id')->paginate(20)->withQueryString();
    }

    public function find(WebPrincipal $principal, int $source, int $record): object
    {
        $project = $this->query($principal, $source)->where('r.id', $record)
            ->select(['r.id as record_id', 'p.MP_ID', 'p.MP_Name', 'p.MP_DateOfRequest', 'p.ApplicationState', 'p.MP_PlaceAddress', 'p.MP_Note'])
            ->selectRaw('CAST(p.MP_RequestedAmount AS CHAR) AS MP_RequestedAmount')->first();
        abort_if($project === null, 404);

        return $project;
    }
}
