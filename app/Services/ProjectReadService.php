<?php

namespace App\Services;

use App\Models\WebAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as EmptyPaginator;
use Illuminate\Support\Facades\DB;

/** Interim read adapter over preserved tables, pending shared domain-table migration. */
class ProjectReadService
{
    public function __construct(private SourceCatalog $catalog, private AccountAccess $access, private AccountAuditTrail $audit) {}

    private function query(WebAccount $account, int $source): Builder
    {
        return DB::table($this->catalog->table($source, 'microproject').' as p')
            ->join('source_records as r', function ($join) {
                $join->on('r.id', '=', 'p.record_id')->on('r.source_id', '=', 'p.origin_source_id')->on('r.table_id', '=', 'p.origin_table_id');
            })
            ->join('sources as s', 's.id', '=', 'r.source_id')
            ->join('cities as c', 'c.id', '=', 's.city_id')
            ->join('source_publications as pub', function ($join) {
                $join->on('pub.source_id', '=', 'r.source_id')->on('pub.snapshot_id', '=', 'r.snapshot_id');
            })
            ->join('account_city_capabilities as grant', 'grant.city_id', '=', 'c.id')
            ->join('web_accounts as account', 'account.id', '=', 'grant.account_id')
            ->where('account.id', $account->id)->whereNull('account.disabled_at')
            ->where('grant.capability', 'projects.view')->whereNull('grant.revoked_at')
            ->where('r.source_id', $source)->where('r.table_id', $this->catalog->tableId('microproject'))
            ->whereIn('pub.publication_kind', app()->environment(['local', 'testing']) ? ['synthetic', 'validated'] : ['validated']);
    }

    private function selectedColumns(): array
    {
        return ['r.id as record_id', 'r.source_id', 'p.MP_ID', 'p.MP_Name', 'p.MP_DateOfRequest', 'p.ApplicationState', 'c.id as city_id', 'c.name_en as city_name_en', 'c.name_ar as city_name_ar', 'pub.publication_kind'];
    }

    public function search(WebAccount $account, array $selectedCities, string $search): LengthAwarePaginator
    {
        abort_unless($this->access->active($account), 403);
        $allowed = $this->access->cities($account)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selectedCities = array_values(array_unique(array_map('intval', $selectedCities)));
        if (array_diff($selectedCities, $allowed)) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $cityIds = $selectedCities === [] ? $allowed : $selectedCities;
        $sources = DB::table('sources')->whereIn('city_id', $cityIds)->whereIn('id', $this->catalog->sourceIds())->pluck('id');
        $union = null;
        foreach ($sources as $source) {
            $query = $this->query($account, (int) $source)->select($this->selectedColumns());
            if ($search !== '') {
                $literal = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
                $query->where(function (Builder $query) use ($literal, $search) {
                    $query->whereRaw("p.MP_Name LIKE ? ESCAPE '!'", [$literal]);
                    if (ctype_digit($search) && strlen($search) <= 10 && (int) $search <= 2147483647) {
                        $query->orWhere('p.MP_ID', (int) $search);
                    }
                });
            }
            $union = $union === null ? $query : $union->unionAll($query);
        }
        $filters = array_filter(['search' => $search, 'cities' => $selectedCities], fn ($value) => $value !== '' && $value !== []);
        if ($union === null) {
            return (new EmptyPaginator([], 0, 20, EmptyPaginator::resolveCurrentPage(), ['path' => EmptyPaginator::resolveCurrentPath()]))->appends($filters);
        }

        return DB::query()->fromSub($union, 'projects')->orderBy('MP_ID')->orderBy('city_id')->orderBy('record_id')
            ->paginate(20)->appends($filters);
    }

    public function find(WebAccount $account, int $record, ?int $expectedSource = null): object
    {
        $identity = DB::table('source_records as r')->join('sources as s', 's.id', '=', 'r.source_id')
            ->where('r.id', $record)->where('r.table_id', $this->catalog->tableId('microproject'))
            ->select(['r.source_id', 's.city_id'])->first();
        abort_if($identity === null || ($expectedSource !== null && (int) $identity->source_id !== $expectedSource), 404);
        if (! $this->access->check($account, (int) $identity->city_id)) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        abort_unless(in_array((int) $identity->source_id, $this->catalog->sourceIds(), true), 404);
        $project = $this->query($account, (int) $identity->source_id)->where('r.id', $record)
            ->select([...$this->selectedColumns(), 'p.MP_PlaceAddress', 'p.MP_Note'])
            ->selectRaw('CAST(p.MP_RequestedAmount AS CHAR) AS MP_RequestedAmount')->first();
        abort_if($project === null, 404);

        return $project;
    }
}
