<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "Profession Meetings" (`Search_Form.bind_ProfessionMeetings`): meetings with career, category, place and attendance, newest first,
 * filtered by meeting date. The registrars' names are not shown: the desktop `user` table is not part of the web data model.
 */
final class MeetingSearch extends SearchMode
{
    use ReadsProjects;

    public function key(): string
    {
        return 'meetings';
    }

    protected function capabilities(): array
    {
        return ['profession_meetings.view'];
    }

    public function filters(): array
    {
        return ['meeting'];
    }

    public function headings(): array
    {
        return ['City', 'Meeting number', 'Meeting date', 'Career', 'Career (Arabic)', 'Category', 'Sub category', 'Place', 'Invited', 'Attended', 'Absent'];
    }

    public function numeric(): array
    {
        return [1, 8, 9, 10];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $query = $this->tables->query('profession_meeting')->whereIn('p.city_id', $cityIds)
            ->addSelect(['p.'.$this->relationOf('profession_meeting', 'Career_ID')['resolved_column'].' as career_id', 'p.'.$this->relationOf('profession_meeting', 'SubCategory_ID')['resolved_column'].' as subcategory_id']);
        foreach ([['meeting_from', '>='], ['meeting_to', '<=']] as [$key, $operator]) {
            if (($filters[$key] ?? '') !== '') {
                $query->where('p.c_MeetingDate', $operator, $filters[$key]);
            }
        }

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderByDesc('p.c_MeetingDate')->orderByDesc('p.c_ID')->orderBy('p.id');
    }

    protected function lines(Collection $rows): array
    {
        $cities = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $careers = $this->catalog('career', $cities);
        $subs = $this->catalog('subcategory', $cities);
        $categories = $this->catalog('category', $cities);
        $categoryRef = $this->tables->relation('subcategory', 'Category_ID')['resolved_column'];

        return $rows->map(function ($row) use ($careers, $subs, $categories, $categoryRef) {
            $sub = $subs->get((int) ($row->subcategory_id ?? 0));

            return [
                $row->city_name_en, $row->c_ID, $this->day($row->c_MeetingDate ?? null), $this->nameOf($careers, $row->career_id, 'c_Name_en'), $this->nameOf($careers, $row->career_id),
                $sub === null ? '' : $this->nameOf($categories, $sub->{$categoryRef} ?? null), (string) ($sub->c_Name ?? ''), $row->c_Place ?? '',
                (int) ($row->c_InvitedCount ?? 0), (int) ($row->c_AttendanceCount ?? 0), (int) ($row->c_AbsenceCount ?? 0),
            ];
        })->all();
    }
}
