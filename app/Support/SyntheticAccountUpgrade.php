<?php

namespace App\Support;

use App\Models\WebAccount;
use App\Services\AccountProvisioner;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit review of the known invented Phase 2 fixture, never general account matching. */
final class SyntheticAccountUpgrade
{
    public function upgrade(): WebAccount
    {
        $config = DB::connection()->getConfig();
        if (! app()->environment(['local', 'testing']) || $config['driver'] !== 'mysql'
            || ! in_array($config['database'], ['micro_projects_test', 'micro_projects_local'], true)
            || ! in_array($config['host'], ['127.0.0.1', 'localhost', '::1'], true) || ! empty($config['url']) || ! empty($config['unix_socket'])) {
            throw new RuntimeException('Synthetic upgrade requires a dedicated loopback local/testing database.');
        }

        return DB::transaction(function () {
            $snapshots = DB::table('snapshots')->lockForUpdate()->get();
            if ($snapshots->count() !== 2 || DB::table('web_principals')->count() !== 2) {
                throw new RuntimeException('Upgrade requires the exact two-city synthetic fixture.');
            }
            $principals = [];
            foreach ([1 => 'aleppo', 2 => 'damascus'] as $source => $name) {
                $snapshot = $snapshots->firstWhere('source_id', $source);
                if ($snapshot === null || $snapshot->manifest_locator !== 'synthetic:phase2:'.$name
                    || ! hash_equals(hash('sha256', 'synthetic-archive-'.$name, true), $snapshot->archive_sha256)
                    || ! hash_equals(hash('sha256', 'synthetic-data-'.$name, true), $snapshot->sql_sha256)) {
                    throw new RuntimeException('Unknown snapshot; no synthetic account review is authorized.');
                }
                $principal = DB::table('web_principals as p')->join('source_records as r', 'r.id', '=', 'p.source_record_id')
                    ->where('p.source_id', $source)->where('r.source_id', $source)->where('r.table_id', 117)
                    ->where('r.snapshot_id', $snapshot->id)->where('r.source_key', 'synthetic:phase2:'.$name.':user')->value('p.id');
                if ($principal === null) {
                    throw new RuntimeException('The reviewed synthetic principal is missing.');
                }
                $principals[] = (int) $principal;
            }
            // Repeat runs never reset account status, links, passwords or grants.
            if (DB::table('web_accounts')->exists()) {
                $account = WebAccount::where('username', 'demo.user')->first();
                $linked = $account ? DB::table('account_legacy_links')->where('account_id', $account->id)->orderBy('principal_id')->pluck('principal_id')->map(fn ($id) => (int) $id)->all() : [];
                $expected = $principals;
                sort($expected);
                if ($account === null || DB::table('web_accounts')->count() !== 1 || $linked !== $expected) {
                    throw new RuntimeException('Existing account data is outside the synthetic upgrade contract.');
                }

                return $account;
            }

            return app(AccountProvisioner::class)->provision('demo.user', $principals[0], $principals, [1 => ['projects.view'], 2 => ['projects.view']], 'synthetic:phase2:reviewed-unified-account');
        });
    }
}
