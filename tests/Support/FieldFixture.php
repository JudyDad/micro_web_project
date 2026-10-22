<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\WebAccount;
use Illuminate\Support\Facades\DB;

/** Invented field-operations data on top of the application fixture. */
final class FieldFixture
{
    public const CAPABILITIES = ['special_cases.view', 'special_cases.manage'];

    public static function grant(WebAccount $account, array $cities = [1, 2], array $capabilities = self::CAPABILITIES): void
    {
        foreach ($cities as $city) {
            foreach ($capabilities as $capability) {
                DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => $city, 'capability' => $capability, 'decision_locator' => 'synthetic:p52', 'granted_at' => now('UTC')]);
            }
        }
    }

    /** Special-case review types of a city; names sort so that the phone review comes first. */
    public static function specialTypes(int $city): array
    {
        return [
            'phone' => ApplicationFixture::row('specialcasetype', $city, ['c_ID' => 81, 'c_Name' => 'أ هاتفية', 'c_Name_en' => 'Phone']),
            'visit' => ApplicationFixture::row('specialcasetype', $city, ['c_ID' => 82, 'c_Name' => 'ب زيارة', 'c_Name_en' => 'Visit']),
        ];
    }
}
