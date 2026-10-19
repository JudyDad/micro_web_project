<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\People\PeopleService;
use Database\Schema\OperationalManifest;
use Database\Schema\PreservationManifest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

require_once __DIR__.'/../../database/schema/OperationalManifest.php';

/** Synthetic workflow checks only: SQLite cannot certify the approved MySQL DDL or locking. */
final class PeopleWorkflowTest extends TestCase
{
    private WebAccount $account;

    private PeopleService $people;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->schema();
        DB::table('web_accounts')->insert(['id' => 1, 'username' => 'synthetic.people', 'disabled_at' => null]);
        foreach ([1 => 'Aleppo', 2 => 'Damascus'] as $id => $name) {
            DB::table('cities')->insert(['id' => $id, 'name_en' => $name, 'name_ar' => 'مدينة تجريبية']);
            DB::table('sources')->insert(['id' => $id, 'city_id' => $id, 'name' => $name]);
            foreach (['people.view', 'people.manage'] as $capability) {
                DB::table('account_city_capabilities')->insert(['account_id' => 1, 'city_id' => $id, 'capability' => $capability]);
            }
        }
        foreach (PreservationManifest::tableIds(PreservationManifest::load()) as $name => $id) {
            DB::table('source_tables')->insert(['id' => $id, 'name' => $name]);
        }
        $this->account = WebAccount::findOrFail(1);
        $this->people = app(PeopleService::class);
    }

    public function test_people_are_separate_by_city_and_literal_search_and_updates_preserve_values(): void
    {
        $first = $this->create(1, ['c_P_FirstName' => 'اختبار %_', 'c_P_NationalNumber' => '000123', 'c_P_Mobile' => ' 00963 00 ']);
        $second = $this->create(2, ['c_P_FirstName' => 'Second']);
        self::assertNotSame($first, $second);
        self::assertSame(2, $this->people->search($this->account, [], '')->total());
        self::assertSame(1, $this->people->search($this->account, [1], '%_')->total());
        self::assertSame(0, $this->people->search($this->account, [2], '%_')->total());
        $before = $this->people->find($this->account, $first);
        $this->people->update($this->account, $first, ['c_P_LastName' => 'Changed'], $before['version']);
        $after = $this->people->find($this->account, $first);
        self::assertSame('Changed', $after['person']->c_P_LastName);
        self::assertSame('000123', $after['person']->c_P_NationalNumber);
        self::assertSame(' 00963 00 ', $after['person']->c_P_Mobile);
        $this->assertStatus(409, fn () => $this->people->update($this->account, $first, ['c_P_LastName' => 'Stale'], $before['version']));
        self::assertSame('Changed', DB::table('op_person')->where('id', $first)->value('c_P_LastName'));
    }

    public function test_duplicate_number_requires_confirmation_and_never_merges_people(): void
    {
        $first = $this->create(1, ['c_P_NationalNumber' => '00042']);
        try {
            $this->create(1, ['c_P_NationalNumber' => '00042']);
            self::fail('An exact duplicate requires an explicit decision.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('duplicate', $exception->errors());
        }
        self::assertSame(1, DB::table('op_person')->count());
        $second = $this->people->create($this->account, 1, $this->fields(['c_P_NationalNumber' => '00042']), true);
        self::assertNotSame($first, $second);
        self::assertSame(2, DB::table('op_person')->where('c_P_NationalNumber', '00042')->count());
    }

    public function test_revoked_permissions_and_disabled_accounts_fail_closed(): void
    {
        $id = $this->create();
        $version = $this->people->find($this->account, $id)['version'];
        DB::table('account_city_capabilities')->where('city_id', 1)->where('capability', 'people.manage')->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->people->update($this->account, $id, ['c_P_FirstName' => 'Denied'], $version));
        self::assertNotNull($this->people->find($this->account, $id));
        DB::table('account_city_capabilities')->where('city_id', 1)->update(['revoked_at' => now()]);
        $this->assertStatus(403, fn () => $this->people->find($this->account, $id));
        $this->assertStatus(403, fn () => $this->people->search($this->account, [1], ''));
        DB::table('web_accounts')->where('id', 1)->update(['disabled_at' => now()]);
        $this->assertStatus(403, fn () => $this->people->search($this->account, [], ''));
    }

    public function test_family_links_reject_cross_city_targets(): void
    {
        $id = $this->create();
        $local = $this->people->createFamily($this->account, 1, ['c_F_Number' => '001-A']);
        $foreign = $this->people->createFamily($this->account, 2, ['c_F_Number' => '001-B']);
        $version = $this->people->find($this->account, $id)['version'];
        $this->assertStatus(422, fn () => $this->people->attachFamily($this->account, $id, $foreign, [], $version));
        self::assertSame(0, DB::table('op_person_family')->count());
        $this->people->attachFamily($this->account, $id, $local, ['c_Work_Name' => 'عمل تجريبي'], $version);
        self::assertCount(1, $this->people->find($this->account, $id)['families']);
        $data = $this->people->find($this->account, $id);
        $membership = $data['families']->first();
        $this->people->attachFamily($this->account, $id, $local, ['c_Work_Name' => 'عمل معدل'], $data['version'], $id, [], (int) $membership->id);
        self::assertSame(1, DB::table('op_person_family')->count());
        self::assertSame('عمل معدل', DB::table('op_person_family')->value('c_Work_Name'));
        $this->actingAs($this->account)->get('/ar/people/'.$id)->assertOk()->assertSee('تعديل ارتباط العائلة');
    }

    public function test_imported_people_remain_hidden_until_their_exact_snapshot_is_published(): void
    {
        $id = $this->create(1, ['c_P_FirstName' => 'Historical']);
        $tableId = DB::table('op_person')->where('id', $id)->value('source_table_id');
        DB::table('operational_entities')->where('id', $id)->update(['identity_kind' => 'legacy_primary']);
        DB::table('source_records')->insert(['id' => 100, 'source_id' => 1, 'table_id' => $tableId, 'snapshot_id' => 55]);
        DB::table('operational_origins')->insert(['source_record_id' => 100, 'source_id' => 1, 'source_table_id' => $tableId, 'snapshot_id' => 55, 'entity_id' => $id]);
        self::assertSame(0, $this->people->search($this->account, [], 'Historical')->total());
        $this->assertStatus(404, fn () => $this->people->find($this->account, $id));
        DB::table('source_publications')->insert(['source_id' => 1, 'snapshot_id' => 54, 'publication_kind' => 'synthetic']);
        self::assertSame(0, $this->people->search($this->account, [], 'Historical')->total());
        DB::table('source_publications')->update(['snapshot_id' => 55]);
        self::assertSame(1, $this->people->search($this->account, [], 'Historical')->total());
        DB::table('op_person')->where('id', $id)->update(['c_P_DOB' => '0000-00-00', 'c_P_Mobile2' => ' 000 ']);
        $version = $this->people->find($this->account, $id)['version'];
        $this->people->update($this->account, $id, ['c_P_LastName' => 'Changed'], $version);
        self::assertSame('0000-00-00', DB::table('op_person')->where('id', $id)->value('c_P_DOB'));
        self::assertSame(' 000 ', DB::table('op_person')->where('id', $id)->value('c_P_Mobile2'));
        $this->app->instance('env', 'production');
        $this->assertStatus(404, fn () => $this->people->find($this->account, $id));
    }

    public function test_education_work_and_course_changes_are_scoped_to_the_person(): void
    {
        $id = $this->create();
        foreach (['education' => [], 'workexp' => ['c_W_Description' => 'Synthetic work', 'c_W_BeginDate' => '2001'], 'course' => ['c_Name' => 'Synthetic course', 'c_Date' => 'Spring 2002']] as $kind => $fields) {
            $version = $this->people->find($this->account, $id)['version'];
            $this->people->saveDetail($this->account, $id, $kind, $fields, $version);
            self::assertCount(1, $this->people->find($this->account, $id)[$kind]);
        }
        $other = $this->create(2);
        $detail = DB::table('op_person_course')->value('id');
        $this->assertStatus(404, fn () => $this->people->saveDetail($this->account, $other, 'course', ['c_Name' => 'Hijack'], $this->people->find($this->account, $other)['version'], $detail));
        self::assertSame('Synthetic course', DB::table('op_person_course')->value('c_Name'));
    }

    public function test_http_forms_render_bilingually_and_save_exact_values(): void
    {
        $this->get('/en/people')->assertRedirect('/en/login');
        $this->actingAs($this->account);
        $this->get('/en/people')->assertOk()->assertSee('People and families')->assertHeader('Cache-Control', 'no-store, private');
        $form = $this->get('/ar/people/create?city_id=1')->assertOk()->assertSee('dir="rtl"', false)->assertSee('إضافة شخص');
        $payload = ['city_id' => 1, 'create_token' => $form->viewData('createToken'), 'fields' => $this->fields(['c_P_Mobile' => ' 001  '])];
        $this->post('/ar/people', $payload)->assertRedirect();
        $this->post('/ar/people', $payload)->assertRedirect();
        self::assertSame(1, DB::table('op_person')->count());
        $id = DB::table('op_person')->value('id');
        self::assertSame(' 001  ', DB::table('op_person')->value('c_P_Mobile'));
        $this->get('/ar/people/'.$id)->assertOk()->assertSee('العمل والخبرات');
        DB::table('op_person')->where('id', $id)->update(['c_P_DOB' => '0000-00-00']);
        $data = $this->people->find($this->account, $id);
        $this->put('/en/people/'.$id, ['version' => $data['version'], 'fields' => ['c_P_DOB' => '0000-00-00', 'c_P_LastName' => 'Edited']])->assertRedirect();
        self::assertSame('0000-00-00', DB::table('op_person')->value('c_P_DOB'));
        $this->put('/en/people/'.$id, ['version' => $data['version'], 'fields' => ['c_P_LastName' => 'Stale']])->assertStatus(409);
        DB::table('account_city_capabilities')->where('capability', 'people.manage')->update(['revoked_at' => now()]);
        $this->get('/en/people/'.$id)->assertOk()->assertDontSee('name="version"', false);
    }

    private function fields(array $overrides = []): array
    {
        return array_replace(['c_P_FirstName' => 'Synthetic', 'c_P_LastName' => 'Person', 'c_P_Sex' => 'Unknown'], $overrides);
    }

    public function test_edited_numbers_require_review_and_unavailable_references_survive_save(): void
    {
        $id = $this->create(1, ['c_P_NationalNumber' => '123']);
        $other = $this->create(1, ['c_P_NationalNumber' => '456']);
        $version = $this->people->find($this->account, $other)['version'];
        $this->assertStatus(422, fn () => $this->people->update($this->account, $other, ['c_P_NationalNumber' => '0123'], $version));
        $this->people->update($this->account, $other, ['c_P_NationalNumber' => '0123'], $version, [], true);
        self::assertSame(2, DB::table('op_person')->count());
        $relation = collect(OperationalManifest::load()['relationships'])->first(fn ($r) => $r['target_table'] === 'op_person' && $r['child_columns'] === ['P_Parish_ID']);
        DB::table('op_person')->where('id', $id)->update([$relation['resolved_column'] => 9001, $relation['state_column'] => 'resolved']);
        $this->actingAs($this->account)->get('/en/people/'.$id)->assertOk()->assertSee('value="9001" selected', false);
        $data = $this->people->find($this->account, $id);
        $this->people->update($this->account, $id, ['c_P_LastName' => 'Retained'], $data['version'], ['P_Parish_ID' => 9001]);
        self::assertSame(9001, DB::table('op_person')->where('id', $id)->value($relation['resolved_column']));
    }

    public function test_failed_entry_input_and_null_choices_are_scoped_to_its_form(): void
    {
        $id = $this->create();
        foreach (['First course', 'Second course'] as $name) {
            $this->people->saveDetail($this->account, $id, 'course', ['c_Name' => $name], $this->people->find($this->account, $id)['version']);
        }
        $first = DB::table('op_person_course')->orderBy('id')->value('id');
        $this->actingAs($this->account)->withSession(['_old_input' => [
            '_form' => 'course-'.$first, 'version' => 'original-stale-version', 'fields' => ['c_Name' => 'Recovered only here', 'c_Date' => 'discarded'], 'null_fields' => ['c_Date'],
        ]])->get('/en/people/'.$id)->assertOk()->assertSee('Recovered only here')->assertSee('name="version" value="original-stale-version"', false)->assertSee('value="Second course"', false)->assertSee('value="c_Date" x-model="missing" checked', false);
        $html = $this->get('/en/people/'.$id)->getContent();
        self::assertLessThanOrEqual(1, substr_count($html, 'Recovered only here'));
    }

    public function test_canonical_education_references_are_scoped_and_visible_to_readers(): void
    {
        $person = $this->create();
        $definition = OperationalManifest::table('education');
        $tableId = (int) preg_replace('/\D/', '', $definition['checks'][0]);
        $lookups = [];
        foreach ([1, 2] as $city) {
            $scope = ['city_id' => $city, 'source_id' => $city, 'source_table_id' => $tableId];
            $id = DB::table('operational_entities')->insertGetId($scope + ['identity_kind' => 'web', 'created_at' => now()]);
            $payload = $scope + ['id' => $id];
            foreach ($definition['columns'] as $column) {
                if (str_starts_with($column['name'], 'c_') && str_contains($column['name'], 'Name')) {
                    $payload[$column['name']] = 'Synthetic education '.$city;
                }
            }
            DB::table($definition['name'])->insert($payload);
            $lookups[$city] = $id;
        }
        $version = $this->people->find($this->account, $person)['version'];
        $this->assertStatus(422, fn () => $this->people->saveDetail($this->account, $person, 'education', [], $version, null, ['Education_ID' => $lookups[2]]));
        self::assertSame(0, DB::table('op_person_education')->count());
        $this->people->saveDetail($this->account, $person, 'education', [], $version, null, ['Education_ID' => $lookups[1]]);
        DB::table('account_city_capabilities')->where('capability', 'people.manage')->update(['revoked_at' => now()]);
        $this->actingAs($this->account)->get('/en/people/'.$person)->assertOk()->assertSee('Synthetic education 1')->assertDontSee('Synthetic education 2');
    }

    private function create(int $city = 1, array $fields = []): int
    {
        return $this->people->create($this->account, $city, $this->fields($fields));
    }

    private function assertStatus(int $status, callable $callback): void
    {
        try {
            $callback();
            self::fail('Expected HTTP status '.$status);
        } catch (HttpException $exception) {
            self::assertSame($status, $exception->getStatusCode());
        } catch (ValidationException $exception) {
            self::assertSame($status, $exception->status);
        }
    }

    private function schema(): void
    {
        $support = [
            'web_accounts' => ['username', 'password_hash', 'credential_principal_id', 'disabled_at', 'created_at', 'updated_at'],
            'cities' => ['name_en', 'name_ar'], 'sources' => ['city_id', 'name'], 'source_tables' => ['name'],
            'account_city_capabilities' => ['account_id', 'city_id', 'capability', 'revoked_at'],
            'account_audit_events' => ['actor_account_id', 'subject_account_id', 'city_id', 'event_type', 'outcome', 'subject_type', 'subject_id', 'decision_locator', 'occurred_at'],
            'source_records' => ['source_id', 'table_id', 'snapshot_id'],
            'source_publications' => ['source_id', 'snapshot_id', 'publication_kind'],
        ];
        foreach ($support as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns) {
                $table->id();
                foreach ($columns as $column) {
                    str_ends_with($column, '_id') ? $table->integer($column)->nullable() : $table->text($column)->nullable();
                }
            });
        }
        foreach (OperationalManifest::tables() as $definition) {
            if (! str_starts_with($definition['name'], 'op_') && ! in_array($definition['name'], ['operational_entities', 'operational_origins'], true)) {
                continue;
            }
            Schema::create($definition['name'], function (Blueprint $table) use ($definition) {
                foreach ($definition['columns'] as $column) {
                    $name = $column['name'];
                    if ($name === 'id') {
                        $table->id();

                        continue;
                    }
                    $field = preg_match('/^(?:BIGINT|int|tinyint)/i', $column['definition']) ? $table->integer($name) : $table->text($name);
                    $field->nullable();
                    if (preg_match("/DEFAULT '([^']*)'/", $column['definition'], $match)) {
                        $field->default($match[1]);
                    }
                }
            });
        }
    }
}
