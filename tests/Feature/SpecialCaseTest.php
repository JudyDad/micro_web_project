<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\WebAccount;
use App\Services\Field\SpecialCaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\ApplicationFixture;
use Tests\Support\FieldFixture;
use Tests\Support\FinanceFixture;
use Tests\TestCase;

final class SpecialCaseTest extends TestCase
{
    private WebAccount $account;

    private SpecialCaseService $cases;

    private array $types;

    private int $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        ApplicationFixture::sqliteSchema();
        $this->account = ApplicationFixture::account();
        FieldFixture::grant($this->account);
        $this->cases = app(SpecialCaseService::class);
        $intake = ApplicationFixture::intake(1);
        $this->types = [1 => FieldFixture::specialTypes(1), 2 => FieldFixture::specialTypes(2)];
        $this->project = FinanceFixture::acceptedProject($this->account, $intake);
    }

    private function version(): string
    {
        return $this->cases->show($this->account, $this->project)['version'];
    }

    private function input(array $overrides = []): array
    {
        return $overrides + ['type_id' => $this->types[1]['visit'], 'date' => '2026-10-20', 'reason' => 'Late instalment', 'description' => ' exact  text ', 'action' => 'Called the family'];
    }

    private function assertRejected(string $field, callable $call): void
    {
        try {
            $call();
            self::fail('Expected a validation error for '.$field);
        } catch (ValidationException $e) {
            self::assertArrayHasKey($field, $e->errors());
        }
    }

    public function test_a_review_is_recorded_with_exact_text_and_edited_in_place(): void
    {
        $id = $this->cases->save($this->account, $this->project, $this->input(), $this->version());
        $row = DB::table('op_specialcase')->where('id', $id)->first();
        self::assertSame(' exact  text ', $row->c_Description);
        self::assertSame('2026-10-20', substr((string) $row->c_Date, 0, 10));
        self::assertSame('null', $row->ref_4ee04ba62b40_state, 'the historical registrar is not invented');

        $this->cases->save($this->account, $this->project, $this->input(['case_id' => $id, 'action' => 'Visited']), $this->version());
        self::assertSame(1, DB::table('op_specialcase')->count());
        self::assertSame('Visited', DB::table('op_specialcase')->value('c_Action'));
        app()->setLocale('en');
        self::assertSame('Visit', $this->cases->show($this->account, $this->project)['cases']->first()->type_name);
    }

    public function test_validation_follows_the_desktop_and_writes_nothing(): void
    {
        $this->assertRejected('reason', fn () => $this->cases->save($this->account, $this->project, $this->input(['reason' => '']), $this->version()));
        $this->assertRejected('type_id', fn () => $this->cases->save($this->account, $this->project, $this->input(['type_id' => null]), $this->version()));
        $this->assertRejected('type_id', fn () => $this->cases->save($this->account, $this->project, $this->input(['type_id' => $this->types[2]['phone']]), $this->version()));
        $this->assertRejected('date', fn () => $this->cases->save($this->account, $this->project, $this->input(['date' => '2026-02-30']), $this->version()));
        self::assertSame(0, DB::table('op_specialcase')->count());
    }

    public function test_stale_and_repeated_submissions_are_refused(): void
    {
        $version = $this->version();
        $this->cases->save($this->account, $this->project, $this->input(), $version);
        try {
            $this->cases->save($this->account, $this->project, $this->input(), $version);
            self::fail('A repeated submission must be refused.');
        } catch (HttpException $e) {
            self::assertSame(409, $e->getStatusCode());
        }
        self::assertSame(1, DB::table('op_specialcase')->count());
    }

    public function test_unchanged_legacy_values_survive_an_unrelated_edit(): void
    {
        $id = $this->cases->save($this->account, $this->project, $this->input(), $this->version());
        DB::table('op_specialcase')->where('id', $id)->update(['c_Reason' => '', 'c_Date' => '1999-01-01 00:00:00']);
        $this->cases->save($this->account, $this->project, $this->input(['case_id' => $id, 'reason' => '', 'date' => '1999-01-01', 'action' => 'Changed only this']), $this->version());
        $row = DB::table('op_specialcase')->where('id', $id)->first();
        self::assertSame('', $row->c_Reason);
        self::assertSame('Changed only this', $row->c_Action);
    }

    public function test_grants_gate_reads_and_writes_and_the_http_pages_work_in_both_languages(): void
    {
        $this->actingAs($this->account);
        $url = '/en/applications/'.$this->project.'/special-cases';
        $this->get('/en/applications/'.$this->project)->assertOk()->assertSee('Special cases');
        $this->get($url)->assertOk()->assertSee('New review')->assertSee('Visit');
        $this->get('/ar/applications/'.$this->project.'/special-cases')->assertOk()->assertSee('dir="rtl"', false)->assertSee('مراجعة جديدة');
        $post = ['version' => $this->version()] + $this->input();
        $this->post($url, $post)->assertRedirect();
        $this->post($url, $post)->assertStatus(409);
        $this->post($url, ['version' => $this->version()] + $this->input(['reason' => '']))->assertSessionHasErrors('reason');
        $this->get($url)->assertOk()->assertSee('Late instalment');

        DB::table('account_city_capabilities')->where('capability', 'special_cases.manage')->update(['revoked_at' => now()]);
        $this->get($url)->assertOk()->assertSee('Late instalment')->assertDontSee('New review');
        $this->post($url, ['version' => $this->version()] + $this->input())->assertForbidden();
        DB::table('account_city_capabilities')->where('capability', 'special_cases.view')->update(['revoked_at' => now()]);
        $this->get($url)->assertForbidden();
        $this->get('/en/applications/'.$this->project)->assertOk()->assertDontSee('Special cases');
        $this->get('/en/applications/0/special-cases')->assertNotFound();
        self::assertSame(1, DB::table('op_specialcase')->count());
    }
}
