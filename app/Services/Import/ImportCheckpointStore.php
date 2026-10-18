<?php

declare(strict_types=1);

namespace App\Services\Import;

use Illuminate\Database\ConnectionInterface;
use RuntimeException;

final class ImportCheckpointStore
{
    public function __construct(private readonly ConnectionInterface $db) {}

    public function acquire(int $run, int $snapshot, string $mappingHash, int $table, string $phase, string $owner): array
    {
        return $this->db->transaction(function () use ($run, $snapshot, $mappingHash, $table, $phase, $owner): array {
            $key = ['snapshot_id' => $snapshot, 'mapping_sha256' => $mappingHash, 'source_table_id' => $table, 'phase' => $phase];
            $this->db->table('migration_checkpoints')->insertOrIgnore($key + ['run_id' => $run, 'updated_at' => $this->now()]);
            $row = $this->db->table('migration_checkpoints')->where($key)->lockForUpdate()->first();
            if (! $row || (int) $row->run_id !== $run) {
                throw new RuntimeException('Checkpoint lane belongs to a different frozen run.');
            }
            if ($row->lease_owner !== null && $row->lease_owner !== $owner && $row->lease_expires_at > $this->now()) {
                throw new RuntimeException('Import checkpoint lane is leased by another writer.');
            }
            $token = (int) $row->fencing_token + 1;
            $this->db->table('migration_checkpoints')->where('id', $row->id)->update([
                'lease_owner' => $owner, 'fencing_token' => $token,
                'lease_expires_at' => now('UTC')->addMinutes(5)->format('Y-m-d H:i:s.u'), 'updated_at' => $this->now(),
            ]);

            return ['id' => (int) $row->id, 'owner' => $owner, 'token' => $token, 'count' => (int) $row->committed_count,
                'batch' => (int) $row->batch_ordinal, 'cursor' => $row->cursor_bytes, 'cursor_hash' => $row->cursor_sha256,
                'batch_hash' => $row->batch_sha256, 'batch_count' => (int) $row->batch_count, 'status' => $row->status];
        });
    }

    /** Must be called inside the row/map/checkpoint transaction. */
    public function lock(array $lease): object
    {
        $row = $this->db->table('migration_checkpoints')->where('id', $lease['id'])->lockForUpdate()->first();
        if (! $row || $row->lease_owner !== $lease['owner'] || (int) $row->fencing_token !== $lease['token']
            || $row->lease_expires_at <= $this->now()) {
            throw new RuntimeException('Import lease expired or writer was fenced.');
        }
        if ((int) $row->committed_count !== $lease['count'] || (int) $row->batch_ordinal !== $lease['batch']) {
            throw new RuntimeException('Checkpoint advanced unexpectedly.');
        }

        return $row;
    }

    public function advance(array &$lease, int $count, string $cursor, string $digest, bool $complete): void
    {
        $this->lock($lease);
        $this->db->table('migration_checkpoints')->where('id', $lease['id'])->update([
            'committed_count' => $lease['count'] + $count, 'cursor_bytes' => $cursor,
            'cursor_sha256' => hash('sha256', $cursor, true), 'batch_sha256' => $digest,
            'batch_count' => $count, 'batch_ordinal' => $lease['batch'] + 1,
            'status' => $complete ? 'complete' : 'running', 'updated_at' => $this->now(),
            'lease_expires_at' => now('UTC')->addMinutes(5)->format('Y-m-d H:i:s.u'),
        ]);
        $lease['count'] += $count;
        $lease['batch']++;
    }

    public function release(array $lease): void
    {
        $this->db->table('migration_checkpoints')->where('id', $lease['id'])->where('lease_owner', $lease['owner'])
            ->where('fencing_token', $lease['token'])->update(['lease_owner' => null, 'lease_expires_at' => null]);
    }

    public function renew(array $lease): void
    {
        $this->db->transaction(function () use ($lease): void {
            $this->lock($lease);
            $this->db->table('migration_checkpoints')->where('id', $lease['id'])->update([
                'lease_expires_at' => now('UTC')->addMinutes(5)->format('Y-m-d H:i:s.u'), 'updated_at' => $this->now(),
            ]);
        });
    }

    private function now(): string
    {
        return now('UTC')->format('Y-m-d H:i:s.u');
    }
}
