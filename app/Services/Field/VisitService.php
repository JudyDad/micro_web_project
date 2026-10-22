<?php

declare(strict_types=1);

namespace App\Services\Field;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Finance\Fields;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Opening and closing visits of a project (the four visit forms, `Classes/Visit.cs`, `Classes/Office_Monitoring.cs`).
 * A visit is created once per kind, edited in place and never deleted. Only changed fields are validated and written, so legacy values survive.
 * The visitors (`visit_user`, historical source users) are not recorded by the web; the acting account's username is stored as the creator.
 */
final class VisitService
{
    public const OFFICE_SLOTS = 6;

    public const QUESTION_SLOTS = 7;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables, private LifecycleService $lifecycle) {}

    private function authorize(WebAccount $account, int $city, bool $write = false, ?string $extra = null): void
    {
        foreach (['applications.view', 'visits.view', ...($write ? ['visits.manage'] : []), ...($extra === null ? [] : [$extra])] as $capability) {
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

    private function fail(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('field.errors.'.$key)]);
    }

    private function linked(object $project, string $key, string $table): ?object
    {
        $ref = $this->tables->relation('microproject', $key);

        return $project->{$ref['state_column']} === 'resolved'
            ? $this->tables->find($table, (int) $project->{$ref['resolved_column']}, (int) $project->city_id, (int) $project->source_id) : null;
    }

    /** @return array{rehab: bool, vehicle: bool} */
    public function classify(object $project): array
    {
        $type = (string) ($this->linked($project, 'MP_Type_ID', 'microprojecttype')->c_Name ?? '');
        $fund = (string) ($this->linked($project, 'MP_FundType_ID', 'fundtype')->c_Name ?? '');
        $sub = $this->linked($project, 'SubCategory_ID', 'subcategory');
        $code = $sub !== null ? $sub->c_ID : ($project->c_SubCategory_ID ?? null);

        return [
            'rehab' => str_contains($type, 'ترميم') && str_contains($fund, 'منحة'),
            'vehicle' => in_array((int) $code, [1, 2], true),
        ];
    }

    private function visits(object $project): Collection
    {
        $ref = $this->tables->relation('visit', 'MicroProject_ID');

        return $this->tables->query('visit')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$ref['resolved_column'], $project->id)->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function officeRows(Collection $visits): Collection
    {
        if ($visits->isEmpty()) {
            return collect();
        }
        $ref = $this->tables->relation('office_monitoring', 'Visit_ID');

        return $this->tables->query('office_monitoring')->whereIn('p.'.$ref['resolved_column'], $visits->pluck('id')->all())
            ->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get()->keyBy(fn ($row) => (int) $row->{$ref['resolved_column']});
    }

    private function version(Collection $visits, Collection $offices, object $project): string
    {
        $strip = fn (Collection $rows) => $rows->map(fn ($row) => collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all())->values()->all();
        $place = $this->tables->relation('microproject', 'MP_Street_ID');

        return hash('sha256', json_encode([$strip($visits), $strip($offices), [$project->{$place['resolved_column']}, $project->c_MP_AddressAfterFund ?? null]], JSON_THROW_ON_ERROR));
    }

    private function ofKind(Collection $visits, string $kind): ?object
    {
        return $visits->first(fn ($visit) => ($visit->c_Kind ?? null) === $kind);
    }

    /** Whether the project already has a visit of the phase (`Kind` contains `op` or `cl`), as the desktop's chooser disables the button. */
    private function phaseTaken(Collection $visits, string $phase): bool
    {
        $needle = $phase === 'opening' ? 'op' : 'cl';

        return $visits->contains(fn ($visit) => str_contains((string) ($visit->c_Kind ?? ''), $needle));
    }

    /** The project's visit hub: recorded visits and which kinds can still be recorded. */
    public function hub(WebAccount $account, int $projectId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $visits = $this->visits($project);
        $class = $this->classify($project);
        $canManage = $this->access->check($account, $city, 'visits.manage');
        $available = [];
        if ($canManage && ! $class['rehab']) {
            foreach (['opening', 'closing'] as $phase) {
                if (! $this->phaseTaken($visits, $phase)) {
                    $available[] = VisitKinds::kindFor($phase, $class['vehicle']);
                }
            }
        }
        $this->audit->record($account->id, 'visits.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

        $rehabVisit = $class['rehab'] ? $visits->first(fn ($visit) => ((string) $visit->c_Kind) === RehabVisitService::KIND) : null;

        return ['rehabVisit' => $rehabVisit, 'canRehab' => $class['rehab'] && $canManage, 'project' => $project, 'version' => $this->version($visits, $this->officeRows($visits), $project), 'visits' => $visits, 'rehab' => $class['rehab'], 'vehicle' => $class['vehicle'], 'available' => $available, 'canManage' => $canManage];
    }

    /** Read model of one visit questionnaire (an existing visit of the kind, or an empty form when the kind can be recorded). */
    public function form(WebAccount $account, int $projectId, string $kind): array
    {
        $definition = VisitKinds::definition($kind);
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $visits = $this->visits($project);
        $offices = $this->officeRows($visits);
        $visit = $this->ofKind($visits, $kind);
        $class = $this->classify($project);
        $canManage = $this->access->check($account, $city, 'visits.manage');
        $creatable = $visit === null && $canManage && ! $class['rehab'] && VisitKinds::kindFor($definition['phase'], $class['vehicle']) === $kind && ! $this->phaseTaken($visits, $definition['phase']);
        abort_if($visit === null && ! $creatable, 404);
        $street = $this->tables->relation('microproject', 'MP_Street_ID');
        $this->audit->record($account->id, 'visits.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

        return [
            'project' => $project, 'kind' => $kind, 'definition' => $definition, 'wording' => VisitKinds::wording($kind), 'visit' => $visit,
            'office' => $visit === null ? null : $offices->get((int) $visit->id), 'canManage' => $canManage, 'canDelete' => $visit !== null && $this->access->check($account, $city, 'visits.delete'), 'version' => $this->version($visits, $offices, $project),
            'streets' => $definition['place'] ? $this->tables->catalog('street', $city, (int) $project->source_id)->sortBy('c_Name', SORT_STRING)->values() : collect(),
            'streetId' => $project->{$street['state_column']} === 'resolved' ? (int) $project->{$street['resolved_column']} : null,
            'placeNote' => (string) ($project->c_MP_AddressAfterFund ?? ''),
        ];
    }

    /**
     * Records or edits the visit of a kind. $input: date, vehicle, street_id, place_note, indicators, q{n}_value, q{n}_text,
     * office{n}_value, office{n}_text, other_comments.
     */
    public function save(WebAccount $account, int $projectId, string $kind, array $input, string $version): int
    {
        $definition = VisitKinds::definition($kind);

        return DB::transaction(function () use ($account, $projectId, $kind, $definition, $input, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            $visits = $this->visits($project);
            $offices = $this->officeRows($visits);
            abort_unless(hash_equals($this->version($visits, $offices, $project), $version), 409, 'Visits changed. Reload before saving.');
            $visit = $this->ofKind($visits, $kind);
            $office = $visit === null ? null : $offices->get((int) $visit->id);
            $actor = mb_substr((string) $account->username, 0, 50);

            if ($visit === null) {
                $class = $this->classify($project);
                $class['rehab'] && $this->fail('visit', 'rehab');
                (VisitKinds::kindFor($definition['phase'], $class['vehicle']) === $kind) || $this->fail('visit', 'kind');
                $this->phaseTaken($visits, $definition['phase']) && $this->fail('visit', 'exists');
            }
            $payload = $this->visitPayload($definition, $visit, $input);
            $officePayload = $this->officePayload($definition, $office, $input, $office === null && $visit === null);

            if ($visit === null) {
                $visitId = $this->tables->insert('visit', $city, $source, $payload + ['c_Kind' => $kind, 'c_Type' => '', 'c_Created_By' => $actor] + $this->tables->link('visit', 'MicroProject_ID', $projectId));
                $this->tables->insert('office_monitoring', $city, $source, $officePayload + $this->tables->link('office_monitoring', 'Visit_ID', $visitId));
                $this->completeTask($project, $definition['task'], 1);
            } else {
                $visitId = (int) $visit->id;
                if ($payload !== []) {
                    DB::table('op_visit')->where('id', $visitId)->update($payload + ['c_Edited_By' => $actor]);
                }
                if ($office !== null && $officePayload !== []) {
                    DB::table('op_office_monitoring')->where('id', $office->id)->update($officePayload);
                } elseif ($office === null) {
                    $fresh = $this->officePayload($definition, null, $input, true);
                    if ($this->enteredOffice($fresh)) {
                        $this->tables->insert('office_monitoring', $city, $source, $fresh + $this->tables->link('office_monitoring', 'Visit_ID', $visitId));
                    }
                }
            }
            if ($definition['place']) {
                $this->updatePlace($project, $input);
            }
            if ($definition['finishes']) {
                $this->lifecycle->enterNamed($project, LifecycleService::FINISHED);
            }
            $this->audit->record($account->id, 'visits.saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

            return $visitId;
        });
    }

    /** Deletes a visit created in the web with its office-monitoring row and resets the phase task, as the desktop did. */
    public function delete(WebAccount $account, int $projectId, string $kind, string $version): void
    {
        $definition = VisitKinds::definition($kind);
        DB::transaction(function () use ($account, $projectId, $kind, $definition, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, false, 'visits.delete');
            $visits = $this->visits($project);
            $offices = $this->officeRows($visits);
            abort_unless(hash_equals($this->version($visits, $offices, $project), $version), 409, 'Visits changed. Reload before deleting.');
            $visit = $this->ofKind($visits, $kind);
            abort_if($visit === null, 404);
            $this->tables->isWeb($visit) || $this->fail('delete', 'imported');
            $office = $offices->get((int) $visit->id);
            if ($office !== null) {
                $this->tables->isWeb($office) || $this->fail('delete', 'imported');
                $this->tables->deleteWeb('office_monitoring', $office);
            }
            $this->tables->deleteWeb('visit', $visit);
            $this->completeTask($project, $definition['task'], 0);
            $this->audit->record($account->id, 'visits.deleted', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    /** A stored choice value: '' or missing is unanswered (-1); anything else must belong to the question's domain. */
    private function choice(array $input, string $key, ?array $domain, ?int $stored = null): int
    {
        $raw = $input[$key] ?? null;
        if ($domain === null || Fields::blank($raw)) {
            return VisitKinds::UNSET;
        }
        $value = is_string($raw) || is_int($raw) ? (string) $raw : '';
        if ($stored !== null && $value === (string) $stored) {
            return $stored; // an unchanged legacy value is kept even when it is outside today's choices
        }
        if (preg_match('/\A-?\d+\z/', $value) !== 1 || ! array_key_exists((int) $value, $domain)) {
            $this->fail($key, 'choice');
        }

        return (int) $value;
    }

    private function text(array $input, string $key, bool $present): string
    {
        $value = $present ? Fields::text($input[$key] ?? '') : '';
        mb_strlen($value) <= 65535 || $this->fail($key, 'long');

        return $value;
    }

    private function visitPayload(array $definition, ?object $visit, array $input): array
    {
        $create = $visit === null;
        $payload = [];
        $set = function (string $column, mixed $new, mixed $stored, ?callable $validate = null) use ($create, &$payload) {
            if ($create || $new !== $stored) {
                if ($validate !== null) {
                    $validate();
                }
                $payload[$column] = $new;
            }
        };

        $date = trim(Fields::text($input['date'] ?? ''));
        if ($create && $date === '') {
            $date = now()->toDateString();
        }
        $set('c_Date', $date, Fields::day($visit->c_Date ?? null), fn () => Fields::isDate($date) || $this->fail('date', 'date'));

        if ($definition['vehicle']) {
            $vehicle = Fields::text($input['vehicle'] ?? '');
            $set('c_VehicleNumber', $vehicle, (string) ($visit->c_VehicleNumber ?? ''), function () use ($vehicle) {
                $vehicle !== '' || $this->fail('vehicle', 'vehicle');
                mb_strlen($vehicle) <= 50 || $this->fail('vehicle', 'vehicle_long');
            });
        } elseif ($create) {
            $payload['c_VehicleNumber'] = '';
        }
        $set('c_Indicators', Fields::text($input['indicators'] ?? ''), (string) ($visit->c_Indicators ?? ''), fn () => $this->text($input, 'indicators', true));

        for ($n = 1; $n <= self::QUESTION_SLOTS; $n++) {
            $defined = array_key_exists($n, $definition['questions']);
            if (! $defined && ! $create) {
                continue; // an unused slot keeps whatever a legacy row holds
            }
            $domain = $defined ? $definition['questions'][$n] : null;
            $text = $this->text($input, 'q'.$n.'_text', $defined);
            $value = $this->choice($defined ? $input : [], 'q'.$n.'_value', $domain, $visit === null ? null : (int) ($visit->{'c_Ans'.$n.'_Value'} ?? VisitKinds::UNSET));
            $set('c_Ans'.$n, $text, (string) ($visit->{'c_Ans'.$n} ?? ''));
            $set('c_Ans'.$n.'_Value', $value, (int) ($visit->{'c_Ans'.$n.'_Value'} ?? VisitKinds::UNSET));
        }

        return $payload;
    }

    /** @param bool $always write every slot (a new office row); otherwise only changed, defined slots */
    private function officePayload(array $definition, ?object $office, array $input, bool $always): array
    {
        $payload = [];
        for ($n = 1; $n <= self::OFFICE_SLOTS; $n++) {
            $defined = $n <= $definition['office'];
            if (! $defined && ! $always) {
                continue;
            }
            $text = $this->text($input, 'office'.$n.'_text', $defined);
            $value = $this->choice($defined ? $input : [], 'office'.$n.'_value', $defined ? VisitKinds::RATING : null, $office === null ? null : (int) ($office->{'c_Ans'.$n.'_Value'} ?? VisitKinds::UNSET));
            if ($always || $text !== (string) ($office->{'c_Ans'.$n} ?? '')) {
                $payload['c_Ans'.$n] = $text;
            }
            if ($always || $value !== (int) ($office->{'c_Ans'.$n.'_Value'} ?? VisitKinds::UNSET)) {
                $payload['c_Ans'.$n.'_Value'] = $value;
            }
        }
        if ($always || $definition['other_comments']) {
            $other = $this->text($input, 'other_comments', $definition['other_comments']);
            if ($always || $other !== (string) ($office->c_OtherComments ?? '')) {
                $payload['c_OtherComments'] = $other;
            }
        }

        return $payload;
    }

    /** Whether a full office payload holds anything beyond blank text and unanswered ratings. */
    private function enteredOffice(array $payload): bool
    {
        foreach ($payload as $column => $value) {
            if (str_ends_with($column, '_Value') ? $value !== VisitKinds::UNSET : $value !== '') {
                return true;
            }
        }

        return false;
    }

    /** `Update_MP_Place`: the project's street and address after funding, written only when changed. */
    private function updatePlace(object $project, array $input): void
    {
        $ref = $this->tables->relation('microproject', 'MP_Street_ID');
        $storedStreet = $project->{$ref['state_column']} === 'resolved' ? (int) $project->{$ref['resolved_column']} : null;
        $streetInput = Fields::blank($input['street_id'] ?? null) ? null : (int) $input['street_id'];
        $note = Fields::text($input['place_note'] ?? '');
        $payload = [];
        if ($streetInput !== $storedStreet) {
            if ($streetInput !== null) {
                $this->tables->find('street', $streetInput, (int) $project->city_id, (int) $project->source_id) !== null || $this->fail('street_id', 'street');
            }
            $payload += $this->tables->link('microproject', 'MP_Street_ID', $streetInput);
        }
        if ($note !== (string) ($project->c_MP_AddressAfterFund ?? '')) {
            mb_strlen($note) <= 255 || $this->fail('place_note', 'place_long');
            $payload['c_MP_AddressAfterFund'] = $note;
        }
        if ($payload !== []) {
            DB::table('op_microproject')->where('id', $project->id)->update($payload);
        }
    }

    /** `Update_Task_MicroProject`: marks the project's existing assignment of the numbered task, nothing when there is none. */
    private function completeTask(object $project, int $legacyTaskId, int $state): void
    {
        $task = $this->tables->catalog('task', (int) $project->city_id, (int) $project->source_id)->first(fn ($row) => (int) ($row->c_T_ID ?? 0) === $legacyTaskId);
        if ($task === null) {
            return;
        }
        $projectRef = $this->tables->relation('task_microproject', 'MicroProject_ID');
        $taskRef = $this->tables->relation('task_microproject', 'Task_ID');
        DB::table('op_task_microproject')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($projectRef['resolved_column'], $project->id)->where($taskRef['resolved_column'], $task->id)
            ->update(['c_State' => $state, 'c_Date' => now()->format('Y-m-d H:i:s')]);
    }
}
