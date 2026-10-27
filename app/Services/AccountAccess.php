<?php

namespace App\Services;

use App\Models\WebAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AccountAccess
{
    public const CAPABILITIES = ['projects.view', 'attachments.download', 'people.view', 'people.manage', 'applications.view', 'applications.manage', 'lifecycle.manage', 'lifecycle.accept', 'assessments.view', 'assessments.manage',
        'finance.view', 'finance.manage', 'executive_files.view', 'executive_files.manage', 'purchasing.view', 'purchasing.manage',
        'special_cases.view', 'special_cases.manage', 'visits.view', 'visits.manage',
        'special_cases.delete', 'visits.delete', 'me_visits.view', 'me_visits.manage', 'me_visits.delete', 'imported.delete',
        'profession_meetings.view', 'profession_meetings.manage', 'profession_meetings.delete',
        'tasks.view', 'tasks.manage', 'tasks.manage_all', 'notifications.view', 'notifications.send', 'accounts.manage',
        // Export rules (owner decision of 27 Oct 2026): money exports, every other data export, and the three attachment categories.
        'finance.export', 'reports.export', 'attachments.personal', 'attachments.legal', 'attachments.financial'];

    /** The capabilities added by the export rules; roles that already exist receive them through `staff:sync-export-roles`. */
    public const EXPORT_CAPABILITIES = ['finance.export', 'reports.export', 'attachments.personal', 'attachments.legal', 'attachments.financial'];

    /** Image category (as the desktop's `imagetype.Category` spells it) → the grant that opens it. */
    public const ATTACHMENT_CATEGORIES = ['ثبوتيات شخصية - Personal' => 'attachments.personal', 'أوراق قانونية - Legal' => 'attachments.legal', 'أوراق مالية - Financial' => 'attachments.financial'];

    public function active(WebAccount $account): bool
    {
        return WebAccount::whereKey($account->getKey())->whereNull('disabled_at')->exists();
    }

    public function cities(WebAccount $account, string $capability = 'projects.view'): Collection
    {
        if (! in_array($capability, self::CAPABILITIES, true) || ! $this->active($account)) {
            return collect();
        }

        return DB::table('cities as c')->join('account_city_capabilities as g', 'g.city_id', '=', 'c.id')
            ->where('g.account_id', $account->getKey())->where('g.capability', $capability)->whereNull('g.revoked_at')
            ->orderBy('c.id')->get(['c.id', 'c.name_en', 'c.name_ar']);
    }

    /** Cities where the account holds every one of the capabilities. */
    public function citiesWithAll(WebAccount $account, array $capabilities): Collection
    {
        $cities = null;
        foreach ($capabilities as $capability) {
            $held = $this->cities($account, $capability);
            $cities = $cities === null ? $held : $cities->filter(fn ($city) => $held->contains('id', $city->id));
        }

        return ($cities ?? collect())->values();
    }

    public function check(WebAccount $account, int $city, string $capability = 'projects.view'): bool
    {
        return in_array($capability, self::CAPABILITIES, true) && $this->active($account)
            && DB::table('account_city_capabilities')->where('account_id', $account->getKey())
                ->where('city_id', $city)->where('capability', $capability)->whereNull('revoked_at')->exists();
    }
}
