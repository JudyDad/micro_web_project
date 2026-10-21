<?php

declare(strict_types=1);

namespace App\Services\Operational;

use App\Services\Sharing\SharedPayloadProjection;
use Database\Schema\OperationalManifest;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../../../database/schema/OperationalManifest.php';

/** Source-qualified access to approved operational tables: published reads, web-identity inserts and relationship lookup. */
final class OperationalTables
{
    public function table(string $sourceTable): array
    {
        return OperationalManifest::table($sourceTable);
    }

    public function tableId(string $sourceTable): int
    {
        return (int) preg_replace('/\D/', '', $this->table($sourceTable)['checks'][0]);
    }

    public function relationships(string $sourceTable): array
    {
        $name = $this->table($sourceTable)['name'];

        return array_values(array_filter(OperationalManifest::load()['relationships'], fn ($r) => $r['target_table'] === $name));
    }

    public function relation(string $sourceTable, string $key): array
    {
        foreach ($this->relationships($sourceTable) as $r) {
            if ($r['child_columns'] === [$key]) {
                return $r;
            }
        }
        abort(422, 'Unsupported reference contract.');
    }

    public function query(string $sourceTable): Builder
    {
        $table = $this->table($sourceTable);

        return DB::table($table['name'].' as p')
            ->join('operational_entities as e', fn ($j) => $j->on('e.id', '=', 'p.id')->on('e.city_id', '=', 'p.city_id')->on('e.source_id', '=', 'p.source_id')->on('e.source_table_id', '=', 'p.source_table_id'))
            ->join('sources as s', fn ($j) => $j->on('s.id', '=', 'p.source_id')->on('s.city_id', '=', 'p.city_id'))
            ->join('cities as c', 'c.id', '=', 'p.city_id')->where('p.source_table_id', $this->tableId($sourceTable))
            ->where(function (Builder $q) {
                $q->where('e.identity_kind', 'web')->orWhere(function (Builder $legacy) {
                    $legacy->whereIn('e.identity_kind', ['legacy_primary', 'legacy_ordinal'])->whereExists(function (Builder $origin) {
                        $origin->selectRaw('1')->from('operational_origins as o')
                            ->join('source_records as r', fn ($j) => $j->on('r.id', '=', 'o.source_record_id')->on('r.source_id', '=', 'o.source_id')->on('r.table_id', '=', 'o.source_table_id')->on('r.snapshot_id', '=', 'o.snapshot_id'))
                            ->join('source_publications as pub', fn ($j) => $j->on('pub.source_id', '=', 'o.source_id')->on('pub.snapshot_id', '=', 'o.snapshot_id'))
                            ->whereColumn('o.entity_id', 'p.id')->whereColumn('o.source_id', 'p.source_id')->whereColumn('o.source_table_id', 'p.source_table_id')
                            ->whereIn('pub.publication_kind', app()->environment(['local', 'testing']) ? ['synthetic', 'validated'] : ['validated']);
                    });
                });
            })->select(['p.*', 'e.identity_kind', 'c.name_en as city_name_en', 'c.name_ar as city_name_ar']);
    }

    /** Catalog rows for one city and source, with shared whole-table aliases projected to their values. */
    public function catalog(string $sourceTable, int $city, int $source): Collection
    {
        return $this->query($sourceTable)->where('p.city_id', $city)->where('p.source_id', $source)->orderBy('p.id')->get()
            ->map(fn ($row) => $this->project($sourceTable, $row));
    }

    public function find(string $sourceTable, int $id, int $city, int $source): ?object
    {
        $row = $this->query($sourceTable)->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $id)->first();

        return $row === null ? null : $this->project($sourceTable, $row);
    }

    public function project(string $sourceTable, object $row): object
    {
        if (isset($row->shared_snapshot_id)) {
            return (new SharedPayloadProjection(DB::connection()))->project($sourceTable, $row, (int) $row->source_id, (int) $row->shared_snapshot_id);
        }

        return $row;
    }

    public function source(int $city): int
    {
        $sources = DB::table('sources')->where('city_id', $city)->pluck('id');
        abort_unless($sources->count() === 1, 409, 'An unambiguous city source is required.');

        return (int) $sources->first();
    }

    public function insert(string $sourceTable, int $city, int $source, array $payload): int
    {
        $table = $this->table($sourceTable);
        $scope = ['city_id' => $city, 'source_id' => $source, 'source_table_id' => $this->tableId($sourceTable)];
        $id = DB::table('operational_entities')->insertGetId($scope + ['identity_kind' => 'web', 'first_key_sha256' => null, 'first_key_bytes' => null, 'created_at' => now('UTC')]);
        foreach ($this->relationships($sourceTable) as $r) {
            $payload += [$r['resolved_column'] => null, $r['state_column'] => 'null'];
        }
        DB::table($table['name'])->insert($scope + ['id' => $id] + $payload);

        return (int) $id;
    }

    /** Payload linking a resolved relationship. */
    public function link(string $sourceTable, string $key, ?int $id): array
    {
        $r = $this->relation($sourceTable, $key);

        return [$r['resolved_column'] => $id, $r['state_column'] => $id === null ? 'null' : 'resolved'];
    }
}
