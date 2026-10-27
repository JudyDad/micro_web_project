<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Generator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * One desktop search report (`Search_Form` mode): the permitted cities, a filtered row query, and the lines shown on screen and exported.
 * The page and the export build their rows from the same query, so what is exported is exactly what the filters select, all pages.
 */
abstract class SearchMode
{
    public const PER_PAGE = 50;

    /** The account asking for the current page or export, for modes whose rows depend on the account's grants. */
    protected ?WebAccount $actor = null;

    /** @var array<string, Collection> catalog rows by table, keyed by entity id */
    private array $catalogs = [];

    public function __construct(protected AccountAccess $access, protected AccountAuditTrail $audit, protected OperationalTables $tables) {}

    abstract public function key(): string;

    /** @return list<string> city capabilities the account needs, all of them, for a city to be searchable */
    abstract protected function capabilities(): array;

    /** @return list<string> */
    abstract public function headings(): array;

    /** @return list<string> the filter inputs this mode understands: q, state, donor, group, request, loan */
    abstract public function filters(): array;

    /** @return list<int> zero-based columns written as numbers in a workbook */
    public function numeric(): array
    {
        return [];
    }

    /** The filtered query over this mode's main table, restricted to the given cities. */
    abstract protected function query(array $cityIds, array $filters): Builder;

    abstract protected function order(Builder $query): Builder;

    /**
     * @param  Collection<int, object>  $rows  one chunk of the query's rows
     * @return list<list<string|int|float|null>> one line per row, in the order of headings()
     */
    abstract protected function lines(Collection $rows): array;

    /** Grants an export of this mode needs in a city, on top of the mode's own: money modes add `finance.export`. */
    protected function exportCapabilities(): array
    {
        return ['reports.export'];
    }

    /** Cities where the account holds every capability the mode needs. */
    public function cities(WebAccount $account): Collection
    {
        $cities = null;
        foreach ($this->capabilities() as $capability) {
            $ids = $this->access->cities($account, $capability)->pluck('id')->all();
            $cities = $cities === null ? $this->access->cities($account, $capability) : $cities->filter(fn ($city) => in_array($city->id, $ids, true));
        }

        return ($cities ?? collect())->values();
    }

    /**
     * Option lists for the filter form: catalog names across the given cities.
     *
     * @return array{states: list<string>, donors: list<string>, groups: list<string>, classification: array<string, list<string>>}
     */
    public function options(Collection $cities): array
    {
        $ids = $cities->pluck('id')->map(fn ($id) => (int) $id)->all();
        $names = fn (string $table, string $column) => $this->catalog($table, $ids)->pluck($column)->filter()->unique()->sort()->values()->all();

        $none = ['states' => [], 'donors' => [], 'groups' => [], 'classification' => []];
        if (! in_array('state', $this->filters(), true)) {
            return $none;
        }
        $classification = [];
        if (in_array('classify', $this->filters(), true)) {
            foreach (['fund' => 'fundtype', 'type' => 'microprojecttype', 'subtype' => 'microprojectsubtype', 'category' => 'category', 'subcategory' => 'subcategory', 'career' => 'career'] as $key => $table) {
                $classification[$key] = $names($table, 'c_Name');
            }
        }

        return ['states' => $names('state', 'c_Name_en'), 'donors' => $names('donor', 'c_Name'), 'groups' => $names('donorgroup', 'c_Name'), 'classification' => $classification];
    }

    /** @return array{rows: LengthAwarePaginator, cities: Collection, total: int} */
    public function page(WebAccount $account, array $filters): array
    {
        [$query, $cities] = $this->prepare($account, $filters, 'reports.search');
        $rows = $this->order($query)->paginate(self::PER_PAGE)->withQueryString();
        $rows->setCollection(collect($this->lines($rows->getCollection())));

        return ['rows' => $rows, 'cities' => $cities, 'total' => $rows->total()];
    }

    /**
     * Every matching row (not one page) for the Excel/CSV export. Authorization and the audit record happen here, before the first
     * byte is sent; only the lines are streamed.
     *
     * @return Generator<int, list<string|int|float|null>>
     */
    public function export(WebAccount $account, array $filters): Generator
    {
        [$query] = $this->prepare($account, $filters, 'reports.exported', true);
        $query = $this->order($query);

        return (function () use ($query) {
            foreach ($query->lazy(200)->chunk(200) as $chunk) {
                yield from $this->lines(collect($chunk->all()));
            }
        })();
    }

    /** @return array{0: Builder, 1: Collection} */
    private function prepare(WebAccount $account, array $filters, string $event, bool $export = false): array
    {
        $this->actor = $account;
        $permitted = $this->cities($account);
        if ($export) {
            $exportable = $this->access->citiesWithAll($account, $this->exportCapabilities());
            $permitted = $permitted->filter(fn ($city) => $exportable->contains('id', $city->id))->values();
        }
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $selected = array_values(array_intersect(array_map('intval', $filters['cities'] ?? []), $permitted->pluck('id')->map(fn ($id) => (int) $id)->all()));
        $ids = $selected !== [] ? $selected : $permitted->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->audit->record($account->id, $event, 'succeeded');

        return [$this->query($ids, $filters), $permitted];
    }

    /** Catalog rows (shared whole-table aliases projected to their values) of the given cities, keyed by entity id. */
    protected function catalog(string $table, array $cityIds): Collection
    {
        $key = $table.':'.implode(',', $cityIds);

        return $this->catalogs[$key] ??= collect($cityIds)->flatMap(fn ($city) => $this->tables->catalog($table, (int) $city, $this->tables->source((int) $city)))->keyBy('id');
    }

    /** Localized state name of a catalog row. */
    protected function stateName(?object $state): string
    {
        if ($state === null) {
            return '';
        }

        return (string) (app()->isLocale('ar') ? ($state->c_Name_ar ?? $state->c_Name_en) : ($state->c_Name_en ?? $state->c_Name_ar));
    }
}
