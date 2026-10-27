<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

/**
 * One-form intake like the desktop Application_New_Form: the applicant, the family book and the project are
 * entered on one screen and written in one transaction. Invented data on SQLite; MySQL locking is not exercised.
 */
final class UnifiedIntakeTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private array $catalog;

    private int $relation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = [
            'P_MaritalStatus_ID' => ApplicationFixture::row('maritalstatus', 1, ['c_ID' => 1, 'c_Name' => 'أعزب']),
            'P_Parish_ID' => ApplicationFixture::row('parish', 1, ['c_ID' => 1, 'c_Name_ar' => 'اللاتين', 'c_Name_en' => 'Latin']),
            'Street_ID' => ApplicationFixture::row('street', 1, ['c_ID' => 1, 'c_Name' => 'الحميدية']),
            'P_HomeType_ID' => ApplicationFixture::row('assettype', 1, ['c_ID' => 1, 'c_Name' => 'ملك']),
        ];
        $this->relation = ApplicationFixture::row('relation', 1, ['c_ID' => 1, 'c_Name' => 'مستفيد', 'c_Name_en' => 'Self']);
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
        $this->actingAs($this->account);
    }

    public function test_one_form_saves_applicant_family_and_project_together(): void
    {
        $page = $this->get('/ar/applications/create?city_id=1')->assertOk()
            ->assertSee('المعلومات الشخصية لمقدم الطلب')->assertSee('دفتر العائلة')->assertSee('name="person[fields][c_P_FatherName]"', false)->assertSee('name="family[number]"', false);
        $people = DB::table('op_person')->count();

        $this->post('/ar/applications', $this->payload($page->viewData('createToken')))->assertRedirect()->assertSessionHasNoErrors();

        self::assertSame($people + 1, DB::table('op_person')->count());
        $person = DB::table('op_person')->where('c_P_NationalNumber', '01234567890')->first();
        self::assertSame('سامر', $person->c_P_FirstName);
        self::assertSame($this->catalog['P_Parish_ID'], (int) $person->{$this->column('person', 'P_Parish_ID')});
        $family = DB::table('op_family')->where('c_F_Number', 'F-100')->first();
        self::assertNotNull($family);
        // Desktop: person_family (Family_ID, Person_ID, Relation_ID = 'مستفيد', P_Provider_ID = the person)
        $membership = DB::table('op_person_family')->first();
        self::assertSame((int) $family->id, (int) $membership->{$this->column('person_family', 'Family_ID')});
        self::assertSame((int) $person->id, (int) $membership->{$this->column('person_family', 'Person_ID')});
        self::assertSame((int) $person->id, (int) $membership->{$this->column('person_family', 'P_Provider_ID')});
        self::assertSame($this->relation, (int) $membership->{$this->column('person_family', 'Relation_ID')});
        $project = DB::table('op_microproject')->first();
        self::assertSame('مشروع خياطة منزلية', $project->c_MP_Name);
        self::assertSame('zero', $project->c_ApplicationState);
        $link = DB::table('op_person_microproject')->first();
        self::assertSame((int) $person->id, (int) $link->{$this->column('person_microproject', 'Person_ID')});
        self::assertSame('مستفيد', $link->c_PersonType);
        self::assertSame(['applications.created', 'people.created', 'people.created', 'people.updated'], DB::table('account_audit_events')->where('outcome', 'succeeded')->orderBy('event_type')->pluck('event_type')->all());
        $this->get('/en/applications/'.$project->id)->assertOk()->assertSee('سامر');
    }

    public function test_every_section_is_checked_before_anything_is_written(): void
    {
        $before = $this->counts();
        $this->post('/ar/applications', $this->payload($this->token(), [
            'person' => ['fields' => ['c_P_FatherName' => '', 'c_P_Mobile' => '']],
            'family' => ['number' => ''],
            'fields' => ['c_MP_Name' => ''],
        ]))->assertSessionHasErrors(['person.fields.c_P_FatherName', 'person.fields.c_P_Mobile', 'family.number', 'c_MP_Name']);
        self::assertSame($before, $this->counts());
        $errors = session('errors')->all();
        self::assertStringContainsString('اسم الأب', implode(' ', $errors));
    }

    public function test_a_project_error_rolls_back_the_new_applicant(): void
    {
        $before = $this->counts();
        $this->post('/ar/applications', $this->payload($this->token(), ['fields' => ['c_MP_DateOfRequest' => 'not-a-date']]))->assertSessionHasErrors(['c_MP_DateOfRequest']);
        self::assertSame($before, $this->counts());
    }

    public function test_duplicate_national_number_needs_explicit_confirmation_and_never_merges(): void
    {
        ApplicationFixture::row('person', 1, ['c_P_FirstName' => 'سامر', 'c_P_LastName' => 'قديم', 'c_P_Sex' => 'ذكر', 'c_P_NationalNumber' => '1234567890']);
        $before = $this->counts();
        $this->post('/ar/applications', $this->payload($this->token()))->assertSessionHasErrors(['person.duplicate']);
        self::assertSame($before, $this->counts());
        $this->get('/ar/applications/create?city_id=1')->assertOk()->assertSee('أشخاص بالرقم الوطني نفسه')->assertSee('سامر قديم');

        $this->post('/ar/applications', $this->payload($this->token(), ['person' => ['confirm_duplicate' => '1']]))->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame(2, DB::table('op_person')->whereIn('c_P_NationalNumber', ['1234567890', '01234567890'])->count());
    }

    public function test_matching_family_number_is_reused_only_when_chosen(): void
    {
        $existing = ApplicationFixture::row('family', 1, ['c_F_Number' => 'F-100']);
        $this->post('/ar/applications', $this->payload($this->token()))->assertSessionHasErrors(['family.duplicate']);
        self::assertSame(0, DB::table('op_microproject')->count());
        $this->get('/ar/applications/create?city_id=1')->assertOk()->assertSee('عائلات بالرقم نفسه')->assertSee('name="family[id]" value="'.$existing.'"', false);

        $this->post('/ar/applications', $this->payload($this->token(), ['family' => ['id' => (string) $existing]]))->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame(1, DB::table('op_family')->count());
        self::assertSame($existing, (int) DB::table('op_person_family')->value($this->column('person_family', 'Family_ID')));

        $this->post('/ar/applications', $this->payload($this->token(), ['family' => ['confirm_duplicate' => '1'], 'person' => ['fields' => ['c_P_NationalNumber' => '09876543210']]]))->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame(2, DB::table('op_family')->where('c_F_Number', 'F-100')->count());
    }

    public function test_existing_person_can_still_be_chosen_without_touching_the_directory(): void
    {
        $before = $this->counts();
        $this->post('/ar/applications', ['create_token' => $this->token(), 'city_id' => 1, 'beneficiary_mode' => 'existing', 'beneficiary_id' => $this->intake['person'],
            'references' => $this->intake['references'], 'fields' => ['c_MP_Name' => 'مشروع لشخص موجود', 'c_MP_DateOfRequest' => '2026-10-27']])->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame($before['people'], DB::table('op_person')->count());
        self::assertSame(0, DB::table('op_family')->count());
        self::assertSame(0, DB::table('op_person_family')->count());
        self::assertSame($this->intake['person'], (int) DB::table('op_person_microproject')->value($this->column('person_microproject', 'Person_ID')));

        $this->post('/ar/applications', ['create_token' => $this->token(), 'city_id' => 1, 'beneficiary_mode' => 'existing', 'beneficiary_id' => '',
            'references' => $this->intake['references'], 'fields' => ['c_MP_Name' => 'بلا شخص', 'c_MP_DateOfRequest' => '2026-10-27']])->assertSessionHasErrors(['beneficiary_id']);
        self::assertSame(1, DB::table('op_microproject')->count());
    }

    public function test_existing_person_with_a_family_number_joins_that_family_once(): void
    {
        $this->post('/ar/applications', ['create_token' => $this->token(), 'city_id' => 1, 'beneficiary_mode' => 'existing', 'beneficiary_id' => $this->intake['person'], 'family' => ['number' => 'F-7'],
            'references' => $this->intake['references'], 'fields' => ['c_MP_Name' => 'الأول', 'c_MP_DateOfRequest' => '2026-10-27']])->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame('F-7', DB::table('op_family')->value('c_F_Number'));
        self::assertSame($this->intake['person'], (int) DB::table('op_person_family')->value($this->column('person_family', 'P_Provider_ID')));

        $this->post('/ar/applications', ['create_token' => $this->token(), 'city_id' => 1, 'beneficiary_mode' => 'existing', 'beneficiary_id' => $this->intake['person'], 'family' => ['number' => 'F-8'],
            'references' => $this->intake['references'], 'fields' => ['c_MP_Name' => 'الثاني', 'c_MP_DateOfRequest' => '2026-10-27']])->assertSessionHasErrors(['family.existing']);
        self::assertSame(1, DB::table('op_family')->count());
        self::assertSame(1, DB::table('op_microproject')->count());
    }

    public function test_private_draft_keeps_the_applicant_and_family_sections(): void
    {
        $payload = $this->payload($this->token(), ['kind' => 'application']);
        $this->post('/ar/applications/drafts', $payload)->assertRedirect('/ar/applications/drafts?city_id=1');
        $draft = $this->get('/ar/applications/drafts?city_id=1')->assertOk()->viewData('drafts')[0];
        $this->get('/ar/applications/drafts/'.$draft['id'].'?city_id=1')->assertRedirect('/ar/applications/create?city_id=1');
        $this->get('/ar/applications/create?city_id=1')->assertOk()
            ->assertSee('value="سامر"', false)->assertSee('value="F-100"', false)->assertSee('>مشروع خياطة منزلية</textarea>', false);
        self::assertSame(0, DB::table('op_person')->where('c_P_FirstName', 'سامر')->count());
    }

    private function payload(string $token, array $overrides = []): array
    {
        return array_replace_recursive([
            '_form' => 'application', 'create_token' => $token, 'city_id' => 1, 'beneficiary_mode' => 'new',
            'person' => [
                'fields' => ['c_P_FirstName' => 'سامر', 'c_P_LastName' => 'حداد', 'c_P_Sex' => 'ذكر', 'c_P_FatherName' => 'جورج', 'c_P_MotherName' => 'مريم',
                    'c_P_NationalNumber' => '01234567890', 'c_P_DOB' => '1988-05-01', 'c_P_IntermidiarySide' => 'رعية اللاتين', 'c_P_HomeAddress' => 'شارع الكنيسة 5', 'c_P_Mobile' => '0999000000'],
                'references' => array_map('strval', $this->catalog),
                'null_fields' => ['c_P_FullName_en'],
            ],
            'family' => ['number' => 'F-100'],
            'references' => $this->intake['references'],
            'fields' => ['c_MP_Name' => 'مشروع خياطة منزلية', 'c_MP_DateOfRequest' => '2026-10-27'],
        ], $overrides);
    }

    private function token(): string
    {
        return $this->get('/ar/applications/create?city_id=1')->assertOk()->viewData('createToken');
    }

    private function counts(): array
    {
        return ['people' => DB::table('op_person')->count(), 'families' => DB::table('op_family')->count(), 'memberships' => DB::table('op_person_family')->count(), 'projects' => DB::table('op_microproject')->count(), 'links' => DB::table('op_person_microproject')->count()];
    }

    private function column(string $table, string $key): string
    {
        foreach (OperationalManifest::load()['relationships'] as $relation) {
            if ($relation['child_source_table'] === $table && $relation['child_columns'] === [$key]) {
                return $relation['resolved_column'];
            }
        }
        self::fail('Unknown relationship '.$table.'.'.$key);
    }
}
