<?php

declare(strict_types=1);

namespace App\Services\Applications;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Lifecycle\LifecycleService;
use App\Services\ProjectNumberAllocator;
use App\Services\Sharing\SharedPayloadProjection;
use Database\Schema\OperationalManifest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

require_once __DIR__.'/../../../database/schema/OperationalManifest.php';

/** Source-scoped application intake. Preserved source rows are never written. */
final class ApplicationService
{
    private const TABLES = ['application' => 'microproject', 'people' => 'person_microproject', 'agriculture' => 'microproject_ag_answer', 'rehab' => 'microproject_rehab', 'rehab_answers' => 'microproject_rehab_answer', 'powersource' => 'microproject_powersource', 'risk' => 'microproject_risk', 'material' => 'material', 'loss' => 'person_loss', 'illness' => 'person_illness', 'asset' => 'person_asset', 'aid' => 'person_aidsource', 'help' => 'person_help', 'homepower' => 'person_powersource'];

    private const APPLICANT_KINDS = ['loss', 'illness', 'asset', 'aid', 'help', 'homepower'];

    private const ROLES = ['beneficiary' => 'مستفيد', 'partner' => 'شريك'];

    private const BLOCKED_FIELDS = ['c_ApplicationState', 'c_IsContentUpdated', 'c_MP_Donor', 'c_Created_By', 'c_Edited_By', 'c_PersonType', 'c_MP_AddressAfterFund'];

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private ProjectNumberAllocator $numbers, private LifecycleService $lifecycle) {}

    public function fields(string $kind): array
    {
        $fields = [];
        foreach ($this->table($kind)['columns'] as $column) {
            $name = $column['name'];
            if (! str_starts_with($name, 'c_') || preg_match('/(?:^c_ID$|_ID$)/i', $name) || in_array($name, self::BLOCKED_FIELDS, true)) {
                continue;
            }
            $definition = strtolower($column['definition']);
            $type = str_starts_with($definition, 'date ') ? 'date' : (preg_match('/^(int|double)/', $definition) ? 'number' : (str_contains($definition, 'text') ? 'textarea' : 'text'));
            preg_match('/(?:var)?char\((\d+)\)/', $definition, $length);
            $fields[$name] = ['type' => $type, 'max' => isset($length[1]) ? (int) $length[1] : 10000, 'label' => substr($name, 2), 'decimal' => str_starts_with($definition, 'double')];
        }

        return $fields;
    }

    public function relations(string $kind): array
    {
        $result = [];
        foreach ($this->relationships($kind) as $r) {
            $key = $r['child_columns'][0];
            if (in_array($key, ['MicroProject_ID', 'MicroRehab_ID', 'Person_ID', 'Category_ID', 'DonorGroup_ID', 'MP_Donor'], true) || $r['parent_source_table'] === 'user') {
                continue;
            }
            $result[$key] = ['label' => $key, 'table' => $r['parent_source_table']];
        }

        return $result;
    }

    private function authorize(WebAccount $account, int $city, bool $write = false): void
    {
        if (! $this->access->check($account, $city, 'applications.view') || ($write && ! $this->access->check($account, $city, 'applications.manage'))) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
    }

    private function row(WebAccount $account, string $kind, int $id, bool $write = false): object
    {
        $row = $this->queryTable(self::TABLES[$kind])->where('p.id', $id)->first();
        abort_if($row === null, 404);
        $this->authorize($account, (int) $row->city_id, $write);

        return $row;
    }

    private function validateFields(string $kind, array $fields): array
    {
        $metadata = $this->fields($kind);
        if (array_diff(array_keys($fields), array_keys($metadata))) {
            throw ValidationException::withMessages(['fields' => 'Unsupported field. Identity, authorship and lifecycle are managed separately.']);
        }
        $rules = [];
        foreach ($metadata as $key => $meta) {
            if (array_key_exists($key, $fields) && $fields[$key] === '' && in_array($meta['type'], ['number', 'date'], true)) {
                throw ValidationException::withMessages(['fields.'.$key => 'Use an explicit null to clear this value.']);
            }
            $rules[$key] = ['nullable', ...match ($meta['type']) {
                'date' => ['date_format:Y-m-d'],
                'number' => $meta['decimal'] ? ['numeric', function ($attribute, $value, $fail) {
                    if (! is_finite((float) $value)) {
                        $fail('The value must be a finite number.');
                    }
                }] : ['integer', 'between:-2147483648,2147483647'],
                default => ['string', 'max:'.$meta['max']],
            }];
        }

        return Validator::make($fields, $rules)->validate();
    }

    private function referencePayload(string $kind, int $city, int $source, array $references): array
    {
        if (array_diff(array_keys($references), array_keys($this->relations($kind)))) {
            throw ValidationException::withMessages(['references' => 'Unsupported relationship.']);
        }
        $payload = [];
        foreach ($references as $key => $id) {
            $r = $this->relation($kind, $key);
            if ($id !== null && $id !== '') {
                Validator::make(['reference' => $id], ['reference' => ['required', 'integer', 'min:1']])->validate();
                abort_unless($this->queryTable($r['parent_source_table'])->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $id)->exists(), 422, 'Relationship is unavailable in this city and source.');
            }
            $payload[$r['resolved_column']] = $id === null || $id === '' ? null : (int) $id;
            $payload[$r['state_column']] = $payload[$r['resolved_column']] === null ? 'null' : 'resolved';
        }

        return $payload;
    }

    private function link(string $kind, string $key, int $id): array
    {
        $r = $this->relation($kind, $key);

        return [$r['resolved_column'] => $id, $r['state_column'] => 'resolved'];
    }

    public function search(WebAccount $account, array $cities, string $search): LengthAwarePaginator
    {
        abort_unless($this->access->active($account), 403);
        $allowed = $this->access->cities($account, 'applications.view')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $cities = array_values(array_unique(array_map('intval', $cities)));
        abort_if(array_diff($cities, $allowed) !== [], 403);
        $query = $this->queryTable('microproject')->whereIn('p.city_id', $cities ?: $allowed);
        if ($search !== '') {
            $literal = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_substr($search, 0, 200)).'%';
            $query->where(function (Builder $q) use ($literal, $search) {
                $q->whereRaw("p.c_MP_Name LIKE ? ESCAPE '!'", [$literal]);
                if (ctype_digit($search) && strlen($search) <= 10) {
                    $q->orWhere('p.c_MP_ID', (int) $search);
                }
            });
        }
        $this->audit->record($account->id, 'applications.list', 'succeeded');

        return $query->orderBy('p.id')->paginate(20)->appends(['cities' => $cities, 'search' => $search]);
    }

    private function aggregate(WebAccount $account, object $application): array
    {
        $result = ['application' => $this->withReferences('application', $application)];
        foreach (array_diff(array_keys(self::TABLES), ['application', 'rehab_answers', ...self::APPLICANT_KINDS]) as $kind) {
            $ref = $this->relation($kind, 'MicroProject_ID');
            $result[$kind] = $this->queryTable(self::TABLES[$kind])->where('p.city_id', $application->city_id)->where('p.source_id', $application->source_id)
                ->where('p.'.$ref['resolved_column'], $application->id)->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get()->map(fn ($row) => $this->withReferences($kind, $row));
        }
        $ref = $this->relation('rehab_answers', 'MicroRehab_ID');
        $result['rehab_answers'] = $this->queryTable('microproject_rehab_answer')->where('p.city_id', $application->city_id)->where('p.source_id', $application->source_id)
            ->whereIn('p.'.$ref['resolved_column'], $result['rehab']->pluck('id'))->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get()->map(fn ($row) => $this->withReferences('rehab_answers', $row));
        // Person and support edits in P4.1 also invalidate an application's open form.
        $applicants = [];
        $personRelation = $this->relation('people', 'Person_ID');
        foreach ($result['people'] as $link) {
            if ($link->{$personRelation['state_column']} === 'resolved') {
                $applicants[$link->id] = $this->applicantSnapshot($application, (int) $link->{$personRelation['resolved_column']});
            }
        }
        $result['version'] = hash_hmac('sha256', serialize([$result, $applicants]), (string) config('app.key'));

        return $result;
    }

    public function find(WebAccount $account, int $id): array
    {
        $application = $this->row($account, 'application', $id);
        $result = $this->aggregate($account, $application);
        $this->audit->record($account->id, 'applications.view', 'succeeded', (int) $application->city_id, subjectType: 'application_entity', subjectId: $id);

        return $result;
    }

    private function mutate(WebAccount $account, int $id, string $version, callable $change): void
    {
        DB::transaction(function () use ($account, $id, $version, $change) {
            DB::table('op_microproject')->where('id', $id)->lockForUpdate()->first();
            $application = $this->row($account, 'application', $id, true);
            $personRelation = $this->relation('people', 'Person_ID');
            $personIds = $this->childrenOf('person_microproject', 'MicroProject_ID', $application)->pluck($personRelation['resolved_column'])->filter()->unique()->sort()->values();
            // P4.1 uses this same person lock for profile and child edits.
            DB::table('op_person')->whereIn('id', $personIds)->orderBy('id')->lockForUpdate()->get();
            abort_unless(hash_equals($this->aggregate($account, $application)['version'], $version), 409, 'Application changed. Reload before saving.');
            $change($application);
            $this->audit->record($account->id, 'applications.updated', 'succeeded', (int) $application->city_id, subjectType: 'application_entity', subjectId: $id);
        });
    }

    private function person(WebAccount $account, int $city, int $source, int $id): object
    {
        abort_unless($this->access->check($account, $city, 'people.view'), 403);
        $person = $this->queryTable('person')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $id)->first();
        abort_if($person === null, 422, 'Select a person from the same city and source.');

        return $person;
    }

    private function requireIntake(array $payload, bool $full): void
    {
        $rules = ['c_MP_Name' => ['required', 'string'], 'c_MP_DateOfRequest' => ['required', 'date_format:Y-m-d']];
        foreach (['SubCategory_ID', 'Career_ID', 'MP_Type_ID', 'MP_SubType_ID'] as $key) {
            $rules[$this->relation('application', $key)['resolved_column']] = ['required', 'integer', 'min:1'];
        }
        if ($full) {
            foreach (['MP_FundType_ID', 'MP_PlaceType_ID'] as $key) {
                $rules[$this->relation('application', $key)['resolved_column']] = ['required', 'integer', 'min:1'];
            }
            $rules['c_MP_RequestedAmount'] = ['required', 'numeric'];
            foreach (range(1, 5) as $n) {
                $rules['c_MP_Agreement'.$n] = ['required', 'integer', 'in:0,1'];
            }
        }
        Validator::make($payload, $rules)->validate();
    }

    private function categoryPayload(int $city, int $source, array $payload): array
    {
        $subcategory = $this->relation('application', 'SubCategory_ID');
        if (! array_key_exists($subcategory['resolved_column'], $payload)) {
            return [];
        }
        $category = $this->relation('application', 'Category_ID');
        if ($payload[$subcategory['resolved_column']] === null) {
            return [$category['resolved_column'] => null, $category['state_column'] => 'null'];
        }
        $row = $this->queryTable('subcategory')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $payload[$subcategory['resolved_column']])->first();
        $relation = $this->sourceRelation('subcategory', 'Category_ID');
        abort_unless($row && $row->{$relation['state_column']} === 'resolved', 422, 'Selected subcategory has no validated category.');
        abort_unless($this->queryTable('category')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $row->{$relation['resolved_column']})->exists(), 422);

        return [$category['resolved_column'] => $row->{$relation['resolved_column']}, $category['state_column'] => 'resolved'];
    }

    private function sourceRelation(string $table, string $key): array
    {
        foreach (OperationalManifest::load()['relationships'] as $r) {
            if ($r['child_source_table'] === $table && $r['child_columns'] === [$key]) {
                return $r;
            }
        }
        abort(422, 'Unsupported reference contract.');
    }

    private function checkSubtype(int $city, int $source, array $payload): void
    {
        $sub = $payload[$this->relation('application', 'MP_SubType_ID')['resolved_column']] ?? null;
        $type = $payload[$this->relation('application', 'MP_Type_ID')['resolved_column']] ?? null;
        $career = $payload[$this->relation('application', 'Career_ID')['resolved_column']] ?? null;
        $subcategory = $payload[$this->relation('application', 'SubCategory_ID')['resolved_column']] ?? null;
        if ($career !== null && $subcategory !== null) {
            $dependency = $this->sourceRelation('career', 'SubCategory_ID');
            abort_unless($this->queryTable('career')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $career)
                ->where('p.'.$dependency['resolved_column'], $subcategory)->where('p.'.$dependency['state_column'], 'resolved')->exists(), 422, 'Selected career does not belong to the subcategory.');
        }
        if ($sub === null || $type === null) {
            return;
        }
        $r = $this->sourceRelation('microprojectsubtype', 'Type_ID');
        abort_unless($this->queryTable('microprojectsubtype')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $sub)
            ->where('p.'.$r['resolved_column'], $type)->where('p.'.$r['state_column'], 'resolved')->exists(), 422, 'Selected subtype does not belong to the type.');
    }

    public function create(WebAccount $account, int $city, array $fields, int $beneficiaryId, array $references = [], string $stage = 'preliminary', array $intake = []): int
    {
        abort_unless(in_array($stage, ['preliminary', 'full'], true), 422);
        abort_if($stage === 'full', 422, 'Full intake submission is unavailable until household/support validation and lifecycle handoff are implemented.');

        return DB::transaction(function () use ($account, $city, $fields, $beneficiaryId, $references, $stage, $intake) {
            $this->authorize($account, $city, true);
            $source = $this->source($city);
            $this->person($account, $city, $source, $beneficiaryId);
            $payload = $this->validateFields('application', $fields) + $this->referencePayload('application', $city, $source, $references);
            $payload += $this->categoryPayload($city, $source, $payload);
            $this->requireIntake($payload, $stage === 'full');
            $this->checkSubtype($city, $source, $payload);
            $payload['c_MP_ID'] = (int) $this->numbers->allocate($source);
            $payload['c_ApplicationState'] = 'zero';
            $id = $this->insert('application', $city, $source, $payload);
            $link = $this->validateFields('people', $intake['people'] ?? []) + $this->link('people', 'MicroProject_ID', $id) + $this->link('people', 'Person_ID', $beneficiaryId) + ['c_PersonType' => self::ROLES['beneficiary']];
            $this->insert('people', $city, $source, $link);
            foreach ($intake['materials'] ?? [] as $material) {
                $this->insert('material', $city, $source, $this->validateFields('material', $material) + $this->link('material', 'MicroProject_ID', $id));
            }
            $this->lifecycle->recordPreliminary($this->row($account, 'application', $id, true));
            if ($stage === 'full') {
                $this->complete($account, $this->row($account, 'application', $id, true));
            }
            $this->audit->record($account->id, 'applications.created', 'succeeded', $city, subjectType: 'application_entity', subjectId: $id);

            return $id;
        });
    }

    public function update(WebAccount $account, int $id, array $fields, string $version, array $references = []): void
    {
        $this->mutate($account, $id, $version, function ($application) use ($fields, $references) {
            [$fields,$references] = $this->changes('application', $application, $fields, $references);
            $payload = $this->validateFields('application', $fields) + $this->referencePayload('application', (int) $application->city_id, (int) $application->source_id, $references);
            $required = ['c_MP_Name', 'c_MP_DateOfRequest'];
            foreach (['SubCategory_ID', 'Career_ID', 'MP_Type_ID', 'MP_SubType_ID'] as $key) {
                $required[] = $this->relation('application', $key)['resolved_column'];
            }
            Validator::make($payload, array_fill_keys(array_intersect(array_keys($payload), $required), ['required']))->validate();
            $payload += $this->categoryPayload((int) $application->city_id, (int) $application->source_id, $payload);
            $merged = array_replace((array) $application, $payload);
            if ($payload !== []) {
                // Legacy rows may carry sentinel dates and incomplete unrelated sections.
                // Completeness belongs to creation; edits validate supplied values only.
                if (array_intersect(array_keys($references), ['MP_SubType_ID', 'MP_Type_ID', 'SubCategory_ID', 'Career_ID'])) {
                    $this->checkSubtype((int) $application->city_id, (int) $application->source_id, $merged);
                }
                DB::table('op_microproject')->where('id', $application->id)->update($payload);
            }
        });
    }

    public function linkPerson(WebAccount $account, int $id, int $personId, string $role, array $fields, string $version, ?int $linkId = null): void
    {
        abort_unless(isset(self::ROLES[$role]), 422);
        $this->mutate($account, $id, $version, function ($application) use ($account, $personId, $role, $fields, $linkId) {
            $this->person($account, (int) $application->city_id, (int) $application->source_id, $personId);
            if ($linkId !== null) {
                $old = $this->ownedChild($account, $application, 'people', $linkId);
                [$fields] = $this->changes('people', $old, $fields, []);
            }
            $payload = $this->validateFields('people', $fields) + $this->link('people', 'Person_ID', $personId) + ['c_PersonType' => self::ROLES[$role]];
            if ($linkId === null) {
                $this->insert('people', (int) $application->city_id, (int) $application->source_id, $payload + $this->link('people', 'MicroProject_ID', (int) $application->id));
            } else {
                $this->ownedChild($account, $application, 'people', $linkId);
                DB::table('op_person_microproject')->where('id', $linkId)->update($payload);
            }
        });
    }

    private function ownedChild(WebAccount $account, object $application, string $kind, int $id, ?int $parentId = null): object
    {
        $child = $this->row($account, $kind, $id, true);
        $r = $this->relation($kind, $kind === 'rehab_answers' ? 'MicroRehab_ID' : 'MicroProject_ID');
        abort_unless((int) $child->city_id === (int) $application->city_id && (int) $child->source_id === (int) $application->source_id
            && (int) $child->{$r['resolved_column']} === ($parentId ?? (int) $application->id) && $child->{$r['state_column']} === 'resolved', 404);

        return $child;
    }

    private function compatibleAnswer(string $kind, int $city, int $source, array $payload): void
    {
        $q = $this->relation($kind, 'Question_ID');
        $a = $this->relation($kind, 'Answer_ID');
        $question = $payload[$q['resolved_column']] ?? null;
        $answer = $payload[$a['resolved_column']] ?? null;
        abort_unless($question !== null && $answer !== null, 422, 'Select a question and its answer.');
        $answerQuestion = $this->sourceRelation($a['parent_source_table'], 'Question_ID');
        abort_unless($this->queryTable($a['parent_source_table'])->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $answer)
            ->where('p.'.$answerQuestion['resolved_column'], $question)->where('p.'.$answerQuestion['state_column'], 'resolved')->exists(), 422, 'Answer does not belong to the selected question.');
    }

    public function saveDetail(WebAccount $account, int $id, string $kind, array $fields, string $version, ?int $detailId = null, array $references = [], ?int $parentId = null): void
    {
        abort_unless(in_array($kind, ['agriculture', 'rehab', 'rehab_answers', 'powersource', 'risk', 'material'], true), 422);
        $this->mutate($account, $id, $version, function ($application) use ($account, $kind, $fields, $detailId, $references, $parentId) {
            if ($kind === 'rehab_answers') {
                abort_if($parentId === null, 422);
                $this->ownedChild($account, $application, 'rehab', $parentId);
            } else {
                abort_if($parentId !== null, 422);
            }
            $old = $detailId === null ? null : $this->ownedChild($account, $application, $kind, $detailId, $parentId);
            if ($old) {
                [$fields,$references] = $this->changes($kind, $old, $fields, $references);
            }
            $payload = $this->validateFields($kind, $fields) + $this->referencePayload($kind, (int) $application->city_id, (int) $application->source_id, $references);
            if (in_array($kind, ['agriculture', 'rehab_answers'], true) && ($old === null || $payload !== [])) {
                $this->compatibleAnswer($kind, (int) $application->city_id, (int) $application->source_id, array_replace((array) ($old ?? (object) []), $payload));
            }
            if ($kind === 'rehab_answers' && ($old === null || $references !== [])) {
                $merged = array_replace((array) ($old ?? (object) []), $payload);
                $questionRelation = $this->relation($kind, 'Question_ID');
                $parentRelation = $this->relation($kind, 'MicroRehab_ID');
                $questionId = $merged[$questionRelation['resolved_column']];
                $question = $this->queryTable('rehabquestion')->where('p.id', $questionId)->where('p.city_id', $application->city_id)->where('p.source_id', $application->source_id)->first();
                if (isset($question->shared_snapshot_id)) {
                    $question = (new SharedPayloadProjection(DB::connection()))->project('rehabquestion', $question, (int) $application->source_id, (int) $question->shared_snapshot_id);
                }
                abort_unless($question && isset($question->c_ID) && ctype_digit((string) $question->c_ID), 422, 'This question needs an explicit validation policy before new answers can be saved.');
                $maximum = (int) $question->c_ID >= 9 ? 1 : 10;
                $existing = $this->queryTable('microproject_rehab_answer')->where('p.city_id', $application->city_id)->where('p.source_id', $application->source_id)
                    ->where('p.'.$parentRelation['resolved_column'], $parentId)->where('p.'.$parentRelation['state_column'], 'resolved')
                    ->where('p.'.$questionRelation['resolved_column'], $questionId)->where('p.'.$questionRelation['state_column'], 'resolved');
                if ($detailId !== null) {
                    $existing->where('p.id', '!=', $detailId);
                }
                abort_if($existing->count() >= $maximum, 422, 'This rehabilitation question has reached its maximum number of answers.');
            }
            if ($detailId === null) {
                $this->insert($kind, (int) $application->city_id, (int) $application->source_id, $payload + $this->link($kind, $kind === 'rehab_answers' ? 'MicroRehab_ID' : 'MicroProject_ID', $parentId ?? (int) $application->id));
            } elseif ($payload !== []) {
                DB::table($this->table($kind)['name'])->where('id', $detailId)->update($payload);
            }
        });
    }

    private function complete(WebAccount $account, object $application): void
    {
        if ($application->c_ApplicationState !== 'zero') {
            throw ValidationException::withMessages(['version' => __('applications.complete_not_preliminary')]);
        }
        $this->requireIntake((array) $application, true);
        $materials = $this->childrenOf('material', 'MicroProject_ID', $application);
        if ($materials->isEmpty()) {
            throw ValidationException::withMessages(['material' => __('applications.complete_budget')]);
        }
        foreach ($materials as $material) {
            $this->requiredValues($material, ['c_Name', 'c_Price', 'c_Amount']);
        }
        // Household, story, work and education gates of the desktop full form are not implemented yet (see P4.3 delivery record).
        $this->lifecycle->enterFull($application);
    }

    private function childrenOf(string $table, string $key, object $parent): Collection
    {
        $relation = $this->sourceRelation($table, $key);

        return $this->queryTable($table)->where('p.city_id', $parent->city_id)->where('p.source_id', $parent->source_id)
            ->where('p.'.$relation['resolved_column'], $parent->id)->where('p.'.$relation['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function applicantSnapshot(object $application, int $personId): array
    {
        $person = $this->queryTable('person')->where('p.city_id', $application->city_id)->where('p.source_id', $application->source_id)->where('p.id', $personId)->first();
        if ($person === null) {
            return ['person' => null];
        }
        $result = ['person' => $person, 'details' => []];
        foreach (self::APPLICANT_KINDS as $kind) {
            $rows = $this->childrenOf(self::TABLES[$kind], 'Person_ID', $person);
            if ($kind === 'aid') {
                $projectRelation = $this->relation('aid', 'MicroProject_ID');
                $rows = $rows->filter(fn ($row) => $row->{$projectRelation['resolved_column']} === null || (int) $row->{$projectRelation['resolved_column']} === (int) $application->id)->values();
            }
            $result['details'][$kind] = $rows;
        }
        foreach (['education' => 'person_education', 'course' => 'person_course', 'work' => 'person_workexp', 'families' => 'person_family'] as $kind => $table) {
            $result[$kind] = $this->childrenOf($table, 'Person_ID', $person);
        }
        $result['household'] = [];
        foreach ($result['families'] as $membership) {
            $family = $this->resolvedParent('person_family', 'Family_ID', $membership, false);
            if ($family === null) {
                continue;
            }
            $members = $this->childrenOf('person_family', 'Family_ID', $family);
            $household = ['family' => $family, 'members' => []];
            foreach ($members as $member) {
                $relative = $this->resolvedParent('person_family', 'Person_ID', $member, false);
                if ($relative === null) {
                    continue;
                }
                $household['members'][] = ['membership' => $member, 'person' => $relative, 'work' => $this->childrenOf('person_workexp', 'Person_ID', $relative)];
            }
            $result['household'][] = $household;
        }

        return $result;
    }

    public function applicantDetails(WebAccount $account, int $id, int $linkId): array
    {
        $application = $this->row($account, 'application', $id);
        abort_unless($this->access->check($account, (int) $application->city_id, 'people.view'), 403);
        $link = $this->queryTable('person_microproject')->where('p.id', $linkId)->first();
        $relation = $this->relation('people', 'MicroProject_ID');
        abort_unless($link && (int) $link->city_id === (int) $application->city_id && (int) $link->source_id === (int) $application->source_id && $link->{$relation['state_column']} === 'resolved' && (int) $link->{$relation['resolved_column']} === $id, 404);
        $person = $this->resolvedParent('person_microproject', 'Person_ID', $link);
        $snapshot = $this->applicantSnapshot($application, (int) $person->id);
        $result = ['person_id' => (int) $person->id, 'details' => [], 'fields' => [], 'options' => []];
        foreach (self::APPLICANT_KINDS as $kind) {
            $result['details'][$kind] = $snapshot['details'][$kind]->map(fn ($row) => $this->withReferences($kind, $row));
            $result['fields'][$kind] = $this->fields($kind);
            $result['options'][$kind] = $this->options($account, (int) $application->city_id, $kind);
        }

        return $result;
    }

    public function saveApplicantDetail(WebAccount $account, int $id, int $linkId, string $kind, array $fields, string $version, ?int $detailId = null, array $references = []): void
    {
        abort_unless(in_array($kind, self::APPLICANT_KINDS, true), 422);
        $this->mutate($account, $id, $version, function ($application) use ($account, $linkId, $kind, $fields, $detailId, $references) {
            $link = $this->ownedChild($account, $application, 'people', $linkId);
            $person = $this->resolvedParent('person_microproject', 'Person_ID', $link);
            abort_unless($this->access->check($account, (int) $application->city_id, 'people.manage'), 403);
            DB::table('op_person')->where('id', $person->id)->lockForUpdate()->first();
            $old = null;
            if ($detailId !== null) {
                $old = $this->row($account, $kind, $detailId, true);
                $owner = $this->relation($kind, 'Person_ID');
                abort_unless((int) $old->city_id === (int) $application->city_id && (int) $old->source_id === (int) $application->source_id && $old->{$owner['state_column']} === 'resolved' && (int) $old->{$owner['resolved_column']} === (int) $person->id, 404);
                if ($kind === 'aid') {
                    $project = $this->relation('aid', 'MicroProject_ID');
                    abort_unless($old->{$project['resolved_column']} === null || (int) $old->{$project['resolved_column']} === (int) $application->id, 404);
                }
                [$fields,$references] = $this->changes($kind, $old, $fields, $references);
            }
            $payload = $this->validateFields($kind, $fields) + $this->referencePayload($kind, (int) $application->city_id, (int) $application->source_id, $references);
            if ($old === null || $payload !== []) {
                $this->validateSupportRow($kind, (object) array_replace((array) ($old ?? $person), $payload));
            }
            if ($old === null) {
                $payload += $this->link($kind, 'Person_ID', (int) $person->id);
                if ($kind === 'aid') {
                    $payload += $this->link($kind, 'MicroProject_ID', (int) $application->id);
                }
                $this->insert($kind, (int) $application->city_id, (int) $application->source_id, $payload);
            } elseif ($payload !== []) {
                DB::table(self::TABLES[$kind] === 'person_aidsource' ? 'op_person_aidsource' : $this->table($kind)['name'])->where('id', $detailId)->update($payload);
            }
        });
    }

    private function resolvedParent(string $table, string $key, object $row, bool $required = true): ?object
    {
        $r = $this->sourceRelation($table, $key);
        $id = $row->{$r['resolved_column']} ?? null;
        if (! $required && ($id === null || ($row->{$r['state_column']} ?? null) !== 'resolved')) {
            return null;
        }
        abort_unless($id !== null && ($row->{$r['state_column']} ?? null) === 'resolved', 422, 'Complete the '.$table.'.'.$key.' selection.');
        $parent = $this->queryTable($r['parent_source_table'])->where('p.id', $id)->where('p.city_id', $row->city_id)->where('p.source_id', $row->source_id)->first();
        if ($parent === null && ! $required) {
            return null;
        }
        abort_if($parent === null, 422, 'The '.$table.'.'.$key.' selection is unavailable.');
        if (isset($parent->shared_snapshot_id)) {
            $parent = (new SharedPayloadProjection(DB::connection()))->project($r['parent_source_table'], $parent, (int) $row->source_id, (int) $parent->shared_snapshot_id);
        }

        return $parent;
    }

    private function requiredValues(object $row, array $fields): void
    {
        Validator::make((array) $row, array_fill_keys($fields, ['required']))->validate();
    }

    private function validateSupportRow(string $kind, object $row): void
    {
        $required = match ($kind) {
            'loss' => ['Loss_ID'],'illness' => ['Illness_ID'],'asset' => ['Asset_ID', 'Asset_Type_ID', 'Asset_Benefits_ID'],
            'aid' => ['AidSource_ID'],'help' => ['AidSource_ID', 'Aid_ID', 'Aidperiod_ID'],'homepower' => ['PowerSource_ID'],
        };
        foreach ($required as $key) {
            $this->resolvedParent(self::TABLES[$kind], $key, $row);
        }
        $this->requiredValues($row, match ($kind) {
            'loss' => ['c_Year'],'aid' => ['c_DateYear'],'homepower' => ['c_Note', 'c_Responsability'],default => [],
        });
        if ($kind === 'illness' && (int) ($row->c_Has_MedicalAid ?? 0) === 1) {
            $this->resolvedParent('person_illness', 'AidSource_ID', $row);
        }
        if ($kind === 'asset' && $this->resolvedParent('person_asset', 'Asset_Type_ID', $row)->c_Name === 'ملك') {
            $this->requiredValues($row, ['c_Year']);
        }
    }

    public function convertToFull(WebAccount $account, int $id, string $version): void
    {
        $this->mutate($account, $id, $version, fn ($application) => $this->complete($account, $application));
    }

    public function personOptions(WebAccount $account, int $city): Collection
    {
        $this->authorize($account, $city);
        abort_unless($this->access->check($account, $city, 'people.view'), 403);

        return $this->queryTable('person')->where('p.city_id', $city)->orderBy('p.id')->limit(500)->get()->map(fn ($p) => (object) ['id' => $p->id, 'label' => trim(($p->c_P_FirstName ?? '').' '.($p->c_P_LastName ?? '')).' · #'.$p->id]);
    }

    public function options(WebAccount $account, int $city, string $kind): array
    {
        $this->authorize($account, $city);
        $result = [];
        foreach ($this->relations($kind) as $key => $r) {
            $result[$key] = $this->queryTable($r['table'])->where('p.city_id', $city)->orderBy('p.id')->limit(500)->get()->map(function ($row) use ($r) {
                if (isset($row->shared_snapshot_id)) {
                    $row = (new SharedPayloadProjection(DB::connection()))->project($r['table'], $row, (int) $row->source_id, (int) $row->shared_snapshot_id);
                }
                $labels = [];
                foreach ((array) $row as $name => $value) {
                    if (str_starts_with($name, 'c_') && ! preg_match('/(?:^c_ID$|_ID$)/i', $name) && $value !== null && $value !== '') {
                        $labels[] = (string) $value;
                    }
                }

                $option = (object) ['id' => $row->id, 'label' => implode(' · ', $labels) ?: '#'.$row->id, 'raw_id' => $row->c_ID ?? null, 'type' => $row->c_Type ?? null];
                if (in_array($r['table'], ['aganswer', 'rehabanswer'], true)) {
                    $question = $this->sourceRelation($r['table'], 'Question_ID');
                    $option->parent_id = $row->{$question['state_column']} === 'resolved' ? $row->{$question['resolved_column']} : null;
                }

                return $option;
            });
        }

        return $result;
    }

    private function table(string $kind): array
    {
        abort_unless(isset(self::TABLES[$kind]), 422);

        return OperationalManifest::table(self::TABLES[$kind]);
    }

    private function relationships(string $kind): array
    {
        return array_values(array_filter(OperationalManifest::load()['relationships'], fn ($r) => $r['target_table'] === $this->table($kind)['name']));
    }

    private function queryTable(string $sourceTable): Builder
    {
        $table = OperationalManifest::table($sourceTable);
        $tableId = (int) preg_replace('/\D/', '', $table['checks'][0]);

        return DB::table($table['name'].' as p')
            ->join('operational_entities as e', fn ($j) => $j->on('e.id', '=', 'p.id')->on('e.city_id', '=', 'p.city_id')->on('e.source_id', '=', 'p.source_id')->on('e.source_table_id', '=', 'p.source_table_id'))
            ->join('sources as s', fn ($j) => $j->on('s.id', '=', 'p.source_id')->on('s.city_id', '=', 'p.city_id'))
            ->join('cities as c', 'c.id', '=', 'p.city_id')->where('p.source_table_id', $tableId)
            ->where(function (Builder $q) {
                $q->where('e.identity_kind', 'web')->orWhere(function (Builder $legacy) {
                    $legacy->whereIn('e.identity_kind', ['legacy_primary', 'legacy_ordinal'])->whereExists(function (Builder $origin) {
                        $origin->selectRaw('1')->from('operational_origins as o')
                            ->join('source_records as r', fn ($j) => $j->on('r.id', '=', 'o.source_record_id')->on('r.source_id', '=', 'o.source_id')->on('r.table_id', '=', 'o.source_table_id')->on('r.snapshot_id', '=', 'o.snapshot_id'))
                            ->join('source_publications as pub', fn ($j) => $j->on('pub.source_id', '=', 'o.source_id')->on('pub.snapshot_id', '=', 'o.snapshot_id'))
                            ->whereColumn('o.entity_id', 'p.id')->whereColumn('o.source_id', 'p.source_id')->whereColumn('o.source_table_id', 'p.source_table_id')
                            ->whereIn('pub.publication_kind', app()->environment(['local', 'testing']) ? ['synthetic', 'validated'] : ['validated']);
                    });
                });
            })->select(['p.*', 'e.identity_kind', 'c.name_en as city_name_en', 'c.name_ar as city_name_ar']);
    }

    private function source(int $city): int
    {
        $sources = DB::table('sources')->where('city_id', $city)->pluck('id');
        abort_unless($sources->count() === 1, 409, 'An unambiguous city source is required.');

        return (int) $sources->first();
    }

    private function insert(string $kind, int $city, int $source, array $payload): int
    {
        $table = $this->table($kind);
        $scope = ['city_id' => $city, 'source_id' => $source, 'source_table_id' => (int) preg_replace('/\D/', '', $table['checks'][0])];
        $id = DB::table('operational_entities')->insertGetId($scope + ['identity_kind' => 'web', 'first_key_sha256' => null, 'first_key_bytes' => null, 'created_at' => now('UTC')]);
        foreach ($this->relationships($kind) as $r) {
            $payload += [$r['resolved_column'] => null, $r['state_column'] => 'null'];
        }
        DB::table($table['name'])->insert($scope + ['id' => $id] + $payload);

        return (int) $id;
    }

    private function changes(string $kind, object $row, array $fields, array $references): array
    {
        $fields = array_filter($fields, fn ($value, $key) => ! property_exists($row, $key)
            || (($value === null) !== ($row->{$key} === null)) || (string) $value !== (string) $row->{$key}, ARRAY_FILTER_USE_BOTH);
        foreach ($references as $key => $value) {
            if (isset($this->relations($kind)[$key])) {
                $relation = $this->relation($kind, $key);
                if ((string) $value === (string) ($row->{$relation['resolved_column']} ?? null)) {
                    unset($references[$key]);
                }
            }
        }

        return [$fields, $references];
    }

    private function relation(string $kind, string $key): array
    {
        foreach ($this->relationships($kind) as $r) {
            if ($r['child_columns'] === [$key]) {
                return $r;
            }
        }
        abort(422);
    }

    private function withReferences(string $kind, object $row): object
    {
        $row = clone $row;
        $row->references = [];
        $row->referenceDescriptions = [];
        foreach ($this->relationships($kind) as $r) {
            $id = $row->{$r['resolved_column']} ?? null;
            $parent = $id === null ? null : $this->queryTable($r['parent_source_table'])
                ->where('p.city_id', $row->city_id)->where('p.source_id', $row->source_id)->where('p.id', $id)->first();
            if ($parent && isset($parent->shared_snapshot_id)) {
                $parent = (new SharedPayloadProjection(DB::connection()))->project($r['parent_source_table'], $parent, (int) $row->source_id, (int) $parent->shared_snapshot_id);
            }
            $label = null;
            if ($parent) {
                $parts = [];
                foreach ((array) $parent as $name => $value) {
                    if (preg_match('/^c_(?:P_FirstName|P_LastName|F_Number|Name|Name_en|E_Name|W_Name)$/', $name) && $value !== null && $value !== '') {
                        $parts[] = (string) $value;
                    }
                }
                $label = implode(' · ', $parts) ?: '#'.$id;
            }
            $raw = $row->{'c_'.$r['child_columns'][0]} ?? null;
            $row->referenceDescriptions[$r['child_columns'][0]] = $label ?? ($id !== null ? __('people.unavailable').' #'.$id : ($raw !== null ? (string) $raw.' · '.__('people.unresolved') : '—'));
        }
        foreach ($this->relations($kind) as $key => $unused) {
            $r = $this->relation($kind, $key);
            $row->references[$key] = $row->{$r['resolved_column']} ?? null;
        }

        return $row;
    }
}
