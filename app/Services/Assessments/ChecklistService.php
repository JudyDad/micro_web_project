<?php

declare(strict_types=1);

namespace App\Services\Assessments;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Assessment checklist (CheckList_Form). The checklist belongs to a person; a project and beneficiary select it.
 * Member rows are inserted or edited in place; deletion is not offered. Preserved rows are never rewritten unless changed.
 */
final class ChecklistService
{
    public const LOCATIONS = ['k', 't', 'c'];

    private const DATES = ['c_HVisit_Date', 'c_PVisit_Date', 'c_TeamDate', 'c_CommityDate', 'c_ProTest_Date', 'c_ProTrain_BDate', 'c_ProTrain_EDate', 'c_MngTrain_Date', 'c_RejectMsgDate'];

    private const TEXTS = [
        'c_HVisit_Note' => 300, 'c_HVisit_Time' => 50, 'c_PVisit_Note' => 300, 'c_PVisit_Time' => 50, 'c_VisitsReport' => 65535, 'c_Notes' => 4294967295,
        'c_NeedProTest_Note' => 65535, 'c_ProTest_Note' => 65535, 'c_ProTest_Result' => 65535,
        'c_ProTrain_Trainer' => 300, 'c_ProTrain_Place' => 300, 'c_ProTrain_Note' => 65535, 'c_ProTrain_Result' => 65535,
        'c_MngTrain_Trainer' => 300, 'c_MngTrain_Place' => 300, 'c_MngTrain_Result' => 65535,
    ];

    /** Flags: two-state visit/message flags and tri-state need/has flags (-1 means not applicable). */
    private const FLAGS = ['c_HasHomeVisit' => [0, 1], 'c_HasProjectVisit' => [0, 1], 'c_RejectMsg' => [0, 1], 'c_NeedProTest' => [-1, 0, 1], 'c_HasProTest' => [-1, 0, 1], 'c_HasProTrain' => [-1, 0, 1], 'c_HasMngTrain' => [-1, 0, 1]];

    private const REFERENCES = ['HVisit_Visitor1_ID', 'HVisit_Visitor2_ID', 'PVisit_Visitor1_ID', 'PVisit_Visitor2_ID', 'ProTest_User_ID'];

    /** Details emptied by the desktop when their switch is off. */
    private const DEPENDENTS = [
        'c_HasHomeVisit' => ['fields' => ['c_HVisit_Date', 'c_HVisit_Time', 'c_HVisit_Note'], 'references' => ['HVisit_Visitor1_ID', 'HVisit_Visitor2_ID']],
        'c_HasProjectVisit' => ['fields' => ['c_PVisit_Date', 'c_PVisit_Time', 'c_PVisit_Note'], 'references' => ['PVisit_Visitor1_ID', 'PVisit_Visitor2_ID']],
        'c_HasProTest' => ['fields' => ['c_ProTest_Date', 'c_ProTest_Note', 'c_ProTest_Result'], 'references' => ['ProTest_User_ID']],
        'c_HasProTrain' => ['fields' => ['c_ProTrain_Trainer', 'c_ProTrain_Place', 'c_ProTrain_BDate', 'c_ProTrain_EDate', 'c_ProTrain_Note', 'c_ProTrain_Result'], 'references' => []],
        'c_HasMngTrain' => ['fields' => ['c_MngTrain_Trainer', 'c_MngTrain_Place', 'c_MngTrain_Date', 'c_MngTrain_Result'], 'references' => []],
        'c_RejectMsg' => ['fields' => ['c_RejectMsgDate'], 'references' => []],
    ];

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables) {}

    public function fieldNames(): array
    {
        return array_merge(self::DATES, array_keys(self::TEXTS), array_keys(self::FLAGS));
    }

    public function referenceNames(): array
    {
        return self::REFERENCES;
    }

    private function authorize(WebAccount $account, int $city, bool $write = false): void
    {
        $capabilities = ['applications.view', 'people.view', 'assessments.view', ...($write ? ['assessments.manage'] : [])];
        foreach ($capabilities as $capability) {
            if (! $this->access->check($account, $city, $capability)) {
                $this->audit->record($account->id, 'access.denied', 'denied');
                abort(403);
            }
        }
    }

    private function project(int $id): object
    {
        $row = $this->tables->query('microproject')->where('p.id', $id)->first();
        abort_if($row === null, 404);

        return $row;
    }

    private function links(object $project): Collection
    {
        $mp = $this->tables->relation('person_microproject', 'MicroProject_ID');

        return $this->tables->query('person_microproject')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$mp['resolved_column'], $project->id)->where('p.'.$mp['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    /** Beneficiaries of the project. A checklist is only reachable through a beneficiary link of this project. */
    public function beneficiaries(WebAccount $account, int $projectId): Collection
    {
        $project = $this->project($projectId);
        $this->authorize($account, (int) $project->city_id);

        return $this->beneficiaryRows($project);
    }

    private function beneficiaryRows(object $project): Collection
    {
        $personRef = $this->tables->relation('person_microproject', 'Person_ID');

        return $this->links($project)->filter(fn ($link) => $link->c_PersonType === 'مستفيد' && $link->{$personRef['state_column']} === 'resolved')
            ->map(function ($link) use ($project, $personRef) {
                $person = $this->tables->find('person', (int) $link->{$personRef['resolved_column']}, (int) $project->city_id, (int) $project->source_id);
                if ($person === null) {
                    return null;
                }

                return (object) ['id' => (int) $person->id, 'label' => trim(($person->c_P_FirstName ?? '').' '.($person->c_P_LastName ?? '')).' · #'.$person->id];
            })->filter()->unique('id')->values();
    }

    private function headers(object $project, int $personId): Collection
    {
        $personRef = $this->tables->relation('checklist', 'Person_ID');

        return $this->tables->query('checklist')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$personRef['resolved_column'], $personId)->where('p.'.$personRef['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function memberRows(object $project, int $personId): Collection
    {
        $personRef = $this->tables->relation('person_checklistmembers', 'Person_ID');

        return $this->tables->query('person_checklistmembers')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$personRef['resolved_column'], $personId)->where('p.'.$personRef['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function version(Collection $headers, Collection $members): string
    {
        $strip = fn (Collection $rows) => $rows->map(fn ($row) => collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all())->all();

        return hash('sha256', json_encode([$strip($headers), $strip($members)], JSON_THROW_ON_ERROR));
    }

    /** Catalog members offered per location, with their position names. */
    public function catalog(object $project): array
    {
        $positionRef = $this->tables->relation('checklist_member', 'Position_ID');
        $positions = $this->tables->catalog('checklist_position', (int) $project->city_id, (int) $project->source_id)->keyBy('id');
        $result = array_fill_keys(self::LOCATIONS, []);
        foreach ($this->tables->catalog('checklist_member', (int) $project->city_id, (int) $project->source_id) as $member) {
            $position = $member->{$positionRef['state_column']} === 'resolved' ? $positions->get($member->{$positionRef['resolved_column']}) : null;
            if ($position === null || ! in_array($position->c_Type, self::LOCATIONS, true)) {
                continue;
            }
            $result[$position->c_Type][] = (object) ['id' => (int) $member->id, 'label' => trim(($member->c_Name ?? '').' · '.($position->c_Name ?? ''), ' ·'), 'active' => (int) $member->c_IsActive !== 0];
        }

        return $result;
    }

    /** All members of a project's checklist catalog by canonical id, for reference validation. */
    private function memberLocation(object $project, int $memberId): ?string
    {
        foreach ($this->catalog($project) as $location => $members) {
            foreach ($members as $member) {
                if ($member->id === $memberId) {
                    return $location;
                }
            }
        }

        return null;
    }

    public function show(WebAccount $account, int $projectId, int $personId, ?int $headerId = null): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $people = $this->beneficiaryRows($project);
        abort_unless($people->contains('id', $personId), 404);
        $headers = $this->headers($project, $personId);
        $members = $this->memberRows($project, $personId);
        $header = $headerId !== null ? $headers->firstWhere('id', $headerId) : ($headers->count() === 1 ? $headers->first() : null);
        abort_if($headerId !== null && $header === null, 404);
        $this->audit->record($account->id, 'assessments.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

        return [
            'project' => $project, 'people' => $people, 'personId' => $personId, 'headers' => $headers, 'header' => $header,
            'members' => $members, 'version' => $this->version($headers, $members), 'catalog' => $this->catalog($project),
            'canManage' => $this->access->check($account, $city, 'assessments.manage'),
            'needsHeaderChoice' => $headerId === null && $headers->count() > 1,
        ];
    }

    /**
     * Saves one person's checklist. $members is a list of {id?, location, member_id, opinion, notes, name_notes, role_notes}.
     * Existing member rows not mentioned are left untouched.
     */
    public function save(WebAccount $account, int $projectId, int $personId, ?int $headerId, array $fields, array $references, array $members, string $version): int
    {
        return DB::transaction(function () use ($account, $projectId, $personId, $headerId, $fields, $references, $members, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            abort_unless($this->beneficiaryRows($project)->contains('id', $personId), 422, 'Select a beneficiary of this project.');
            DB::table('op_person')->where('id', $personId)->lockForUpdate()->first();
            $headers = $this->headers($project, $personId);
            $existing = $this->memberRows($project, $personId);
            abort_unless(hash_equals($this->version($headers, $existing), $version), 409, 'Checklist changed. Reload before saving.');
            abort_if($headers->count() > 1 && $headerId === null, 422, 'Several checklists exist for this person; choose one.');
            $header = $headerId !== null ? $headers->firstWhere('id', $headerId) : $headers->first();
            abort_if($headerId !== null && $header === null, 404);

            $payload = $this->headerPayload($project, $header, $fields, $references);
            if ($header === null) {
                $headerId = $this->tables->insert('checklist', $city, $source, $payload + $this->tables->link('checklist', 'Person_ID', $personId));
            } else {
                $payload = $this->changed($header, $payload);
                if ($payload !== []) {
                    DB::table('op_checklist')->where('id', $header->id)->update($payload);
                }
                $headerId = (int) $header->id;
            }
            $this->saveMembers($project, $personId, $existing, $members);
            $this->audit->record($account->id, 'assessments.saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

            return $headerId;
        });
    }

    private function headerPayload(object $project, ?object $header, array $fields, array $references): array
    {
        // A form re-submits every field; values equal to the stored row are untouched and are not revalidated (legacy sentinels survive).
        if ($header !== null) {
            $fields = $this->changed($header, $fields);
        }
        $unknown = array_diff(array_keys($fields), $this->fieldNames());
        if ($unknown !== [] || array_diff(array_keys($references), self::REFERENCES)) {
            throw ValidationException::withMessages(['fields' => 'Unsupported checklist field.']);
        }
        $rules = [];
        foreach (self::DATES as $name) {
            $rules[$name] = ['nullable', 'date_format:Y-m-d'];
        }
        foreach (self::TEXTS as $name => $max) {
            $rules[$name] = ['nullable', 'string', 'max:'.$max];
        }
        foreach (self::FLAGS as $name => $allowed) {
            $rules[$name] = ['nullable', 'integer', Rule::in($allowed)];
        }
        // Only supplied values are validated; untouched legacy values (sentinels included) are kept as stored.
        $supplied = Validator::make($fields, array_intersect_key($rules, $fields))->validate();
        foreach (array_merge(self::DATES, array_keys(self::FLAGS)) as $name) {
            if (($supplied[$name] ?? null) === '') {
                $supplied[$name] = null;
            }
        }
        $effective = [];
        foreach ($this->fieldNames() as $name) {
            $effective[$name] = array_key_exists($name, $supplied) ? $supplied[$name] : ($header->{$name} ?? null);
        }
        $given = [];
        $refs = [];
        foreach (self::REFERENCES as $key) {
            $relation = $this->tables->relation('checklist', $key);
            if (array_key_exists($key, $references)) {
                $given[$key] = $references[$key] === null || $references[$key] === '' ? null : (int) $references[$key];
                if ($given[$key] !== null && $this->memberLocation($project, $given[$key]) === null) {
                    throw ValidationException::withMessages(['references.'.$key => 'Select a checklist member from this city.']);
                }
            }
            $refs[$key] = array_key_exists($key, $given) ? $given[$key]
                : ($header !== null && $header->{$relation['state_column']} === 'resolved' ? (int) $header->{$relation['resolved_column']} : null);
        }
        $sender = $this->tables->relation('checklist', 'RejectMsgSender_ID');
        $this->validateSections($effective, $refs, $header !== null && $header->{$sender['state_column']} === 'resolved');
        $payload = $supplied;
        foreach (self::DEPENDENTS as $flag => $dependents) {
            // Only a switch changed by this save empties its section; unrelated saves leave stored details alone.
            if (! array_key_exists($flag, $supplied) || (int) ($effective[$flag] ?? 0) === 1) {
                continue;
            }
            foreach ($dependents['fields'] as $name) {
                if (($effective[$name] ?? null) !== null) {
                    $payload[$name] = null;
                }
            }
            foreach ($dependents['references'] as $key) {
                $given[$key] = null;
                $refs[$key] = null;
            }
        }
        foreach ($given as $key => $id) {
            $relation = $this->tables->relation('checklist', $key);
            $stored = $header !== null && $header->{$relation['state_column']} === 'resolved' ? (int) $header->{$relation['resolved_column']} : null;
            if ($id !== $stored || ($header === null && $id !== null)) {
                $payload += $this->tables->link('checklist', $key, $id);
            }
        }

        return $payload;
    }

    /** Rules of CheckList_Form.check_data. */
    private function validateSections(array $values, array $refs, bool $hasSender): void
    {
        $required = [];
        // The sender is a historical source user and cannot be chosen for a web actor; a rejection message needs an existing one.
        if ((int) $values['c_RejectMsg'] === 1 && ! $hasSender) {
            $required['c_RejectMsg'] = 'A rejection message needs a recorded sender.';
        }
        if ((int) $values['c_HasHomeVisit'] === 1 && $refs['HVisit_Visitor1_ID'] === null) {
            $required['references.HVisit_Visitor1_ID'] = 'Select at least one visitor for the home visit.';
        }
        if ((int) $values['c_HasProjectVisit'] === 1 && $refs['PVisit_Visitor1_ID'] === null) {
            $required['references.PVisit_Visitor1_ID'] = 'Select at least one visitor for the project visit.';
        }
        if ((int) $values['c_HasProTest'] === 1 && (blank($values['c_ProTest_Note']) || $refs['ProTest_User_ID'] === null)) {
            $required['c_ProTest_Note'] = 'Complete the professional test information.';
        }
        if ((int) $values['c_HasProTrain'] === 1 && (blank($values['c_ProTrain_Trainer']) || blank($values['c_ProTrain_Place']) || blank($values['c_ProTrain_Note']))) {
            $required['c_ProTrain_Trainer'] = 'Complete the professional training information.';
        }
        if ((int) $values['c_HasMngTrain'] === 1 && (blank($values['c_MngTrain_Trainer']) || blank($values['c_MngTrain_Place']))) {
            $required['c_MngTrain_Trainer'] = 'Complete the management training information.';
        }
        if ($required !== []) {
            throw ValidationException::withMessages($required);
        }
    }

    /** Only values that differ from the stored row are written, so exact legacy text survives. */
    private function changed(object $row, array $payload): array
    {
        $blank = fn ($value) => $value === null || $value === '';

        return array_filter($payload, fn ($value, $key) => ! property_exists($row, $key)
            || ! ($blank($value) && $blank($row->{$key}))
            && ((($value === null) !== ($row->{$key} === null)) || (string) $value !== (string) $row->{$key}), ARRAY_FILTER_USE_BOTH);
    }

    private function saveMembers(object $project, int $personId, Collection $existing, array $members): void
    {
        $memberRef = $this->tables->relation('person_checklistmembers', 'Member_ID');
        foreach ($members as $index => $input) {
            $location = $input['location'] ?? null;
            $row = Validator::make($input, [
                'id' => ['nullable', 'integer', 'min:1'], 'location' => ['required', Rule::in(self::LOCATIONS)],
                'member_id' => ['required', 'integer', 'min:1'], 'opinion' => ['nullable', 'integer', Rule::in([-1, 0, 1])],
                'notes' => ['nullable', 'string', 'max:65535'], 'name_notes' => ['nullable', 'string', 'max:400'], 'role_notes' => ['nullable', 'string', 'max:500'],
            ])->validate();
            if ($this->memberLocation($project, (int) $row['member_id']) !== $location) {
                throw ValidationException::withMessages(['members.'.$index.'.member_id' => 'The member does not belong to this list.']);
            }
            $payload = [
                'c_Location' => $location,
                'c_Opinion' => $location === 'k' ? -1 : (int) ($row['opinion'] ?? -1),
                'c_Notes' => $row['notes'] ?? null,
            ] + ($location === 'k' ? ['c_NameNotes' => $row['name_notes'] ?? null, 'c_RoleNotes' => $row['role_notes'] ?? null] : [])
              + $this->tables->link('person_checklistmembers', 'Member_ID', (int) $row['member_id']);
            if (($row['id'] ?? null) === null) {
                $this->tables->insert('person_checklistmembers', (int) $project->city_id, (int) $project->source_id, $payload + [
                    'c_created_at' => now()->format('Y-m-d H:i:s'),
                ] + $this->tables->link('person_checklistmembers', 'Person_ID', $personId));

                continue;
            }
            $current = $existing->firstWhere('id', (int) $row['id']);
            abort_if($current === null, 422, 'Checklist member row does not belong to this person.');
            abort_unless($current->c_Location === $location, 422, 'A member row cannot move between lists.');
            $changes = $this->changed($current, $payload);
            if ($changes !== []) {
                DB::table('op_person_checklistmembers')->where('id', $current->id)->update($changes);
            }
        }
    }
}
