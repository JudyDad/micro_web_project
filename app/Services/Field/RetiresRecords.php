<?php

declare(strict_types=1);

namespace App\Services\Field;

use App\Models\WebAccount;

/**
 * Deletion rules shared by the field services (they provide `$access`, `$audit` and `$tables`): a record created in the web is deleted
 * outright, an imported record is only hidden (a row in `operational_deletions`) and needs the administrator-only `imported.delete`
 * capability on top of the area's own delete capability. Restoring needs the same two.
 */
trait RetiresRecords
{
    private function requireImportedDelete(WebAccount $account, int $city): void
    {
        if (! $this->access->check($account, $city, 'imported.delete')) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
    }

    /** Deletes (web record, after $beforeDelete removes its dependents) or hides (imported record). */
    private function retire(WebAccount $account, int $city, string $sourceTable, object $row, ?callable $beforeDelete = null): void
    {
        if ($this->tables->isWeb($row)) {
            if ($beforeDelete !== null) {
                $beforeDelete();
            }
            $this->tables->deleteWeb($sourceTable, $row);

            return;
        }
        $this->requireImportedDelete($account, $city);
        $this->tables->hide($sourceTable, $row, $account->id);
    }

    /** Whether the account may delete this record: its area capability, plus `imported.delete` for imported ones. */
    private function mayRetire(WebAccount $account, int $city, string $areaCapability, object $row): bool
    {
        return $this->access->check($account, $city, $areaCapability) && ($this->tables->isWeb($row) || $this->access->check($account, $city, 'imported.delete'));
    }

    private function mayRestore(WebAccount $account, int $city, string $areaCapability): bool
    {
        return $this->access->check($account, $city, $areaCapability) && $this->access->check($account, $city, 'imported.delete');
    }
}
