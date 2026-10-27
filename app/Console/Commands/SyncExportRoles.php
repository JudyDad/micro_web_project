<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\AccountProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Brings existing accounts in line with the export rules of 27 October 2026: the cashier duty is now the financial role, and the roles that
 * hold the new export and attachment-category grants (`finance.export`, `reports.export`, `attachments.personal|legal|financial`) give them
 * to the accounts that already hold the role. Only the new grants are added, never anything an administrator revoked by hand. Safe to repeat.
 */
final class SyncExportRoles extends Command
{
    protected $signature = 'staff:sync-export-roles {--dry-run : Report what would change and change nothing} {--decision= : Evidence reference recorded with each grant}';

    protected $description = 'Merge the cashier role into financial and grant the new export and attachment-category grants to the roles that carry them';

    public function handle(AccessRoles $roles, AccountProvisioner $provisioner): int
    {
        $decision = (string) ($this->option('decision') ?: 'role-sync:export-rules:'.now('UTC')->format('Ymd'));
        if (! AccountAuditTrail::validDecision($decision)) {
            $this->error('The decision reference may use letters, digits and _ : / . - only.');

            return self::FAILURE;
        }
        $counts = ['cashier roles merged' => 0, 'grants added' => 0];
        DB::beginTransaction();
        try {
            foreach (DB::table('account_city_roles')->where('role', 'cashier')->whereNull('revoked_at')->get() as $row) {
                $account = WebAccount::find($row->account_id);
                if ($account === null) {
                    continue;
                }
                // The cashier assignment ends and the account holds the financial role instead (its grants are given in full).
                DB::table('account_city_roles')->where('id', $row->id)->update(['revoked_at' => now('UTC')->format('Y-m-d H:i:s.u')]);
                $roles->assign($account, (int) $row->city_id, 'financial', $decision);
                $counts['cashier roles merged']++;
            }
            foreach (DB::table('account_city_roles')->whereNull('revoked_at')->get() as $row) {
                $account = WebAccount::find($row->account_id);
                if ($account === null || ! isset(AccessRoles::ROLES[$row->role])) {
                    continue;
                }
                foreach (array_intersect(AccountAccess::EXPORT_CAPABILITIES, AccessRoles::capabilities($row->role)) as $capability) {
                    $held = DB::table('account_city_capabilities')->where('account_id', $row->account_id)->where('city_id', $row->city_id)->where('capability', $capability)->exists();
                    if (! $held) {
                        $provisioner->grant($account, (int) $row->city_id, $capability, $decision);
                        $counts['grants added']++;
                    }
                }
            }
            $this->option('dry-run') ? DB::rollBack() : DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        foreach ($counts as $what => $count) {
            $this->line(sprintf('%-22s %d', $what, $count));
        }
        $this->info($this->option('dry-run') ? 'Dry run: nothing was changed.' : 'Done.');

        return self::SUCCESS;
    }
}
