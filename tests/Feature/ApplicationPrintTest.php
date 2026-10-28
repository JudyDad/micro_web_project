<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\ApplicationFixture;
use Tests\TestCase;

/** Printable application form (checklist print style) and the two-value sex list. Invented data on SQLite. */
final class ApplicationPrintTest extends TestCase
{
    private WebAccount $account;

    private array $intake;

    private array $catalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        $this->intake = ApplicationFixture::intake(1);
        $this->catalog = [
            'P_MaritalStatus_ID' => ApplicationFixture::row('maritalstatus', 1, ['c_ID' => 1, 'c_Name' => 'متزوج']),
            'P_Parish_ID' => ApplicationFixture::row('parish', 1, ['c_ID' => 1, 'c_Name_ar' => 'الروم الكاثوليك', 'c_Name_en' => 'Greek Catholic']),
            'Street_ID' => ApplicationFixture::row('street', 1, ['c_ID' => 1, 'c_Name' => 'الحميدية']),
            'P_HomeType_ID' => ApplicationFixture::row('assettype', 1, ['c_ID' => 1, 'c_Name' => 'إيجار']),
        ];
        ApplicationFixture::row('relation', 1, ['c_ID' => 1, 'c_Name' => 'مستفيد', 'c_Name_en' => 'Self']);
        config(['session.block_store' => 'array', 'filesystems.disks.local.serve' => false]);
        Storage::fake('local');
        $this->actingAs($this->account);
    }

    public function test_printable_form_shows_project_applicant_family_and_budget_in_arabic_and_english(): void
    {
        $id = $this->intakeApplication();
        $applications = app(ApplicationService::class);
        $applications->saveDetail($this->account, $id, 'material', ['c_Name' => 'ماكينة خياطة', 'c_Price' => '320.5', 'c_Amount' => '1'], $applications->find($this->account, $id)['version']);

        $this->get('/en/applications/'.$id)->assertOk()->assertSee('/en/applications/'.$id.'/print');
        $page = $this->get('/ar/applications/'.$id.'/print')->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('dir="rtl"', false)->assertSee('assets/brand/checklist-header.png')->assertSee('استمارة الطلب')
            ->assertSee('معلومات المشروع')->assertSee('مشروع خياطة منزلية')->assertSee('المعلومات الشخصية لمقدم الطلب: سامر جورج حداد')
            ->assertSee('01234567890')->assertSee('الروم الكاثوليك')->assertSee('إيجار')
            ->assertSee('رقم دفتر العائلة')->assertSee('F-100')->assertSee('مستفيد')
            ->assertSee('الميزانية المقترحة')->assertSee('ماكينة خياطة')->assertSee('320.5')->assertSee('التواقيع');
        self::assertStringNotContainsString('خطأ', $page->getContent());
        $this->get('/en/applications/'.$id.'/print')->assertOk()->assertSee('Application Form')->assertSee('Family book number')->assertSee('Proposed budget')->assertSee('Male');
        self::assertSame(2, DB::table('account_audit_events')->where('event_type', 'applications.printed')->where('outcome', 'succeeded')->count());
    }

    public function test_print_needs_people_view_because_it_shows_personal_data(): void
    {
        $id = $this->intakeApplication();
        DB::table('account_city_capabilities')->where('account_id', $this->account->id)->where('city_id', 1)->where('capability', 'people.view')->update(['revoked_at' => now('UTC')]);
        $this->get('/ar/applications/'.$id.'/print')->assertForbidden();
        $this->get('/ar/applications/'.$id)->assertOk()->assertDontSee('/applications/'.$id.'/print');
        self::assertSame(0, DB::table('account_audit_events')->where('event_type', 'applications.printed')->count());
        $this->get('/ar/applications/999999/print')->assertNotFound();
    }

    public function test_sex_is_the_desktop_two_value_list_on_every_person_form(): void
    {
        $this->get('/ar/applications/create?city_id=1')->assertOk()
            ->assertSee('id="applicant-c_P_Sex" name="person[fields][c_P_Sex]"', false)->assertSee('<option value="ذكر"', false)->assertSee('<option value="أنثى"', false)
            ->assertDontSee('name="person[fields][c_P_Sex]" type="text"', false);
        $this->get('/ar/people/create?city_id=1')->assertOk()->assertSee('id="new-c_P_Sex" name="fields[c_P_Sex]"', false)->assertSee('<option value="أنثى"', false);
        // A historical value outside the list stays visible and selected on the person's page until it is changed.
        $this->get('/ar/people/'.$this->intake['person'])->assertOk()->assertSee('<option value="Unknown" selected>', false)->assertSee('<option value="ذكر"', false);

        $token = $this->get('/ar/applications/create?city_id=1')->viewData('createToken');
        $this->post('/ar/applications', $this->payload($token, ['person' => ['fields' => ['c_P_Sex' => 'Unknown']]]))->assertSessionHasErrors(['person.fields.c_P_Sex']);
        self::assertSame(0, DB::table('op_microproject')->count());
        $this->post('/ar/applications', $this->payload($token, ['person' => ['fields' => ['c_P_Sex' => 'أنثى']]]))->assertRedirect()->assertSessionHasNoErrors();
        self::assertSame('أنثى', DB::table('op_person')->where('c_P_NationalNumber', '01234567890')->value('c_P_Sex'));
    }

    private function intakeApplication(): int
    {
        return app(ApplicationService::class)->intake($this->account, 1, ['c_MP_Name' => 'مشروع خياطة منزلية', 'c_MP_DateOfRequest' => '2026-10-27'], $this->intake['references'],
            ['id' => null, 'fields' => $this->payload('')['person']['fields'], 'references' => $this->catalog, 'confirm_duplicate' => false], ['id' => null, 'number' => 'F-100', 'confirm_duplicate' => false]);
    }

    private function payload(string $token, array $overrides = []): array
    {
        return array_replace_recursive([
            '_form' => 'application', 'create_token' => $token, 'city_id' => 1, 'beneficiary_mode' => 'new',
            'person' => [
                'fields' => ['c_P_FirstName' => 'سامر', 'c_P_LastName' => 'حداد', 'c_P_Sex' => 'ذكر', 'c_P_FatherName' => 'جورج', 'c_P_MotherName' => 'مريم',
                    'c_P_NationalNumber' => '01234567890', 'c_P_DOB' => '1988-05-01', 'c_P_IntermidiarySide' => 'رعية اللاتين', 'c_P_HomeAddress' => 'شارع الكنيسة 5', 'c_P_Mobile' => '0999000000'],
                'references' => array_map('strval', $this->catalog),
            ],
            'family' => ['number' => 'F-100'],
            'references' => $this->intake['references'],
            'fields' => ['c_MP_Name' => 'مشروع خياطة منزلية', 'c_MP_DateOfRequest' => '2026-10-27'],
        ], $overrides);
    }
}
