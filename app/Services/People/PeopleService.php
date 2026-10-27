<?php

declare(strict_types=1);

namespace App\Services\People;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Sharing\SharedPayloadProjection;
use Database\Schema\OperationalManifest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

require_once __DIR__.'/../../../database/schema/OperationalManifest.php';

/** Operational writes only. Original source payloads and identity evidence are immutable. */
final class PeopleService
{
    private const TABLES = ['person' => 'person', 'family' => 'family', 'families' => 'person_family', 'education' => 'person_education', 'workexp' => 'person_workexp', 'course' => 'person_course'];

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit) {}

    private function table(string $kind): array
    {
        abort_unless(isset(self::TABLES[$kind]), 422);

        return OperationalManifest::table(self::TABLES[$kind]);
    }

    private function relationships(string $kind): array
    {
        return array_values(array_filter(OperationalManifest::load()['relationships'], fn ($r) => $r['target_table'] === $this->table($kind)['name']));
    }

    public function fields(string $kind): array
    {
        $fields = [];
        foreach ($this->table($kind)['columns'] as $column) {
            $name = $column['name'];
            if (! str_starts_with($name, 'c_') || preg_match('/(?:^c_ID$|_ID$)/i', $name)) {
                continue;
            }
            $definition = strtolower($column['definition']);
            $type = str_starts_with($definition, 'date ') ? 'date' : (preg_match('/^(int|double)/', $definition) ? 'number' : (str_contains($definition, 'text') ? 'textarea' : 'text'));
            preg_match('/(?:var)?char\((\d+)\)/', $definition, $length);
            $fields[$name] = ['type' => $type, 'max' => isset($length[1]) ? (int) $length[1] : 10000, 'label' => substr($name, 2)];
        }

        if ($kind === 'person') {
            $first = ['c_P_FirstName', 'c_P_LastName', 'c_P_FatherName', 'c_P_MotherName', 'c_P_FullName_en', 'c_P_NationalNumber', 'c_P_DOB', 'c_P_Sex', 'c_P_RegistrationPlace', 'c_P_Mobile', 'c_P_Mobile2', 'c_P_HomeTel', 'c_P_HomeAddress'];
            $fields = array_replace(array_fill_keys($first, null), $fields);
        }

        return $fields;
    }

    public function relations(string $kind): array
    {
        $result = [];
        foreach ($this->relationships($kind) as $r) {
            $key = $r['child_columns'][0];
            if (! in_array($key, ['Person_ID', 'Family_ID', 'P_Provider_ID', 'MicroProject_ID'], true)) {
                $result[$key] = ['label' => $key, 'table' => $r['parent_source_table']];
            }
        }

        return $result;
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

    private function authorize(WebAccount $account, int $city, bool $write = false): void
    {
        if (! $this->access->check($account, $city, 'people.view') || ($write && ! $this->access->check($account, $city, 'people.manage'))) {
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

    public function search(WebAccount $account, array $cities, string $search): LengthAwarePaginator
    {
        abort_unless($this->access->active($account), 403);
        $allowed = $this->access->cities($account, 'people.view')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $cities = array_values(array_unique(array_map('intval', $cities)));
        abort_if(array_diff($cities, $allowed) !== [], 403);
        $query = $this->queryTable('person')->whereIn('p.city_id', $cities ?: $allowed);
        if ($search !== '') {
            $literal = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_substr($search, 0, 200)).'%';
            $query->where(function (Builder $q) use ($literal, $search) {
                foreach (['c_P_FirstName', 'c_P_LastName', 'c_P_FatherName', 'c_P_NationalNumber', 'c_P_Mobile'] as $field) {
                    $q->orWhereRaw("p.$field LIKE ? ESCAPE '!'", [$literal]);
                }
                $variant = str_starts_with($search, '0') ? substr($search, 1) : '0'.$search;
                $q->orWhere('p.c_P_NationalNumber', $variant);
                $personRef = $this->relation('families', 'Person_ID');
                $familyRef = $this->relation('families', 'Family_ID');
                $q->orWhereExists(function (Builder $membership) use ($search, $personRef, $familyRef) {
                    $membership->selectRaw('1')->from('op_person_family as pf')->join('op_family as f', 'f.id', '=', 'pf.'.$familyRef['resolved_column'])
                        ->whereColumn('pf.'.$personRef['resolved_column'], 'p.id')->whereColumn('pf.city_id', 'p.city_id')
                        ->whereColumn('pf.source_id', 'p.source_id')->whereColumn('f.city_id', 'p.city_id')->whereColumn('f.source_id', 'p.source_id')
                        ->where('pf.'.$personRef['state_column'], 'resolved')->where('pf.'.$familyRef['state_column'], 'resolved')
                        ->where('f.c_F_Number', $search)
                        ->whereIn('pf.id', $this->queryTable('person_family')->select('p.id'))
                        ->whereIn('f.id', $this->queryTable('family')->select('p.id'));
                });
            });
        }
        $this->audit->record($account->id, 'people.list', 'succeeded');

        return $query->orderBy('p.id')->paginate(20)->appends(['search' => $search, 'cities' => $cities]);
    }

    private function aggregate(WebAccount $account, object $person): array
    {
        $person = $this->withReferences('person', $person);
        $result = ['person' => $person];
        foreach (['families', 'education', 'workexp', 'course'] as $kind) {
            $r = $this->relation($kind, 'Person_ID');
            $result[$kind] = $this->queryTable(self::TABLES[$kind])->where('p.city_id', $person->city_id)->where('p.source_id', $person->source_id)
                ->where('p.'.$r['resolved_column'], $person->id)->where('p.'.$r['state_column'], 'resolved')->orderBy('p.id')->get()
                ->map(fn ($row) => $this->withReferences($kind, $row));
            if ($kind === 'families') {
                foreach ($result[$kind] as $membership) {
                    $familyRef = $this->relation('families', 'Family_ID');
                    $family = $this->queryTable('family')->where('p.city_id', $person->city_id)->where('p.source_id', $person->source_id)->where('p.id', $membership->{$familyRef['resolved_column']})->first();
                    $membership->c_F_Number = $family?->c_F_Number;
                    $membership->familyId = $family?->id;
                    $membership->providerId = $membership->{$this->relation('families', 'P_Provider_ID')['resolved_column']} ?? null;
                }
            }
        }
        $result['version'] = hash_hmac('sha256', serialize($result), (string) config('app.key'));

        return $result;
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
                    if (preg_match('/^c_(?:P_FirstName|P_LastName|F_Number|Name|Name_ar|Name_en|E_Name|W_Name)$/', $name) && $value !== null && $value !== '') {
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

    public function find(WebAccount $account, int $personId): array
    {
        $person = $this->row($account, 'person', $personId);
        $result = $this->aggregate($account, $person);
        $this->audit->record($account->id, 'people.view', 'succeeded', (int) $person->city_id, subjectType: 'person_entity', subjectId: $personId);

        return $result;
    }

    public function validateFields(string $kind, array $fields, bool $creating = false): array
    {
        $metadata = $this->fields($kind);
        if (array_diff(array_keys($fields), array_keys($metadata))) {
            throw ValidationException::withMessages(['fields' => 'Unsupported identity, relationship or payload field.']);
        }
        $rules = [];
        foreach ($metadata as $key => $meta) {
            if (array_key_exists($key, $fields) && $fields[$key] === '' && in_array($meta['type'], ['number', 'date'], true)) {
                throw ValidationException::withMessages(['fields.'.$key => __('people.empty_value')]);
            }
            $rules[$key] = ['nullable', ...match ($meta['type']) {
                'date' => ['date_format:Y-m-d'],
                'number' => $key === 'c_W_Salary' ? ['numeric', 'between:-1.0e100,1.0e100'] : ['integer', 'between:-2147483648,2147483647'],
                default => ['string', 'max:'.$meta['max']],
            }];
        }
        if ($creating && $kind === 'person') {
            foreach (['c_P_FirstName', 'c_P_LastName', 'c_P_Sex'] as $key) {
                $rules[$key][0] = 'required';
            }
        }

        return Validator::make($fields, $rules)->validate();
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

    public function duplicateCandidates(WebAccount $account, int $city, string $number): Collection
    {
        $this->authorize($account, $city);
        if ($number === '') {
            return collect();
        }
        $variants = [$number, str_starts_with($number, '0') ? substr($number, 1) : '0'.$number];

        return $this->queryTable('person')->where('p.city_id', $city)->whereIn('p.c_P_NationalNumber', $variants)->orderBy('p.id')->get();
    }

    public function create(WebAccount $account, int $city, array $fields, bool $confirmDuplicate = false, array $references = []): int
    {
        return DB::transaction(function () use ($account, $city, $fields, $confirmDuplicate, $references) {
            $this->authorize($account, $city, true);
            $payload = $this->validateFields('person', $fields, true);
            if (! $confirmDuplicate && $this->duplicateCandidates($account, $city, $payload['c_P_NationalNumber'] ?? '')->isNotEmpty()) {
                throw ValidationException::withMessages(['duplicate' => 'Review the existing people and explicitly confirm creating a separate person.']);
            }
            $source = $this->source($city);
            $payload += $this->referencePayload($account, 'person', $city, $source, $references);
            $id = $this->insert('person', $city, $source, $payload);
            $this->audit->record($account->id, 'people.created', 'succeeded', $city, subjectType: 'person_entity', subjectId: $id);

            return $id;
        });
    }

    private function mutate(WebAccount $account, int $personId, string $version, callable $change): void
    {
        DB::transaction(function () use ($account, $personId, $version, $change) {
            DB::table('op_person')->where('id', $personId)->lockForUpdate()->first();
            $person = $this->row($account, 'person', $personId, true);
            abort_unless(hash_equals($this->aggregate($account, $person)['version'], $version), 409, 'This record changed. Reload before saving.');
            $change($person);
            $this->audit->record($account->id, 'people.updated', 'succeeded', (int) $person->city_id, subjectType: 'person_entity', subjectId: $personId);
        });
    }

    public function update(WebAccount $account, int $personId, array $fields, string $version, array $references = [], bool $confirmDuplicate = false): void
    {
        $this->mutate($account, $personId, $version, function ($person) use ($account, $fields, $references, $confirmDuplicate) {
            [$fields, $references] = $this->changes('person', $person, $fields, $references);
            if (! $confirmDuplicate && isset($fields['c_P_NationalNumber']) && is_string($fields['c_P_NationalNumber'])
                && $this->duplicateCandidates($account, (int) $person->city_id, $fields['c_P_NationalNumber'])->contains(fn ($candidate) => (int) $candidate->id !== (int) $person->id)) {
                throw ValidationException::withMessages(['duplicate' => __('people.duplicate_review')]);
            }
            $payload = $this->validateFields('person', $fields) + $this->referencePayload($account, 'person', (int) $person->city_id, (int) $person->source_id, $references);
            if ($payload !== []) {
                DB::table('op_person')->where('id', $person->id)->update($payload);
            }
        });
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

    private function referencePayload(WebAccount $account, string $kind, int $city, int $source, array $references): array
    {
        if (array_diff(array_keys($references), array_keys($this->relations($kind)))) {
            throw ValidationException::withMessages(['references' => 'Unsupported relationship.']);
        }
        $payload = [];
        foreach ($references as $key => $id) {
            $r = $this->relation($kind, $key);
            if ($id !== null && $id !== '') {
                Validator::make(['reference' => $id], ['reference' => ['required', 'integer', 'min:1']])->validate();
                abort_unless($this->queryTable($r['parent_source_table'])->where('p.city_id', $city)->where('p.source_id', $source)->where('p.id', $id)->exists(), 422, 'Relationship is not available in this source.');
            }
            $payload[$r['resolved_column']] = $id === null || $id === '' ? null : (int) $id;
            $payload[$r['state_column']] = $payload[$r['resolved_column']] === null ? 'null' : 'resolved';
        }

        return $payload;
    }

    public function searchFamilies(WebAccount $account, int $city, string $number): Collection
    {
        $this->authorize($account, $city);
        $q = $this->queryTable('family')->where('p.city_id', $city);
        if ($number !== '') {
            $q->where('p.c_F_Number', $number);
        }

        return $q->orderBy('p.id')->limit(100)->get();
    }

    public function createFamily(WebAccount $account, int $city, array $fields, bool $confirmDuplicate = false): int
    {
        return DB::transaction(function () use ($account, $city, $fields, $confirmDuplicate) {
            $this->authorize($account, $city, true);
            $payload = $this->validateFields('family', $fields);
            if (! $confirmDuplicate && ($payload['c_F_Number'] ?? '') !== '' && $this->searchFamilies($account, $city, $payload['c_F_Number'])->isNotEmpty()) {
                throw ValidationException::withMessages(['duplicate' => 'Review existing families and explicitly confirm creating a separate family.']);
            }
            $id = $this->insert('family', $city, $this->source($city), $payload);
            $this->audit->record($account->id, 'people.created', 'succeeded', $city, subjectType: 'family_entity', subjectId: $id);

            return $id;
        });
    }

    public function attachFamily(WebAccount $account, int $personId, int $familyId, array $fields, string $version, ?int $relatedPersonId = null, array $references = [], ?int $membershipId = null): void
    {
        $this->mutate($account, $personId, $version, function ($person) use ($account, $familyId, $fields, $relatedPersonId, $references, $membershipId) {
            $family = $this->row($account, 'family', $familyId, true);
            abort_unless($family->city_id === $person->city_id && $family->source_id === $person->source_id, 422);
            if ($membershipId !== null) {
                $membership = $this->row($account, 'families', $membershipId, true);
                $memberRef = $this->relation('families', 'Person_ID');
                abort_unless((int) $membership->{$memberRef['resolved_column']} === (int) $person->id
                    && (int) $membership->city_id === (int) $person->city_id && (int) $membership->source_id === (int) $person->source_id, 404);
                [$fields, $references] = $this->changes('families', $membership, $fields, $references);
            }
            $payload = $this->validateFields('families', $fields) + $this->referencePayload($account, 'families', (int) $person->city_id, (int) $person->source_id, $references);
            $links = ['Person_ID' => (int) $person->id, 'Family_ID' => $familyId];
            $providerRef = $this->relation('families', 'P_Provider_ID');
            $currentProvider = isset($membership) ? ($membership->{$providerRef['resolved_column']} ?? null) : null;
            if ($relatedPersonId !== null && $relatedPersonId !== (int) $currentProvider) {
                $provider = $this->row($account, 'person', $relatedPersonId);
                abort_unless($provider->city_id === $person->city_id && $provider->source_id === $person->source_id, 422);
                $links['P_Provider_ID'] = $relatedPersonId;
            } elseif ($relatedPersonId === null && $currentProvider !== null) {
                $payload += [$providerRef['resolved_column'] => null, $providerRef['state_column'] => 'null'];
            }
            foreach ($links as $key => $id) {
                $r = $this->relation('families', $key);
                $payload += [$r['resolved_column'] => $id, $r['state_column'] => 'resolved'];
            }
            if ($membershipId === null) {
                $this->insert('families', (int) $person->city_id, (int) $person->source_id, $payload);
            } else {
                DB::table('op_person_family')->where('id', $membershipId)->update($payload);
            }
        });
    }

    public function saveDetail(WebAccount $account, int $personId, string $kind, array $fields, string $version, ?int $detailId = null, array $references = []): void
    {
        abort_unless(in_array($kind, ['education', 'workexp', 'course'], true), 422);
        $this->mutate($account, $personId, $version, function ($person) use ($account, $kind, $fields, $detailId, $references) {
            $r = $this->relation($kind, 'Person_ID');
            if ($detailId !== null) {
                $child = $this->row($account, $kind, $detailId, true);
                abort_unless((int) $child->{$r['resolved_column']} === (int) $person->id && $child->city_id === $person->city_id && $child->source_id === $person->source_id, 404);
                [$fields, $references] = $this->changes($kind, $child, $fields, $references);
                $payload = $this->validateFields($kind, $fields) + $this->referencePayload($account, $kind, (int) $person->city_id, (int) $person->source_id, $references);
                if ($payload !== []) {
                    DB::table($this->table($kind)['name'])->where('id', $detailId)->update($payload);
                }
            } else {
                $payload = $this->validateFields($kind, $fields) + $this->referencePayload($account, $kind, (int) $person->city_id, (int) $person->source_id, $references);
                $this->insert($kind, (int) $person->city_id, (int) $person->source_id, $payload + [$r['resolved_column'] => $person->id, $r['state_column'] => 'resolved']);
            }
        });
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

                return (object) ['id' => $row->id, 'label' => implode(' · ', $labels) ?: '#'.$row->id];
            });
        }

        return $result;
    }

    public function providers(WebAccount $account, int $city): Collection
    {
        $this->authorize($account, $city);

        return $this->queryTable('person')->where('p.city_id', $city)->orderBy('p.id')->limit(500)->get()
            ->map(fn ($row) => (object) ['id' => $row->id, 'label' => $row->c_P_FirstName.' '.$row->c_P_LastName.' · '.($row->c_P_NationalNumber ?? '')]);
    }
}
