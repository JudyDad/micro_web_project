<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "Attachments" (`Search_Form.bind_Images`): the images recorded for each project, by type. This lists what exists only: the file path and
 * the file itself are never in a report (files open through the separate, audited download path).
 *
 * As the desktop narrowed image types by role (owner decision of 27 Oct 2026), an account sees only the images whose category it holds the
 * grant for in the city: `attachments.personal`, `attachments.legal` or `attachments.financial`. The same grants decide which images can be
 * downloaded. A type of an unknown category is listed only to an account holding all three.
 */
final class AttachmentSearch extends SearchMode
{
    use FiltersProjects;
    use ReadsProjects;

    public function key(): string
    {
        return 'attachments';
    }

    protected function capabilities(): array
    {
        return ['attachments.download', 'projects.view', 'applications.view'];
    }

    /** Cities where the account may list attachments and holds at least one category grant. */
    public function cities(WebAccount $account): Collection
    {
        return parent::cities($account)->filter(fn ($city) => $this->grantedCategories($account, (int) $city->id) !== [])->values();
    }

    /** @return list<string> the image categories the account may see in the city */
    private function grantedCategories(WebAccount $account, int $city): array
    {
        return array_keys(array_filter(AccountAccess::ATTACHMENT_CATEGORIES, fn (string $grant) => $this->access->check($account, $city, $grant)));
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request'];
    }

    public function headings(): array
    {
        return ['City', 'Project number', 'Beneficiary', 'Career', 'Image type', 'Image type (English)', 'Category'];
    }

    public function numeric(): array
    {
        return [1];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $project = $this->relationOf('image', 'MicroProject_ID');
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $query = $this->tables->query('image')->whereIn('p.city_id', $cityIds)
            ->join('op_microproject as m', fn ($j) => $this->scoped('m', 'p')($j->on('m.id', '=', 'p.'.$project['resolved_column'])->where('p.'.$project['state_column'], 'resolved')))
            ->leftJoin('op_person_microproject as l', fn ($j) => $this->scoped('l', 'm')($j->on('l.'.$linkProject['resolved_column'], '=', 'm.id')->where('l.'.$linkProject['state_column'], 'resolved')))
            ->leftJoin('op_person as pe', fn ($j) => $this->scoped('pe', 'l')($j->on('pe.id', '=', 'l.'.$linkPerson['resolved_column'])->where('l.'.$linkPerson['state_column'], 'resolved')))
            ->addSelect(['m.c_MP_ID as project_number', 'pe.c_P_FirstName', 'pe.c_P_LastName', 'pe.c_P_FatherName', 'p.'.$this->relationOf('image', 'ImageType_ID')['resolved_column'].' as type_id'])
            ->whereRaw('('.$this->currentStateSql().') is not null');
        $this->selectProjectRefs($query, 'm', ['career_id' => 'Career_ID']);
        // Per city, only the image types whose category the account holds the grant for; types of an unknown category need all three grants.
        $typeRef = $this->relationOf('image', 'ImageType_ID')['resolved_column'];
        $query->where(function (Builder $any) use ($cityIds, $typeRef) {
            foreach ($cityIds as $city) {
                $granted = $this->grantedCategories($this->actor, $city);
                $all = count($granted) === count(AccountAccess::ATTACHMENT_CATEGORIES);
                $types = $this->catalog('imagetype', [$city])->filter(fn ($type) => in_array((string) ($type->c_Category ?? ''), $granted, true) || ($all && ! isset(AccountAccess::ATTACHMENT_CATEGORIES[(string) ($type->c_Category ?? '')])))->pluck('id')->all();
                $any->orWhere(fn (Builder $q) => $q->where('p.city_id', $city)->whereIn('p.'.$typeRef, $types === [] ? [0] : $types));
            }
        });
        $this->applyProjectFilters($query, $cityIds, $filters);

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderBy('m.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id')->orderBy('l.id');
    }

    protected function lines(Collection $rows): array
    {
        $cities = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $careers = $this->catalog('career', $cities);
        $types = $this->catalog('imagetype', $cities);

        return $rows->map(fn ($row) => [
            $row->city_name_en, $row->project_number, $this->personName($row, true), $this->nameOf($careers, $row->career_id), $this->nameOf($types, $row->type_id), $this->nameOf($types, $row->type_id, 'c_Name_en'),
            $this->nameOf($types, $row->type_id, 'c_Category'),
        ])->all();
    }
}
