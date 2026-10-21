<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

final class ApplicationWorkflowTest extends TestCase
{
    private WebAccount $account;

    private ApplicationService $applications;

    private array $intake;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->applications = app(ApplicationService::class);
        $this->intake = [1 => ApplicationFixture::intake(1), 2 => ApplicationFixture::intake(2)];
    }

    public function test_creation_allocates_per_source_numbers_and_literal_search_preserves_exact_input(): void
    {
        $one = $this->create(1, ['c_MP_Name' => 'طلب %_ تجريبي', 'c_MP_Note' => '  exact note  ']);
        $two = $this->create(1);
        $other = $this->create(2);
        self::assertSame(1000, (int) DB::table('op_microproject')->where('id', $one)->value('c_MP_ID'));
        self::assertSame(1001, (int) DB::table('op_microproject')->where('id', $two)->value('c_MP_ID'));
        self::assertSame(1000, (int) DB::table('op_microproject')->where('id', $other)->value('c_MP_ID'));
        self::assertSame(1, $this->applications->search($this->account, [], '%_')->total());
        self::assertSame(0, $this->applications->search($this->account, [2], '%_')->total());
        self::assertSame(2, $this->applications->search($this->account, [], '1000')->total());
        self::assertSame('  exact note  ', $this->applications->find($this->account, $one)['application']->c_MP_Note);
        self::assertSame('zero', $this->applications->find($this->account, $one)['application']->c_ApplicationState);
        self::assertSame(0, DB::table('source_records')->count());
        self::assertSame(0, DB::table('operational_origins')->count());
    }

    public function test_revoked_grants_disable_writes_and_reads_without_consuming_numbers(): void
    {
        $id = $this->create();
        $version = $this->version($id);
        DB::table('account_city_capabilities')->where('city_id', 1)->where('capability', 'applications.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->create());
        $this->assertStatus(403, fn () => $this->applications->update($this->account, $id, ['c_MP_Name' => 'Denied'], $version));
        self::assertSame(1001, (int) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
        self::assertNotNull($this->applications->find($this->account, $id));
        DB::table('account_city_capabilities')->where('city_id', 1)->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->applications->find($this->account, $id));
        $this->assertStatus(403, fn () => $this->applications->search($this->account, [1], ''));
        DB::table('web_accounts')->where('id', $this->account->id)->update(['disabled_at' => now()]);
        $this->assertStatus(403, fn () => $this->applications->search($this->account, [], ''));
    }

    public function test_beneficiaries_partners_and_catalogs_cannot_cross_city_boundaries(): void
    {
        $this->assertStatus(422, fn () => $this->applications->create($this->account, 1, $this->fields(), $this->intake[2]['person'], $this->intake[1]['references']));
        self::assertSame(0, DB::table('op_microproject')->count());
        self::assertSame(1000, (int) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
        $id = $this->create();
        $this->assertStatus(422, fn () => $this->applications->linkPerson($this->account, $id, $this->intake[2]['person'], 'partner', [], $this->version($id)));
        $this->assertStatus(422, fn () => $this->applications->update($this->account, $id, [], $this->version($id), ['Career_ID' => $this->intake[2]['references']['Career_ID']]));
        $partner = ApplicationFixture::row('person', 1, ['c_P_FirstName' => 'Partner']);
        $this->applications->linkPerson($this->account, $id, $partner, 'partner', ['c_P_Goal' => 'Exact goal'], $this->version($id));
        self::assertCount(2, $this->applications->find($this->account, $id)['people']);
        self::assertSame('شريك', DB::table('op_person_microproject')->orderByDesc('id')->value('c_PersonType'));
        DB::table('account_city_capabilities')->where('capability', 'people.view')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->create());
    }

    public function test_stale_save_cannot_overwrite_newer_application_or_detail_changes(): void
    {
        $id = $this->create();
        $version = $this->version($id);
        $this->applications->update($this->account, $id, ['c_MP_Name' => 'Newer'], $version);
        $this->assertStatus(409, fn () => $this->applications->update($this->account, $id, ['c_MP_Name' => 'Stale'], $version));
        self::assertSame('Newer', $this->applications->find($this->account, $id)['application']->c_MP_Name);
        $version = $this->version($id);
        $this->applications->saveDetail($this->account, $id, 'rehab', ['c_PropertyArea' => '123'], $version);
        $this->assertStatus(409, fn () => $this->applications->update($this->account, $id, ['c_MP_Name' => 'Stale detail'], $version));
    }

    public function test_agriculture_answers_must_match_the_selected_question_and_city(): void
    {
        $id = $this->create();
        $question = ApplicationFixture::row('agquestion', 1, ['c_ID' => 1, 'c_Name' => 'Question one']);
        $other = ApplicationFixture::row('agquestion', 1, ['c_ID' => 2, 'c_Name' => 'Question two']);
        $answer = ApplicationFixture::row('aganswer', 1, ['c_ID' => 3, 'c_Name' => 'Answer'], ['Question_ID' => $question]);
        $this->assertStatus(422, fn () => $this->applications->saveDetail($this->account, $id, 'agriculture', [], $this->version($id), null, ['Question_ID' => $other, 'Answer_ID' => $answer]));
        self::assertSame(0, DB::table('op_microproject_ag_answer')->count());
        $this->applications->saveDetail($this->account, $id, 'agriculture', [], $this->version($id), null, ['Question_ID' => $question, 'Answer_ID' => $answer]);
        self::assertCount(1, $this->applications->find($this->account, $id)['agriculture']);
    }

    public function test_rehabilitation_answers_cannot_use_another_applications_parent(): void
    {
        $id = $this->create();
        $other = $this->create();
        $this->applications->saveDetail($this->account, $other, 'rehab', ['c_PropertyArea' => '100'], $this->version($other));
        $parent = (int) DB::table('op_microproject_rehab')->value('id');
        $question = ApplicationFixture::row('rehabquestion', 1, ['c_ID' => 1, 'c_Name' => 'Question']);
        $answer = ApplicationFixture::row('rehabanswer', 1, ['c_ID' => 2, 'c_Name' => 'Answer'], ['Question_ID' => $question]);
        $this->assertStatus(404, fn () => $this->applications->saveDetail($this->account, $id, 'rehab_answers', ['c_Number' => '1'], $this->version($id), null, ['Question_ID' => $question, 'Answer_ID' => $answer], $parent));
        self::assertSame(0, DB::table('op_microproject_rehab_answer')->count());
        $this->applications->saveDetail($this->account, $other, 'rehab_answers', ['c_Number' => '1', 'c_Note' => 'Exact note'], $this->version($other), null, ['Question_ID' => $question, 'Answer_ID' => $answer], $parent);
        self::assertCount(1, $this->applications->find($this->account, $other)['rehab_answers']);
    }

    public function test_imported_identity_and_invalid_historical_values_remain_unchanged_by_unrelated_edits(): void
    {
        $id = $this->create();
        $row = DB::table('op_microproject')->where('id', $id)->first();
        DB::table('operational_entities')->where('id', $id)->update(['identity_kind' => 'legacy_primary']);
        DB::table('source_records')->insert(['id' => 100, 'source_id' => 1, 'table_id' => $row->source_table_id, 'snapshot_id' => 10]);
        DB::table('operational_origins')->insert(['source_record_id' => 100, 'source_id' => 1, 'source_table_id' => $row->source_table_id, 'snapshot_id' => 10, 'entity_id' => $id]);
        $this->assertStatus(404, fn () => $this->applications->find($this->account, $id));
        DB::table('source_publications')->insert(['source_id' => 1, 'snapshot_id' => 10, 'publication_kind' => 'synthetic']);
        DB::table('op_microproject')->where('id', $id)->update(['c_MP_DateOfRequest' => '0000-00-00', 'c_MP_Note' => '  preserved  ']);
        $before = (array) DB::table('source_records')->first();
        $this->applications->update($this->account, $id, ['c_MP_Name' => 'New title'], $this->version($id));
        self::assertSame('0000-00-00', DB::table('op_microproject')->where('id', $id)->value('c_MP_DateOfRequest'));
        self::assertSame('  preserved  ', DB::table('op_microproject')->where('id', $id)->value('c_MP_Note'));
        self::assertSame($row->c_MP_ID, DB::table('op_microproject')->where('id', $id)->value('c_MP_ID'));
        self::assertSame($before, (array) DB::table('source_records')->first());
        self::assertSame(1, DB::table('operational_origins')->count());
    }

    public function test_incomplete_full_intake_is_explicitly_deferred_without_writing_or_allocating(): void
    {
        $this->assertStatus(422, fn () => $this->applications->create($this->account, 1, $this->fields(), $this->intake[1]['person'], $this->intake[1]['references'], 'full'));
        self::assertSame(0, DB::table('op_microproject')->count());
        self::assertSame(1000, (int) DB::table('project_number_sequences')->where('source_id', 1)->value('next_number'));
        $id = $this->create();
        $this->assertStatus(422, fn () => $this->applications->convertToFull($this->account, $id, $this->version($id)));
        self::assertSame('zero', DB::table('op_microproject')->where('id', $id)->value('c_ApplicationState'));
    }

    public function test_dependent_catalog_tampering_and_nonfinite_values_are_rejected(): void
    {
        $otherCategory = ApplicationFixture::row('category', 1, ['c_ID' => 30]);
        $otherSubcategory = ApplicationFixture::row('subcategory', 1, ['c_ID' => 31], ['Category_ID' => $otherCategory]);
        $refs = array_replace($this->intake[1]['references'], ['SubCategory_ID' => $otherSubcategory]);
        $this->assertStatus(422, fn () => $this->applications->create($this->account, 1, $this->fields(), $this->intake[1]['person'], $refs));
        $id = $this->create();
        $this->assertStatus(422, fn () => $this->applications->update($this->account, $id, ['c_MP_RequestedAmount' => '1e999'], $this->version($id)));
        $this->applications->update($this->account, $id, ['c_MP_RequestedAmount' => '-12.123456789'], $this->version($id));
        self::assertSame('-12.123456789', DB::table('op_microproject')->where('id', $id)->value('c_MP_RequestedAmount'));
        $otherType = ApplicationFixture::row('microprojecttype', 1, ['c_ID' => 40]);
        $this->assertStatus(422, fn () => $this->applications->update($this->account, $id, [], $this->version($id), ['MP_Type_ID' => $otherType]));
    }

    public function test_rehab_limits_use_raw_question_id_and_explicit_header_identity(): void
    {
        $id = $this->create();
        foreach ([1, 2] as $unused) {
            $this->applications->saveDetail($this->account, $id, 'rehab', ['c_PropertyArea' => 'مساحة نصية'], $this->version($id));
        }
        $headers = DB::table('op_microproject_rehab')->pluck('id');
        $question = ApplicationFixture::row('rehabquestion', 1, ['c_ID' => 9, 'c_Name' => 'Synthetic question']);
        $answer = ApplicationFixture::row('rehabanswer', 1, ['c_ID' => 1], ['Question_ID' => $question]);
        $refs = ['Question_ID' => $question, 'Answer_ID' => $answer];
        $this->applications->saveDetail($this->account, $id, 'rehab_answers', ['c_Result' => 'نص وليس مجموعاً', 'c_Price' => '1.123456789'], $this->version($id), null, $refs, (int) $headers[0]);
        $this->assertStatus(422, fn () => $this->applications->saveDetail($this->account, $id, 'rehab_answers', [], $this->version($id), null, $refs, (int) $headers[0]));
        $this->applications->saveDetail($this->account, $id, 'rehab_answers', [], $this->version($id), null, $refs, (int) $headers[1]);
        self::assertSame(2, DB::table('op_microproject_rehab_answer')->count());
        $row = DB::table('op_microproject_rehab_answer')->orderBy('id')->first();
        $this->applications->saveDetail($this->account, $id, 'rehab_answers', ['c_Note' => 'Edited note'], $this->version($id), (int) $row->id, [], (int) $headers[0]);
        self::assertSame('1.123456789', DB::table('op_microproject_rehab_answer')->where('id', $row->id)->value('c_Price'));
        self::assertSame('نص وليس مجموعاً', DB::table('op_microproject_rehab_answer')->where('id', $row->id)->value('c_Result'));
    }

    private function fields(array $overrides = []): array
    {
        return array_replace(['c_MP_Name' => 'Synthetic application', 'c_MP_DateOfRequest' => '2026-10-19'], $overrides);
    }

    private function create(int $city = 1, array $fields = []): int
    {
        return $this->applications->create($this->account, $city, $this->fields($fields), $this->intake[$city]['person'], $this->intake[$city]['references']);
    }

    private function version(int $id): string
    {
        return $this->applications->find($this->account, $id)['version'];
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
