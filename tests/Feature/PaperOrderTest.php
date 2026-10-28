<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Applications\PaperOrder;
use App\Services\People\PeopleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

/**
 * The web forms follow the paper form's order and controls (desktop Application_New_Form): field sequence, lookup
 * ordering and labels, the birth year, yes/no controls and blank boxes on the create form. Invented data on SQLite.
 */
final class PaperOrderTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private array $catalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = [
            'P_MaritalStatus_ID' => ApplicationFixture::row('maritalstatus', 1, ['c_ID' => 2, 'c_Name' => 'متزوج']),
            'P_Parish_ID' => ApplicationFixture::row('parish', 1, ['c_ID' => 2, 'c_Name_ar' => 'الروم', 'c_Name_en' => 'Greek']),
            'Street_ID' => ApplicationFixture::row('street', 1, ['c_ID' => 1, 'c_Name' => 'وادي النصارى']),
            'P_HomeType_ID' => ApplicationFixture::row('assettype', 1, ['c_ID' => 1, 'c_Name' => 'ملك']),
        ];
        ApplicationFixture::row('relation', 1, ['c_ID' => 1, 'c_Name' => 'مستفيد', 'c_Name_en' => 'Self']);
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
        $this->actingAs($this->account);
    }

    public function test_field_metadata_follows_the_paper_order_and_hides_internal_columns(): void
    {
        $applications = app(ApplicationService::class);
        $people = app(PeopleService::class);
        $project = array_keys($applications->fields('application') + $applications->relations('application'));
        self::assertSame(['c_MP_Speciality', 'c_MP_DateOfRequest', 'c_MP_Name', 'c_MP_RequestedAmount'], array_values(array_intersect($project, ['c_MP_Name', 'c_MP_DateOfRequest', 'c_MP_Speciality', 'c_MP_RequestedAmount'])));
        $merged = array_keys(PaperOrder::sort('application', $applications->fields('application') + $applications->relations('application')));
        self::assertSame(['SubCategory_ID', 'Career_ID', 'c_MP_Speciality', 'MP_Type_ID', 'c_MP_DateOfRequest', 'MP_SubType_ID', 'c_MP_Name', 'c_MP_RequestedAmount', 'MP_FundType_ID'], array_slice($merged, 0, 9), 'the project form row order');
        $person = array_keys($people->fields('person'));
        self::assertSame(['c_P_FirstName', 'c_P_LastName', 'c_P_Sex', 'c_P_FullName_en', 'c_P_FatherName', 'c_P_MotherName', 'c_P_RegistrationPlace', 'c_P_NationalNumber', 'c_P_DOB'], array_slice($person, 0, 9));
        self::assertNotContains('c_IsProjectOwner', $person);
        self::assertNotContains('c_Partnership', $project);
        self::assertSame(['Work_ID', 'W_WorkType_ID', 'c_W_Description', 'c_W_BeginDate'], array_slice(array_keys(PaperOrder::sort('workexp', $people->fields('workexp') + $people->relations('workexp'))), 0, 4));
    }

    public function test_lookups_are_sorted_as_the_desktop_and_labelled_by_name(): void
    {
        ApplicationFixture::row('street', 1, ['c_ID' => 2, 'c_Name' => 'الحميدية']);
        ApplicationFixture::row('parish', 1, ['c_ID' => 1, 'c_Name_ar' => 'اللاتين', 'c_Name_en' => 'Latin']);
        $options = app(PeopleService::class)->options($this->account, 1, 'person');
        self::assertSame(['الحميدية', 'وادي النصارى'], $options['Street_ID']->pluck('label')->all(), 'streets by name');
        self::assertSame(['اللاتين', 'الروم'], $options['P_Parish_ID']->pluck('label')->all(), 'parishes by original id');
        app()->setLocale('en');
        self::assertSame(['Latin', 'Greek'], app(PeopleService::class)->options($this->account, 1, 'person')['P_Parish_ID']->pluck('label')->all());
        $career = app(ApplicationService::class)->options($this->account, 1, 'application')['Career_ID'][0];
        self::assertSame('Synthetic career', $career->label);
        self::assertSame($this->intake['references']['SubCategory_ID'], (int) $career->parent_id, 'career carries its subcategory for the cascade');
    }

    public function test_create_form_shows_the_project_first_with_typed_controls_and_no_raw_keys(): void
    {
        $page = $this->get('/ar/applications/create?city_id=1')->assertOk();
        $html = $page->getContent();
        self::assertLessThan(mb_strpos($html, '<legend>المعلومات الشخصية لمقدم الطلب</legend>'), mb_strpos($html, '<legend>استمارة المشروع</legend>'), 'project form before the applicant');
        self::assertLessThan(mb_strpos($html, '<legend>دفتر العائلة</legend>'), mb_strpos($html, '<legend>المعلومات الشخصية لمقدم الطلب</legend>'));
        $page->assertSee('name="blank_is_null"', false)->assertSee('id="applicant-c_P_DOB"', false)->assertSee('placeholder="YYYY"', false)
            ->assertSee('type="date"', false)->assertDontSee('name="person[fields][c_P_FullName_en]"', false)->assertDontSee('name="fields[c_MP_RequestedAmount]"', false);
        self::assertDoesNotMatchRegularExpression('/>\s*(applications|people|forms)\.[a-z_.]+\s*</', $html);
        $show = $this->get('/ar/applications/'.ApplicationFixture::row('microproject', 1, ['c_MP_ID' => 77, 'c_MP_Name' => 'x', 'c_ApplicationState' => 'zero']))->assertOk();
        self::assertDoesNotMatchRegularExpression('/>\s*applications\.[a-z_]+\s*</', $show->getContent());
        $show->assertSee('name="fields[c_MP_Agreement1]" value="1"', false)->assertSee('تقديم الطلب الكامل');
    }

    public function test_birth_year_and_blank_boxes_are_stored_as_the_desktop_does(): void
    {
        $token = $this->get('/ar/applications/create?city_id=1')->viewData('createToken');
        $this->post('/ar/applications', [
            '_form' => 'application', 'create_token' => $token, 'city_id' => 1, 'beneficiary_mode' => 'new', 'blank_is_null' => '1',
            'person' => ['fields' => ['c_P_FirstName' => 'ليلى', 'c_P_LastName' => 'نصر', 'c_P_Sex' => 'أنثى', 'c_P_FatherName' => 'جورج', 'c_P_MotherName' => 'هالة',
                'c_P_NationalNumber' => '09876543210', 'c_P_DOB' => '1990', 'c_P_IntermidiarySide' => 'الرعية', 'c_P_HomeAddress' => 'شارع 5', 'c_P_Mobile' => '0912345678', 'c_P_RegistrationPlace' => '', 'c_P_MilitaryPeriod' => ''],
                'references' => array_map('strval', $this->catalog)],
            'family' => ['number' => 'F-1'], 'references' => $this->intake['references'],
            'fields' => ['c_MP_Name' => 'مشروع', 'c_MP_DateOfRequest' => '2026-10-28', 'c_MP_Speciality' => '', 'c_MP_Agreement1' => ''],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $person = DB::table('op_person')->where('c_P_FirstName', 'ليلى')->first();
        self::assertSame('1990-01-01', $person->c_P_DOB);
        self::assertNull($person->c_P_RegistrationPlace);
        self::assertNull($person->c_P_MilitaryPeriod);
        $project = DB::table('op_microproject')->where('c_MP_Name', 'مشروع')->first();
        self::assertNull($project->c_MP_Speciality);
        self::assertNull($project->c_MP_Agreement1);
    }

    public function test_one_save_writes_project_link_and_person_rows_together_or_not_at_all(): void
    {
        $applications = app(ApplicationService::class);
        $id = $applications->create($this->account, 1, ['c_MP_Name' => 'قبل', 'c_MP_DateOfRequest' => '2026-10-28'], $this->intake['person'], $this->intake['references']);
        $page = $this->get('/ar/applications/'.$id)->assertOk()->assertSee('id="paper-form"', false)->assertSee('name="link[fields][c_P_Loss]"', false)->assertSee('name="person[fields][c_P_FirstName]"', false)->assertSee('form="paper-form"', false);
        $version = $page->viewData('version');
        $linkId = (int) $page->viewData('beneficiary')['link']->id;
        $this->post('/ar/applications/'.$id, ['_form' => 'application', 'city_id' => 1, 'kind' => 'application', 'version' => $version, 'link_id' => $linkId, 'person_id' => $this->intake['person'],
            'fields' => ['c_MP_Name' => 'بعد', 'c_MP_Agreement1' => '1'], 'link' => ['fields' => ['c_P_Loss' => 'قصة', 'c_P_HasWork' => '0']], 'person' => ['fields' => ['c_P_FirstName' => 'سمير', 'c_P_DOB' => '1985']],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $project = DB::table('op_microproject')->where('id', $id)->first();
        self::assertSame(['بعد', 1], [$project->c_MP_Name, (int) $project->c_MP_Agreement1]);
        $link = DB::table('op_person_microproject')->where('id', $linkId)->first();
        self::assertSame(['قصة', 0], [$link->c_P_Loss, (int) $link->c_P_HasWork]);
        $person = DB::table('op_person')->where('id', $this->intake['person'])->first();
        self::assertSame(['سمير', '1985-01-01'], [$person->c_P_FirstName, $person->c_P_DOB]);
        // A failing person section rolls the project change back: nothing is written.
        $version = $this->get('/ar/applications/'.$id)->viewData('version');
        $this->post('/ar/applications/'.$id, ['_form' => 'application', 'city_id' => 1, 'kind' => 'application', 'version' => $version, 'link_id' => $linkId, 'person_id' => $this->intake['person'],
            'fields' => ['c_MP_Name' => 'لن يُحفظ'], 'person' => ['fields' => ['c_P_DOB' => 'not-a-date']],
        ])->assertRedirect()->assertSessionHasErrors();
        self::assertSame('بعد', DB::table('op_microproject')->where('id', $id)->value('c_MP_Name'));
    }
}
