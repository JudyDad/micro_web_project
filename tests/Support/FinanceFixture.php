<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\WebAccount;
use App\Services\Applications\ApplicationService;
use App\Services\Finance\FinanceService;
use App\Services\Lifecycle\LifecycleService;
use Illuminate\Support\Facades\DB;

/** Invented finance data on top of the application fixture. */
final class FinanceFixture
{
    public const CAPABILITIES = ['finance.view', 'finance.manage', 'executive_files.view', 'executive_files.manage', 'purchasing.view', 'purchasing.manage'];

    public static function grant(WebAccount $account, array $cities = [1, 2], array $capabilities = self::CAPABILITIES): void
    {
        foreach ($cities as $city) {
            foreach ($capabilities as $capability) {
                DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => $city, 'capability' => $capability, 'decision_locator' => 'synthetic:p51', 'granted_at' => now('UTC')]);
            }
        }
    }

    /** A third city whose loan amounts are stored in DOUBLE columns (Homs in the approved mapping). */
    public static function addDoubleCity(WebAccount $account): int
    {
        DB::table('cities')->insert(['id' => 3, 'name_en' => 'Homs', 'name_ar' => 'مدينة تجريبية']);
        DB::table('sources')->insert(['id' => 3, 'city_id' => 3, 'name' => 'Homs']);
        DB::table('project_number_sequences')->insert(['source_id' => 3, 'next_number' => 1000]);
        foreach (['people.view', 'people.manage', 'applications.view', 'applications.manage', 'lifecycle.manage', 'lifecycle.accept', ...self::CAPABILITIES] as $capability) {
            DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => 3, 'capability' => $capability, 'decision_locator' => 'synthetic:p51', 'granted_at' => now('UTC')]);
        }

        return 3;
    }

    /** Catalog rows a finance flow needs: the loan task, donors, groups and purchase categories. */
    public static function catalog(int $city): array
    {
        $task = ApplicationFixture::row('task', $city, ['c_T_ID' => 5, 'c_T_Name' => FinanceService::LOAN_TASK, 'c_T_State' => 0, 'c_T_Type' => 'f']);
        $donors = [
            'a' => ApplicationFixture::row('donor', $city, ['c_ID' => 51, 'c_Name' => 'Synthetic donor A', 'c_Name_abb' => 'SDA']),
            'b' => ApplicationFixture::row('donor', $city, ['c_ID' => 52, 'c_Name' => 'Synthetic donor B', 'c_Name_abb' => 'SDB']),
        ];
        $groups = [
            'a' => ApplicationFixture::row('donorgroup', $city, ['c_ID' => 61, 'c_Name' => 'Group A', 'c_Donor_ID' => 51], ['Donor_ID' => $donors['a']]),
            'b' => ApplicationFixture::row('donorgroup', $city, ['c_ID' => 62, 'c_Name' => 'Group B', 'c_Donor_ID' => 52], ['Donor_ID' => $donors['b']]),
        ];
        $categories = ['tools' => ApplicationFixture::row('purchasingcategory', $city, ['c_ID' => 71, 'c_Name' => 'أدوات', 'c_Name_en' => 'Tools'])];

        return ['task' => $task, 'donors' => $donors, 'groups' => $groups, 'categories' => $categories];
    }

    /** A project in the given city already moved to Accepted. */
    public static function acceptedProject(WebAccount $account, array $intake, int $city = 1): int
    {
        $id = app(ApplicationService::class)->create($account, $city, ['c_MP_Name' => 'Synthetic finance project', 'c_MP_DateOfRequest' => '2026-10-19'], $intake['person'], $intake['references']);
        $lifecycle = app(LifecycleService::class);
        $lifecycle->transition($account, $id, $intake['states']['Accepted'], null, null, $lifecycle->panel($account, $id)['version']);

        return $id;
    }

    /** Assigns a donor directly (as the admin panel would) so a loan can be recorded. */
    public static function withDonor(WebAccount $account, int $project, int $donor): void
    {
        $finance = app(FinanceService::class);
        $finance->assignDonor($account, $project, $donor, $finance->show($account, $project)['version']);
    }

    public static function loanInput(array $overrides = []): array
    {
        return $overrides + ['currency' => 'USD', 'exchange_rate' => '15000', 'amount' => '1000', 'date' => '2026-10-20', 'returned' => '1200', 'payment' => '120', 'note' => ' exact  note ', 'receipt' => 'R-1'];
    }
}
