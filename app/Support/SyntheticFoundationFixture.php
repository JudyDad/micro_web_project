<?php

namespace App\Support;

use App\Services\AccountProvisioner;
use App\Services\AuditTrail;
use Database\Schema\PreservationManifest;
use Illuminate\Support\Facades\DB;
use RuntimeException;

require_once __DIR__.'/../../database/schema/PreservationManifest.php';

/** Invented two-city fixture; never an importer or production account provisioner. */
final class SyntheticFoundationFixture
{
    public const PASSWORD = 'Synthetic-Desktop-2026!';

    public function create(): array
    {
        $config = DB::connection()->getConfig();
        if (! app()->environment(['local', 'testing']) || $config['driver'] !== 'mysql'
            || ! in_array($config['database'], ['micro_projects_test', 'micro_projects_local'], true)
            || ! in_array($config['host'], ['127.0.0.1', 'localhost', '::1'], true) || ! empty($config['url']) || ! empty($config['unix_socket'])) {
            throw new RuntimeException('Synthetic fixture requires a dedicated loopback local/testing database.');
        }
        if (DB::table('snapshots')->exists() || DB::table('web_principals')->exists()) {
            throw new RuntimeException('Fixture creation requires empty preservation and principal data; existing records are never changed.');
        }

        return DB::transaction(function (): array {
            $result = ['sources' => ['aleppo' => 1, 'damascus' => 2], 'principals' => [], 'projects' => []];
            $manifest = PreservationManifest::load();
            $tableIds = PreservationManifest::tableIds($manifest);
            $now = now('UTC')->format('Y-m-d H:i:s.u');
            foreach ($result['sources'] as $name => $source) {
                $snapshot = DB::table('snapshots')->insertGetId([
                    'source_id' => $source, 'archive_sha256' => hash('sha256', 'synthetic-archive-'.$name, true),
                    'sql_sha256' => hash('sha256', 'synthetic-data-'.$name, true), 'manifest_locator' => 'synthetic:phase2:'.$name, 'captured_at' => null,
                ]);
                $run = DB::table('migration_runs')->insertGetId(['snapshot_id' => $snapshot, 'tool_version' => 'phase2-synthetic-v1', 'started_at' => $now, 'completed_at' => $now, 'status' => 'synthetic']);
                foreach (['user', 'microproject'] as $table) {
                    $key = 'synthetic:phase2:'.$name.':'.$table;
                    $record = DB::table('source_records')->insertGetId([
                        'source_id' => $source, 'table_id' => $tableIds[$table], 'snapshot_id' => $snapshot,
                        'source_key' => $key, 'key_sha256' => hash('sha256', $key, true), 'key_kind' => 'primary',
                        'import_run_id' => $run, 'resolution_status' => 'synthetic',
                    ]);
                    $definition = collect($manifest['tables'])->first(fn ($entry) => $entry['city'] === ucfirst($name) && $entry['source_table'] === $table);
                    $row = ['record_id' => $record, 'origin_source_id' => $source, 'origin_table_id' => $tableIds[$table]];
                    foreach ($definition['columns'] as $column) {
                        if (str_contains($column['target_definition'], 'NOT NULL') && ! str_contains($column['target_definition'], 'DEFAULT')) {
                            $row[$column['target_column']] = preg_match('/^(?:tinyint|smallint|mediumint|int|bigint|float|double|decimal)/', $column['target_definition']) ? 0 : '';
                        }
                    }
                    if ($table === 'user') {
                        $row = array_replace($row, ['UserID' => 1, 'UserName' => 'demo.user', 'Password' => 'AAECAwQFBgcICQoLDA0OD6rirHkgJUrCARvhwiO9AlEz7NUBCDqkiw29w+5zjOOk', 'Email' => '', 'UserRoleID' => 2, 'IsVisitor' => 0, 'IsME' => 0, 'IsCommunication' => 0]);
                    } else {
                        $row = array_replace($row, ['MP_ID' => 1042, 'MP_Name' => 'Synthetic '.ucfirst($name).' project', 'MP_RequestedAmount' => 12.5, 'MP_DateOfRequest' => '2026-10-17', 'MP_PlaceAddress' => 'Synthetic address', 'MP_Note' => 'Invented demonstration record.', 'ApplicationState' => 'synthetic']);
                    }
                    DB::table($definition['target_table'])->insert($row);
                    if ($table === 'user') {
                        $principal = DB::table('web_principals')->insertGetId(['source_id' => $source, 'source_table_id' => 117, 'source_record_id' => $record, 'password_hash' => null, 'disabled_at' => null, 'created_at' => $now, 'updated_at' => $now]);
                        $result['principals'][$name] = $principal;
                        $grant = DB::table('principal_capabilities')->insertGetId(['source_id' => $source, 'principal_id' => $principal, 'capability' => 'projects.view', 'decision_locator' => 'synthetic:phase2:projects.view', 'granted_at' => $now, 'revoked_at' => null]);
                        app(AuditTrail::class)->record($source, $principal, 'capability.granted', 'succeeded', 'capability_grant', $grant, 'synthetic:phase2:projects.view');
                    } else {
                        $result['projects'][$name] = $record;
                    }
                }
                DB::table('source_publications')->insert(['source_id' => $source, 'snapshot_id' => $snapshot, 'publication_kind' => 'synthetic', 'evidence_locator' => 'synthetic:phase2:'.$name, 'published_at' => $now]);
                app(AuditTrail::class)->record($source, null, 'publication.changed', 'succeeded', 'publication', $source, 'synthetic:phase2:'.$name);
                app(AuditTrail::class)->record($source, null, 'fixture.created', 'succeeded', decision: 'synthetic:phase2:v1');
            }

            $account = app(AccountProvisioner::class)->provision('demo.user', $result['principals']['aleppo'], array_values($result['principals']), [1 => ['projects.view'], 2 => ['projects.view']], 'synthetic:phase2:reviewed-unified-account');
            $result['account'] = $account->id;

            return $result;
        });
    }
}
