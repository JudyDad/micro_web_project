<?php

namespace Tests\Integration;

use App\Services\AuditTrail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Uses a pre-provisioned restricted account, never grants privileges itself. */
class RuntimeAuditPrivilegesTest extends TestCase
{
    public function test_runtime_can_append_audit_but_cannot_rewrite_evidence_or_business_rows(): void
    {
        if (getenv('RUN_RUNTIME_AUDIT_TESTS') !== '1') {
            $this->markTestSkipped('Provision a dedicated restricted runtime account for this privilege check.');
        }
        $host = getenv('MYSQL_RUNTIME_HOST') ?: '127.0.0.1';
        $database = getenv('MYSQL_RUNTIME_DATABASE');
        self::assertContains($host, ['127.0.0.1', 'localhost', '::1']);
        self::assertContains($database, ['micro_projects_test', 'micro_projects_local']);
        config([
            'database.default' => 'mysql', 'database.connections.mysql.url' => null,
            'database.connections.mysql.host' => $host, 'database.connections.mysql.port' => getenv('MYSQL_RUNTIME_PORT') ?: '33072',
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => getenv('MYSQL_RUNTIME_USERNAME'),
            'database.connections.mysql.password' => getenv('MYSQL_RUNTIME_PASSWORD'),
            'database.connections.mysql.unix_socket' => '',
        ]);
        DB::purge('mysql');
        self::assertSame($database, DB::selectOne('SELECT DATABASE() AS db')->db);
        self::assertSame(2, DB::table('source_publications')->where('publication_kind', 'synthetic')->count());
        DB::beginTransaction();
        try {
            $before = DB::table('audit_events')->count();
            app(AuditTrail::class)->record(1, null, 'access.denied', 'denied');
            self::assertSame($before + 1, DB::table('audit_events')->count());
            foreach ([
                "UPDATE audit_events SET outcome = 'failed' WHERE id = 0",
                'DELETE FROM audit_events WHERE id = 0',
                "UPDATE legacy_a_microproject SET MP_Name = 'forbidden' WHERE record_id = 0",
                "UPDATE source_publications SET publication_kind = 'validated' WHERE source_id = 0",
                "UPDATE principal_capabilities SET capability = 'forbidden' WHERE id = 0",
                'UPDATE web_principals SET disabled_at = CURRENT_TIMESTAMP WHERE id = 0',
            ] as $sql) {
                try {
                    DB::statement($sql);
                    self::fail('The restricted runtime unexpectedly has write access: '.$sql);
                } catch (QueryException $exception) {
                    self::assertContains((int) $exception->errorInfo[1], [1142, 1143]);
                }
            }
        } finally {
            DB::rollBack();
        }
    }
}
