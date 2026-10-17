<?php

namespace App\Services;

use App\Models\WebPrincipal;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class SourceIdentity
{
    public function __construct(private SourceCatalog $catalog) {}

    public function publishedUsers(int $sourceId): Builder
    {
        $query = DB::table($this->catalog->table($sourceId, 'user').' as u')
            ->join('source_records as r', function ($join) {
                $join->on('r.id', '=', 'u.record_id')->on('r.source_id', '=', 'u.origin_source_id')->on('r.table_id', '=', 'u.origin_table_id');
            })
            ->join('source_publications as p', function ($join) {
                $join->on('p.source_id', '=', 'r.source_id')->on('p.snapshot_id', '=', 'r.snapshot_id');
            })
            ->where('r.source_id', $sourceId)->where('r.table_id', $this->catalog->tableId('user'))
            ->whereIn('p.publication_kind', app()->environment(['local', 'testing']) ? ['synthetic', 'validated'] : ['validated']);

        return $query;
    }

    public function activeUser(WebPrincipal $principal): ?object
    {
        // Reload state: an authenticated instance may predate an administrator's disable.
        $current = WebPrincipal::find($principal->getKey());
        if ($current === null || $current->disabled_at !== null || $current->source_table_id !== 117
            || $current->source_id !== $principal->source_id || $current->source_record_id !== $principal->source_record_id
            || ! in_array($current->source_id, $this->catalog->sourceIds(), true)) {
            return null;
        }

        return $this->publishedUsers($current->source_id)->where('r.id', $current->source_record_id)
            ->where('u.UserRoleID', '<>', 6)->select(['u.record_id', 'u.UserID', 'u.UserRoleID', 'r.snapshot_id'])->first();
    }
}
