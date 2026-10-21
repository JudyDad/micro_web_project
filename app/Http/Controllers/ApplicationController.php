<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationWriteRequest;
use App\Http\Requests\ProjectSearchRequest;
use App\Services\AccountAccess;
use App\Services\Applications\ApplicationService;
use App\Services\Drafts\ApplicationDraftStore;
use Database\Schema\OperationalManifest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ApplicationController
{
    public function index(ProjectSearchRequest $request, ApplicationService $applications, AccountAccess $access): View
    {
        $search = $request->validated('search') ?? '';
        $selectedCities = array_map('intval', $request->validated('cities') ?? []);

        return view('applications.index', [
            'applications' => $applications->search($request->user(), $selectedCities, $search),
            'cities' => $access->cities($request->user(), 'applications.view'),
            'manageCities' => $access->cities($request->user(), 'applications.manage'),
            'search' => $search, 'selectedCities' => $selectedCities,
        ]);
    }

    public function create(Request $request, ApplicationService $applications, AccountAccess $access): View
    {
        $city = (int) $request->validate(['city_id' => ['required', 'integer', 'between:1,5']])['city_id'];
        abort_unless($access->check($request->user(), $city, 'applications.manage'), 403);

        $fields = $applications->fields('application');
        $options = $applications->options($request->user(), $city, 'application');
        $coreFields = array_intersect_key(array_replace(array_flip(['c_MP_Name', 'c_MP_DateOfRequest', 'c_MP_Speciality']), $fields), array_flip(['c_MP_Name', 'c_MP_DateOfRequest', 'c_MP_Speciality']));
        $coreOptions = array_intersect_key(array_replace(array_flip(['SubCategory_ID', 'Career_ID', 'MP_Type_ID', 'MP_SubType_ID']), $options), array_flip(['SubCategory_ID', 'Career_ID', 'MP_Type_ID', 'MP_SubType_ID']));

        return view('applications.create', [
            'cityId' => $city, 'fields' => $fields, 'options' => $options,
            'coreFields' => $coreFields, 'coreOptions' => $coreOptions,
            'persons' => $applications->personOptions($request->user(), $city),
            'createToken' => $this->createToken($request, $city),
        ]);
    }

    public function store(ApplicationWriteRequest $request, string $locale, ApplicationService $applications, AccountAccess $access): RedirectResponse
    {
        $request->validate(['create_token' => ['required'], 'beneficiary_id' => ['required', 'integer', 'min:1']]);
        $city = (int) $request->validated('city_id');
        abort_unless($access->check($request->user(), $city, 'applications.view') && $access->check($request->user(), $city, 'applications.manage') && $access->check($request->user(), $city, 'people.view'), 403);
        $tokens = $request->session()->get('applications.create_tokens', []);
        $token = $request->validated('create_token');
        $receipt = $tokens[$token] ?? null;
        abort_unless($receipt && $receipt['account'] === $request->user()->id && $receipt['city'] === $city, 419);
        $id = $receipt['id'];
        if ($id === null) {
            $id = $applications->create($request->user(), $city, $request->payload(), (int) $request->validated('beneficiary_id'), $request->validated('references') ?? [], $request->validated('stage') ?? 'preliminary');
            $tokens[$token]['id'] = $id;
            $request->session()->put('applications.create_tokens', $tokens);
        } else {
            $applications->find($request->user(), (int) $id);
        }

        return $this->back($locale, (int) $id);
    }

    public function show(Request $request, string $locale, string $application, ApplicationService $applications, AccountAccess $access): View
    {
        $data = $applications->find($request->user(), $this->id($application));
        $city = (int) $data['application']->city_id;
        $canManage = $access->check($request->user(), $city, 'applications.manage');
        $canLinkPeople = $canManage && $access->check($request->user(), $city, 'people.view');
        $metadata = $options = [];
        foreach (['application', 'people', 'agriculture', 'rehab', 'rehab_answers', 'powersource', 'risk', 'material'] as $kind) {
            $metadata[$kind] = $applications->fields($kind);
            $options[$kind] = $canManage ? $applications->options($request->user(), $city, $kind) : [];
        }
        $relationships = collect(OperationalManifest::load()['relationships']);
        $personRelation = $relationships->first(fn ($relation) => $relation['child_source_table'] === 'person_microproject' && $relation['child_columns'] === ['Person_ID']);
        $headerRelation = $relationships->first(fn ($relation) => $relation['child_source_table'] === 'microproject_rehab_answer' && $relation['child_columns'] === ['MicroRehab_ID']);
        $applicantDetails = [];
        if ($canLinkPeople) {
            foreach ($data['people'] as $link) {
                $applicantDetails[$link->id] = $applications->applicantDetails($request->user(), (int) $application, (int) $link->id);
            }
        }

        return view('applications.show', $data + [
            'applicantDetails' => $applicantDetails,
            'personReferenceColumn' => $personRelation['resolved_column'], 'headerReferenceColumn' => $headerRelation['resolved_column'],
            'cityId' => $city, 'canManage' => $canManage, 'canLinkPeople' => $canLinkPeople,
            'metadata' => $metadata, 'options' => $options,
            'persons' => $canLinkPeople ? $applications->personOptions($request->user(), $city) : collect(),
        ]);
    }

    public function update(ApplicationWriteRequest $request, string $locale, string $application, ApplicationService $applications): RedirectResponse
    {
        $request->validate(['version' => ['required']]);
        $applications->update($request->user(), $this->id($application), $request->payload(), $request->validated('version'), $request->validated('references') ?? []);

        return $this->back($locale, $this->id($application));
    }

    public function person(ApplicationWriteRequest $request, string $locale, string $application, ApplicationService $applications): RedirectResponse
    {
        $request->validate(['version' => ['required'], 'person_id' => ['required', 'integer', 'min:1'], 'role' => ['required']]);
        $applications->linkPerson($request->user(), $this->id($application), (int) $request->validated('person_id'), $request->validated('role'), $request->payload(), $request->validated('version'), $request->validated('link_id') ? (int) $request->validated('link_id') : null);

        return $this->back($locale, $this->id($application));
    }

    public function detail(ApplicationWriteRequest $request, string $locale, string $application, string $kind, ApplicationService $applications): RedirectResponse
    {
        $request->validate(['version' => ['required']]);
        $applications->saveDetail($request->user(), $this->id($application), $kind, $request->payload(), $request->validated('version'), $request->validated('detail_id') ? (int) $request->validated('detail_id') : null, $request->validated('references') ?? [], $request->validated('parent_id') ? (int) $request->validated('parent_id') : null);

        return $this->back($locale, $this->id($application));
    }

    public function applicantDetail(ApplicationWriteRequest $request, string $locale, string $application, string $link, string $kind, ApplicationService $applications): RedirectResponse
    {
        $request->validate(['version' => ['required']]);
        $applications->saveApplicantDetail($request->user(), $this->id($application), $this->id($link), $kind, $request->payload(), $request->validated('version'), $request->validated('detail_id') ? (int) $request->validated('detail_id') : null, $request->validated('references') ?? []);

        return $this->back($locale, $this->id($application));
    }

    public function complete(ApplicationWriteRequest $request, string $locale, string $application, ApplicationService $applications): RedirectResponse
    {
        $request->validate(['version' => ['required']]);
        $applications->convertToFull($request->user(), $this->id($application), $request->validated('version'));

        return $this->back($locale, $this->id($application));
    }

    public function drafts(Request $request, ApplicationDraftStore $drafts): View
    {
        $city = (int) $request->validate(['city_id' => ['required', 'integer', 'between:1,5']])['city_id'];

        return view('applications.drafts', ['drafts' => $drafts->listing($request->user(), $city), 'cityId' => $city]);
    }

    public function saveDraft(ApplicationWriteRequest $request, string $locale, ApplicationDraftStore $drafts): RedirectResponse
    {
        $request->validate(['kind' => ['required']]);
        $payload = $request->safe()->only(['references', 'beneficiary_id', 'person_id', 'role', 'stage', 'detail_id', 'link_id', 'parent_id', 'version']);
        $payload['fields'] = $request->payload();
        $drafts->save($request->user(), (int) $request->validated('city_id'), $request->validated('kind'), $request->validated('application_id') ? (int) $request->validated('application_id') : null, $payload, $request->validated('draft_id'), $request->validated('draft_version'));

        return redirect()->route('applications.drafts', ['locale' => $locale, 'city_id' => $request->validated('city_id')])->with('status', __('applications.draft_saved'));
    }

    public function restoreDraft(Request $request, string $locale, string $draft, ApplicationDraftStore $drafts): RedirectResponse
    {
        $city = (int) $request->validate(['city_id' => ['required', 'integer', 'between:1,5']])['city_id'];
        $saved = $drafts->load($request->user(), $city, $draft);
        $payload = $saved['payload'];
        $payload['null_fields'] = array_keys(array_filter($payload['fields'] ?? [], fn ($value) => $value === null));
        $supportKind = in_array($saved['kind'], ['loss', 'illness', 'asset', 'aid', 'help', 'homepower'], true);
        $payload['_form'] = $saved['kind'] === 'application' ? 'application' : ($supportKind
            ? $saved['kind'].'-'.$payload['link_id'].'-'.($payload['detail_id'] ?? 'new')
            : $saved['kind'].'-'.($payload['detail_id'] ?? $payload['link_id'] ?? 'new'));
        $payload['draft_id'] = $saved['id'];
        $payload['draft_version'] = $saved['version'];
        $payload['city_id'] = $city;
        $route = $saved['application_id'] === null ? 'applications.create' : 'applications.show';

        return redirect()->route($route, ['locale' => $locale, 'city_id' => $city, 'application' => $saved['application_id']])->withInput($payload)->with('status', __('applications.draft_restored'));
    }

    public function discardDraft(Request $request, string $locale, string $draft, ApplicationDraftStore $drafts): RedirectResponse
    {
        $data = $request->validate(['city_id' => ['required', 'integer', 'between:1,5'], 'draft_version' => ['required', 'string', 'size:64']]);
        $drafts->discard($request->user(), (int) $data['city_id'], $draft, $data['draft_version']);

        return redirect()->route('applications.drafts', ['locale' => $locale, 'city_id' => $data['city_id']])->with('status', __('applications.draft_discarded'));
    }

    private function createToken(Request $request, int $city): string
    {
        $tokens = $request->session()->get('applications.create_tokens', []);
        $old = $request->old('create_token');
        if (is_string($old) && isset($tokens[$old]) && $tokens[$old]['account'] === $request->user()->id && $tokens[$old]['city'] === $city && $tokens[$old]['id'] === null) {
            return $old;
        }
        $token = Str::random(64);
        $tokens[$token] = ['account' => $request->user()->id, 'city' => $city, 'id' => null];
        $request->session()->put('applications.create_tokens', array_slice($tokens, -32, null, true));

        return $token;
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }

    private function back(string $locale, int $application): RedirectResponse
    {
        return redirect()->route('applications.show', compact('locale', 'application'))->with('status', __('applications.saved'));
    }
}
