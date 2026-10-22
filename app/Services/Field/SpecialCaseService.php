<?php

declare(strict_types=1);

namespace App\Services\Field;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Finance\Fields;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Special-case reviews of a project (SpecialCase_Form). Reviews are inserted or edited in place; deletion is not offered.
 * The desktop's registrar (`User_ID`) is a historical source user and cannot stand in for a web account: new reviews leave it
 * unset and the audit trail records the acting account, while legacy reviews keep their value untouched.
 */
final class SpecialCaseService
{
    use RetiresRecords;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables) {}

    private function authorize(WebAccount $account, int $city, bool $write = false, ?string $extra = null): void
    {
        foreach (['applications.view', 'special_cases.view', ...($write ? ['special_cases.manage'] : []), ...($extra === null ? [] : [$extra])] as $capability) {
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

    private function cases(object $project, bool $deleted = false): Collection
    {
        $ref = $this->tables->relation('specialcase', 'MicroProject_ID');

        return $this->tables->query('specialcase', $deleted)->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$ref['resolved_column'], $project->id)->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function version(Collection $cases): string
    {
        return hash('sha256', json_encode($cases->map(fn ($row) => collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all())->all(), JSON_THROW_ON_ERROR));
    }

    private function fail(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('field.errors.'.$key)]);
    }

    /** Review types sorted by name; the desktop preselected the first (phone). */
    private function types(object $project): Collection
    {
        return $this->tables->catalog('specialcasetype', (int) $project->city_id, (int) $project->source_id)->sortBy('c_Name', SORT_STRING)->values();
    }

    public function show(WebAccount $account, int $projectId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $cases = $this->cases($project);
        $types = $this->types($project);
        $typeRef = $this->tables->relation('specialcase', 'Type_ID');
        $names = $types->keyBy('id');
        $this->audit->record($account->id, 'special_cases.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        $canRestore = $this->mayRestore($account, $city, 'special_cases.delete');

        return [
            'canRestore' => $canRestore, 'deleted' => $canRestore ? $this->cases($project, true) : collect(),
            'project' => $project, 'types' => $types, 'canManage' => $this->access->check($account, $city, 'special_cases.manage'), 'canDelete' => $this->access->check($account, $city, 'special_cases.delete'), 'version' => $this->version($cases),
            'cases' => $cases->map(function ($row) use ($typeRef, $names, $account) {
                $row = clone $row;
                $row->type_id = $row->{$typeRef['state_column']} === 'resolved' ? (int) $row->{$typeRef['resolved_column']} : null;
                $type = $row->type_id === null ? null : $names->get($row->type_id);
                $row->deletable = $this->mayRetire($account, (int) $row->city_id, 'special_cases.delete', $row);
                $row->type_name = $type === null ? null : (app()->isLocale('ar') ? $type->c_Name : ($type->c_Name_en ?: $type->c_Name));

                return $row;
            }),
        ];
    }

    /** $input: case_id?, type_id, date, reason, description, action. Only changed fields are validated and written. */
    public function save(WebAccount $account, int $projectId, array $input, string $version): int
    {
        return DB::transaction(function () use ($account, $projectId, $input, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            $cases = $this->cases($project);
            abort_unless(hash_equals($this->version($cases), $version), 409, 'Special cases changed. Reload before saving.');
            $caseId = Fields::blank($input['case_id'] ?? null) ? null : (int) $input['case_id'];
            $case = null;
            if ($caseId !== null) {
                $case = $cases->firstWhere('id', $caseId);
                abort_if($case === null, 404);
            }
            $payload = $this->payload($project, $case, $input);
            if ($case === null) {
                $caseId = $this->tables->insert('specialcase', $city, $source, $payload + $this->tables->link('specialcase', 'MicroProject_ID', $projectId));
            } elseif ($payload !== []) {
                DB::table('op_specialcase')->where('id', $case->id)->update($payload);
            }
            $this->audit->record($account->id, 'special_cases.saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

            return (int) $caseId;
        });
    }

    /** Deletes a review created in the web. Imported reviews are preserved. */
    public function delete(WebAccount $account, int $projectId, int $caseId, string $version): void
    {
        DB::transaction(function () use ($account, $projectId, $caseId, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, false, 'special_cases.delete');
            $cases = $this->cases($project);
            abort_unless(hash_equals($this->version($cases), $version), 409, 'Special cases changed. Reload before deleting.');
            $case = $cases->firstWhere('id', $caseId);
            abort_if($case === null, 404);
            $this->retire($account, $city, 'specialcase', $case);
            $this->audit->record($account->id, 'special_cases.deleted', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    /** Makes an imported review that an administrator deleted visible again. */
    public function restore(WebAccount $account, int $projectId, int $caseId): void
    {
        DB::transaction(function () use ($account, $projectId, $caseId) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, false, 'special_cases.delete');
            $this->requireImportedDelete($account, $city);
            abort_if($this->cases($project, true)->firstWhere('id', $caseId) === null, 404);
            $this->tables->unhide('specialcase', $caseId);
            $this->audit->record($account->id, 'special_cases.restored', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    private function payload(object $project, ?object $case, array $input): array
    {
        $create = $case === null;
        $text = fn (string $key) => Fields::text($input[$key] ?? '');
        $payload = [];

        $date = trim($text('date'));
        if ($create && $date === '') {
            $date = now()->toDateString();
        }
        if ($create || $date !== Fields::day($case->c_Date ?? null)) {
            Fields::isDate($date) || $this->fail('date', 'date');
            $payload['c_Date'] = $date;
        }

        $typeRef = $this->tables->relation('specialcase', 'Type_ID');
        $typeInput = Fields::blank($input['type_id'] ?? null) ? null : (int) $input['type_id'];
        $storedType = $create || $case->{$typeRef['state_column']} !== 'resolved' ? null : (int) $case->{$typeRef['resolved_column']};
        if ($create || $typeInput !== $storedType) {
            $typeInput !== null || $this->fail('type_id', 'type');
            $type = $this->tables->find('specialcasetype', $typeInput, (int) $project->city_id, (int) $project->source_id);
            $type !== null || $this->fail('type_id', 'type');
            $payload += $this->tables->link('specialcase', 'Type_ID', (int) $type->id);
        }

        // The desktop only refuses an empty reason (SpecialCase_Form.Save_button_Click:381).
        $reason = $text('reason');
        if ($create || $reason !== (string) ($case->c_Reason ?? '')) {
            ($reason !== '' && mb_strlen($reason) <= 65535) || $this->fail('reason', 'reason');
            $payload['c_Reason'] = $reason;
        }
        foreach (['description' => 'c_Description', 'action' => 'c_Action'] as $key => $column) {
            $value = $text($key);
            if ($create || $value !== (string) ($case->{$column} ?? '')) {
                mb_strlen($value) <= 65535 || $this->fail($key, 'long');
                $payload[$column] = $value;
            }
        }

        return $payload;
    }
}
