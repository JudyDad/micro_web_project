<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AccountAuditTrail;
use App\Services\Staff\LegacyStaffAdopter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class AdoptLegacyStaff extends Command
{
    protected $signature = 'staff:adopt-legacy {--city= : Only this city id} {--dry-run : Report what would be copied and change nothing} {--decision= : Evidence reference recorded with each grant}';

    protected $description = 'Copy roles, duties, e-mail, task responsibilities and notices of linked desktop users into the web staff model';

    public function handle(LegacyStaffAdopter $adopter): int
    {
        $city = $this->option('city') === null ? null : (int) $this->option('city');
        $decision = (string) ($this->option('decision') ?: 'adopt:legacy:'.now('UTC')->format('Ymd'));
        if (! AccountAuditTrail::validDecision($decision)) {
            $this->error('The decision reference may use letters, digits and _ : / . - only.');

            return self::FAILURE;
        }
        if ($city !== null && ! DB::table('cities')->where('id', $city)->exists()) {
            $this->error('Unknown city.');

            return self::FAILURE;
        }
        DB::beginTransaction();
        try {
            $result = $adopter->adopt($city, $decision);
            $this->option('dry-run') ? DB::rollBack() : DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        foreach ($result as $what => $count) {
            $this->line(sprintf('%-9s %d', $what, $count));
        }
        $this->info($this->option('dry-run') ? 'Dry run: nothing was changed.' : 'Done.');

        return self::SUCCESS;
    }
}
