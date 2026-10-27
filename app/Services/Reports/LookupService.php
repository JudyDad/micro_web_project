<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Generator;
use Illuminate\Support\Collection;

/**
 * The lookup (reference) tables the desktop's administration forms list and export: streets, donors and groups, aid sources, educations,
 * works, state reasons and the other catalogs. Read only: the web has no lookup editor. A linked value (a group's donor, a career's
 * sub category) is shown by name. Needs `applications.manage` in a city (the data-entry and administrator roles, which are the roles
 * that reach the desktop's lookup forms); only that city's rows are listed.
 */
final class LookupService
{
    public const TABLES = [
        'street', 'aidsource', 'donor', 'donorgroup', 'education', 'educationtype', 'educationlevel', 'work', 'worktype', 'statereason', 'state', 'career', 'category', 'subcategory',
        'fundtype', 'microprojecttype', 'microprojectsubtype', 'maritalstatus', 'parish', 'priest', 'relation', 'personstate', 'loss', 'illness', 'asset', 'assettype', 'assetbenefits',
        'goal', 'aid', 'aidperiod', 'powersource', 'medicalneeds', 'imagetype', 'specialcasetype', 'checklist_member', 'purchasingcategory',
    ];

    private const NAME_COLUMNS = ['c_Name', 'c_Name_en', 'c_W_Name', 'c_Priest_Name', 'c_Name_ar', 'c_F_Number'];

    private const TECHNICAL = ['id', 'city_id', 'source_id', 'source_table_id', 'shared_group_id', 'shared_row_id', 'shared_snapshot_id'];

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables) {}

    /** Cities where the account may list the lookups. */
    public function cities(WebAccount $account): Collection
    {
        return $this->access->cities($account, 'applications.manage');
    }

    /** @return list<string> */
    public static function tables(): array
    {
        return self::TABLES;
    }

    /**
     * @return array{lookups: list<array{table: string, count: int}>, cities: Collection}
     */
    public function index(WebAccount $account, array $filters): array
    {
        [$permitted, $ids] = $this->scope($account, $filters, 'lookups.listed');

        return ['lookups' => array_map(fn (string $table) => ['table' => $table, 'count' => $this->tables->query($table)->whereIn('p.city_id', $ids)->count()], self::TABLES), 'cities' => $permitted];
    }

    /**
     * The table's headings and every row of the permitted cities (all of them, in city and entry order) for the Excel/CSV export.
     * Authorization and the audit record happen here, before the first byte is sent.
     *
     * @return array{0: list<string>, 1: Generator<int, list<string|int|float|null>>}
     */
    public function exportRows(WebAccount $account, string $table, array $filters): array
    {
        abort_unless(in_array($table, self::TABLES, true), 404);
        [, $ids] = $this->scope($account, $filters, 'reports.exported');
        $relations = [];
        foreach ($this->tables->relationships($table) as $relation) {
            $relations['c_'.$relation['child_columns'][0]] = $relation;
        }
        $columns = [];
        foreach ($this->tables->table($table)['columns'] as $column) {
            $name = $column['name'];
            if (in_array($name, self::TECHNICAL, true) || str_starts_with($name, 'ref_')) {
                continue;
            }
            $columns[] = $name;
        }
        $headings = ['City', ...array_map(fn (string $name) => isset($relations[$name]) ? preg_replace('/_ID\z/i', '', substr($name, 2)) : ltrim(str_replace('_', ' ', preg_replace('/\Ac_/', '', $name))), $columns)];

        $rows = (function () use ($table, $ids, $columns, $relations) {
            $parents = [];
            foreach ($ids as $city) {
                $source = $this->tables->source($city);
                foreach ($this->tables->catalog($table, $city, $source) as $row) {
                    $line = [$row->city_name_en];
                    foreach ($columns as $name) {
                        if (isset($relations[$name])) {
                            $relation = $relations[$name];
                            $id = $row->{$relation['state_column']} === 'resolved' ? $row->{$relation['resolved_column']} : null;
                            $parents[$relation['parent_source_table'].':'.$city] ??= $this->tables->catalog($relation['parent_source_table'], $city, $source)->keyBy('id');
                            $parent = $id === null ? null : $parents[$relation['parent_source_table'].':'.$city]->get((int) $id);
                            $line[] = $parent === null ? ($row->{$name} ?? null) : $this->display($parent);
                        } else {
                            $line[] = $row->{$name} ?? null;
                        }
                    }
                    yield $line;
                }
            }
        })();

        return [$headings, $rows];
    }

    private function display(object $row): string
    {
        foreach (self::NAME_COLUMNS as $column) {
            if (isset($row->{$column}) && $row->{$column} !== '') {
                return (string) $row->{$column};
            }
        }

        return (string) $row->id;
    }

    /** @return array{0: Collection, 1: list<int>} */
    private function scope(WebAccount $account, array $filters, string $event): array
    {
        $permitted = $this->cities($account);
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $selected = array_values(array_intersect(array_map('intval', $filters['cities'] ?? []), $permitted->pluck('id')->map(fn ($id) => (int) $id)->all()));
        $this->audit->record($account->id, $event, 'succeeded');

        return [$permitted, $selected !== [] ? $selected : $permitted->pluck('id')->map(fn ($id) => (int) $id)->all()];
    }
}
