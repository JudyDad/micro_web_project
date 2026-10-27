<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Facades\DB;

/**
 * The desktop's details grids ("ShowDetails_Form") that carry an export: the project's state history and a beneficiary's family
 * members. Each has the authorization of the page it comes from, and the audit record of an export.
 */
final class DetailExportService
{
    public function __construct(private LifecycleService $lifecycle, private OperationalTables $tables, private AccountAccess $access, private AccountAuditTrail $audit) {}

    /**
     * Every state of the project, newest first. The reason and the note are shown only to accounts that may manage the lifecycle (as on the page).
     *
     * @return array{0: list<string>, 1: list<list<string|int|null>>}
     */
    public function stateHistory(WebAccount $account, int $projectId): array
    {
        $panel = $this->lifecycle->panel($account, $projectId);
        $project = DB::table('op_microproject as m')->join('cities as c', 'c.id', '=', 'm.city_id')->where('m.id', $projectId)->first(['m.c_MP_ID', 'm.city_id', 'c.name_en']);
        abort_if($project === null, 404);
        $this->requireExport($account, (int) $project->city_id);
        $this->audit->record($account->id, 'reports.exported', 'succeeded', (int) $project->city_id);
        $rows = $panel['history']->map(fn ($row) => [
            $project->name_en, $project->c_MP_ID, app()->isLocale('ar') ? ($row->state_name_ar ?? $row->state_name_en) : ($row->state_name_en ?? $row->state_name_ar), substr((string) $row->c_Date, 0, 10),
            (int) $row->c_Current_State === 1 ? 'Yes' : 'No', $row->reason_name, $row->c_Note,
        ])->all();

        return [['City', 'Project number', 'State', 'State date', 'Current', 'Reason', 'Note'], $rows];
    }

    /** Details exports need `reports.export` in the city (the desktop refused them to Guest, Lawful and Media). */
    private function requireExport(WebAccount $account, int $city): void
    {
        if (! $this->access->check($account, $city, 'reports.export')) {
            $this->audit->record($account->id, 'access.denied', 'denied', $city);
            abort(403);
        }
    }

    /**
     * The members of the beneficiary's family (every person whose family provider is the beneficiary), with family book, relation, marital
     * status, work and date of birth. Needs `people.view` in the person's city.
     *
     * @return array{0: list<string>, 1: list<list<string|int|null>>}
     */
    public function familyMembers(WebAccount $account, int $personId): array
    {
        $person = $this->tables->query('person')->where('p.id', $personId)->first();
        abort_if($person === null, 404);
        $city = (int) $person->city_id;
        if (! $this->access->check($account, $city, 'people.view')) {
            $this->audit->record($account->id, 'access.denied', 'denied', $city);
            abort(403);
        }
        $this->requireExport($account, $city);
        $source = (int) $person->source_id;
        $provider = $this->tables->relation('person_family', 'P_Provider_ID');
        $memberRef = $this->tables->relation('person_family', 'Person_ID');
        $familyRef = $this->tables->relation('person_family', 'Family_ID');
        $relationRef = $this->tables->relation('person_family', 'Relation_ID');
        $maritalRef = $this->tables->relation('person', 'P_MaritalStatus_ID');
        $relations = $this->tables->catalog('relation', $city, $source)->keyBy('id');
        $marital = $this->tables->catalog('maritalstatus', $city, $source)->keyBy('id');
        $links = $this->tables->query('person_family')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.'.$provider['resolved_column'], $personId)
            ->where('p.'.$provider['state_column'], 'resolved')->orderBy('p.id')->get();
        $members = $this->tables->query('person')->whereIn('p.id', $links->pluck($memberRef['resolved_column'])->filter()->unique()->all())->get()->keyBy('id');
        $families = $this->tables->query('family')->whereIn('p.id', $links->pluck($familyRef['resolved_column'])->filter()->unique()->all())->get()->keyBy('id');
        $this->audit->record($account->id, 'reports.exported', 'succeeded', $city);

        $rows = $links->map(function ($link) use ($members, $families, $relations, $marital, $memberRef, $familyRef, $relationRef, $maritalRef) {
            $member = $members->get((int) $link->{$memberRef['resolved_column']});
            $family = $link->{$familyRef['state_column']} === 'resolved' ? $families->get((int) $link->{$familyRef['resolved_column']}) : null;

            return [
                $link->city_name_en, $member?->c_P_ID, $link->c_Family_ID, $family?->c_F_Number, $member === null ? '' : trim(($member->c_P_FirstName ?? '').' '.($member->c_P_LastName ?? '').' ابن/ة '.($member->c_P_FatherName ?? '')),
                $member?->c_P_MotherName, $member === null ? '' : (string) ($marital->get((int) ($member->{$maritalRef['resolved_column']} ?? 0))->c_Name ?? ''),
                (string) ($relations->get((int) ($link->{$relationRef['resolved_column']} ?? 0))->c_Name ?? ''), $link->c_Work_Name,
                $member === null || ($member->c_P_DOB ?? '') === '' || str_starts_with((string) $member->c_P_DOB, '0000') ? null : substr((string) $member->c_P_DOB, 0, 10),
            ];
        })->all();

        return [['City', 'Person number', 'Family number', 'Family book number', 'Name', 'Mother', 'Marital status', 'Relation', 'Work', 'Date of birth'], $rows];
    }
}
