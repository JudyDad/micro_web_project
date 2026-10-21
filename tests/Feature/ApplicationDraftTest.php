<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Drafts\ApplicationDraftStore;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

final class ApplicationDraftTest extends TestCase
{
    private WebAccount $account;

    private ApplicationDraftStore $drafts;

    protected function setUp(): void
    {
        parent::setUp();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
        $this->drafts = app(ApplicationDraftStore::class);
    }

    public function test_encrypted_recovery_preserves_original_edit_version_without_business_writes(): void
    {
        $payload = ['fields' => ['c_MP_Name' => 'طلب تجريبي', 'c_MP_Note' => ' exact '], 'version' => str_repeat('a', 64)];
        $draft = $this->drafts->save($this->account, 1, 'application', null, $payload);
        $stored = Storage::disk('local')->get($this->path($draft));
        self::assertStringNotContainsString(' exact ', $stored);
        self::assertSame($payload, $this->drafts->load($this->account, 1, $draft['id'])['payload']);
        self::assertArrayNotHasKey('payload', $this->drafts->listing($this->account, 1)[0]);
        self::assertSame(0, DB::table('op_microproject')->count());
        self::assertSame(1000, (int) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
        self::assertStringNotContainsString(' exact ', json_encode(DB::table('account_audit_events')->get()));
        self::assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_stale_updates_and_discards_cannot_destroy_newer_recovery(): void
    {
        $draft = $this->save();
        $new = $this->drafts->save($this->account, 1, 'application', null, ['fields' => ['c_MP_Name' => 'New']], $draft['id'], $draft['version']);
        self::assertNotSame($draft['version'], $new['version']);
        $this->assertStatus(409, fn () => $this->drafts->save($this->account, 1, 'application', null, [], $draft['id'], $draft['version']));
        $this->assertStatus(409, fn () => $this->drafts->discard($this->account, 1, $draft['id'], $draft['version']));
        self::assertSame('New', $this->drafts->load($this->account, 1, $draft['id'])['payload']['fields']['c_MP_Name']);
        $this->drafts->discard($this->account, 1, $new['id'], $new['version']);
        self::assertSame([], $this->drafts->listing($this->account, 1));
    }

    public function test_account_city_path_and_revoked_access_are_checked(): void
    {
        $draft = $this->save();
        $other = ApplicationFixture::account();
        $this->assertStatus(404, fn () => $this->drafts->load($other, 1, $draft['id']));
        $this->assertStatus(404, fn () => $this->drafts->load($this->account, 2, $draft['id']));
        $this->assertStatus(404, fn () => $this->drafts->load($this->account, 1, '../'.$draft['id']));
        DB::table('account_city_capabilities')->where('account_id', $this->account->id)->where('capability', 'applications.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->drafts->load($this->account, 1, $draft['id']));
        $this->assertStatus(403, fn () => $this->drafts->listing($this->account, 1));
        $this->assertStatus(403, fn () => $this->save());
        $this->assertStatus(403, fn () => $this->drafts->discard($this->account, 1, $draft['id'], $draft['version']));
    }

    public function test_expiration_removes_recovery_and_releases_abandoned_quota(): void
    {
        $draft = $this->save();
        $this->travel(8)->days();
        $this->assertStatus(404, fn () => $this->drafts->load($this->account, 1, $draft['id']));
        Storage::disk('local')->assertMissing($this->path($draft));
        $this->travelBack();
        for ($i = 0; $i < 100; $i++) {
            $path = 'application-drafts/'.$this->account->id.'/1/'.sprintf('%032x', $i).'.enc';
            Storage::disk('local')->put($path, 'old undecryptable data');
            touch(Storage::disk('local')->path($path), now()->subDays(8)->timestamp);
        }
        $this->save();
        self::assertCount(1, Storage::disk('local')->allFiles());
    }

    public function test_corruption_invalid_payload_and_public_storage_fail_closed(): void
    {
        $draft = $this->save();
        Storage::disk('local')->put($this->path($draft), 'corrupt ciphertext');
        $this->assertStatus(404, fn () => $this->drafts->load($this->account, 1, $draft['id']));
        self::assertSame([], $this->drafts->listing($this->account, 1));
        $this->assertStatus(422, fn () => $this->drafts->save($this->account, 1, 'application', null, ['password' => 'forbidden']));
        $this->assertStatus(422, fn () => $this->drafts->save($this->account, 1, 'application', null, ['fields' => ['c_MP_Name' => ['nested']]]));
        $this->assertStatus(422, fn () => $this->drafts->save($this->account, 1, 'application', null, ['fields' => ['c_MP_Name' => str_repeat('x', 40001)]]));
        config(['filesystems.disks.local.serve' => true]);
        $this->assertStatus(503, fn () => $this->save());
    }

    public function test_audit_failure_restores_previous_ciphertext_and_failed_new_save_leaves_no_file(): void
    {
        $draft = $this->save();
        $original = Storage::disk('local')->get($this->path($draft));
        Schema::drop('account_audit_events');
        foreach ([
            fn () => $this->save(),
            fn () => $this->drafts->save($this->account, 1, 'application', null, [], $draft['id'], $draft['version']),
            fn () => $this->drafts->discard($this->account, 1, $draft['id'], $draft['version']),
        ] as $operation) {
            try {
                $operation();
                self::fail('Audit failure must fail the operation.');
            } catch (QueryException) {
                self::assertSame($original, Storage::disk('local')->get($this->path($draft)));
                self::assertCount(1, Storage::disk('local')->allFiles());
            }
        }
    }

    public function test_draft_update_cannot_reassign_its_child_record_identity(): void
    {
        $draft = $this->drafts->save($this->account, 1, 'application', null, ['detail_id' => '12', 'link_id' => '34']);
        $same = $this->drafts->save($this->account, 1, 'application', null, ['detail_id' => 12, 'link_id' => 34], $draft['id'], $draft['version']);
        $this->assertStatus(409, fn () => $this->drafts->save($this->account, 1, 'application', null, ['detail_id' => 13, 'link_id' => 34], $same['id'], $same['version']));
        $this->assertStatus(409, fn () => $this->drafts->save($this->account, 1, 'application', null, ['detail_id' => 12, 'link_id' => 35], $same['id'], $same['version']));
        self::assertSame(12, $this->drafts->load($this->account, 1, $same['id'])['payload']['detail_id']);
    }

    private function save(): array
    {
        return $this->drafts->save($this->account, 1, 'application', null, ['fields' => ['c_MP_Name' => 'Synthetic draft']]);
    }

    private function path(array $draft): string
    {
        return 'application-drafts/'.$this->account->id.'/1/'.$draft['id'].'.enc';
    }

    private function assertStatus(int $status, callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected status '.$status);
        } catch (HttpException $exception) {
            self::assertSame($status, $exception->getStatusCode());
        } catch (ValidationException $exception) {
            self::assertSame($status, $exception->status);
        }
    }
}
