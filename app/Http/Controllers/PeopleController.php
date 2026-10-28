<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeopleWriteRequest;
use App\Http\Requests\ProjectSearchRequest;
use App\Services\AccountAccess;
use App\Services\People\PeopleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class PeopleController
{
    /** Type-ahead matches for the person pickers (existing applicant, linked person, family provider): JSON `[{id, label}]`. */
    public function lookup(Request $request, string $locale, PeopleService $people): JsonResponse
    {
        $input = $request->validate(['city_id' => ['required', 'integer', 'between:1,5'], 'q' => ['nullable', 'string', 'max:200']]);

        return response()->json($people->lookup($request->user(), (int) $input['city_id'], (string) ($input['q'] ?? ''))->values());
    }

    public function index(ProjectSearchRequest $request, PeopleService $people, AccountAccess $access): View
    {
        $search = $request->validated('search') ?? '';
        $selectedCities = array_map('intval', $request->validated('cities') ?? []);

        return view('people.index', [
            'people' => $people->search($request->user(), $selectedCities, $search),
            'cities' => $access->cities($request->user(), 'people.view'),
            'manageCities' => $access->cities($request->user(), 'people.manage'),
            'search' => $search, 'selectedCities' => $selectedCities,
            'createToken' => $this->createToken($request, 'family'),
        ]);
    }

    public function create(Request $request, PeopleService $people, AccountAccess $access): View
    {
        $data = $request->validate(['city_id' => ['required', 'integer', 'between:1,5']]);
        $city = (int) $data['city_id'];
        abort_unless($access->check($request->user(), $city, 'people.view') && $access->check($request->user(), $city, 'people.manage'), 403);

        return view('people.create', [
            'cityId' => $city, 'fields' => $people->fields('person'),
            'options' => $people->options($request->user(), $city, 'person'),
            'createToken' => $this->createToken($request, 'person'),
        ]);
    }

    public function store(PeopleWriteRequest $request, string $locale, PeopleService $people): RedirectResponse
    {
        $id = $this->createOnce($request, 'person', fn () => $people->create($request->user(), (int) $request->validated('city_id'), $request->payload(), $request->boolean('confirm_duplicate'), $request->validated('references') ?? []));

        return redirect()->route('people.show', ['locale' => $locale, 'person' => $id])->with('status', __('people.saved'));
    }

    public function show(Request $request, string $locale, string $person, PeopleService $people, AccountAccess $access): View
    {
        $data = $people->find($request->user(), $this->id($person));
        $city = (int) $data['person']->city_id;
        $canManage = $access->check($request->user(), $city, 'people.manage');
        $metadata = $options = [];
        foreach (['person', 'families', 'education', 'workexp', 'course'] as $kind) {
            $metadata[$kind] = $people->fields($kind);
            $options[$kind] = $canManage ? $people->options($request->user(), $city, $kind) : [];
        }
        $filters = $request->validate(['family_search' => ['nullable', 'string', 'max:50']]);
        // The provider picker searches as the user types; only the chosen person's label is rendered with the form.
        $providerIds = $data['families']->pluck('providerId')->filter()->map(fn ($id) => (int) $id);
        if ($canManage && is_numeric($request->old('related_person_id'))) {
            $providerIds->push((int) $request->old('related_person_id'));
        }
        $providerLabels = $canManage ? $providerIds->unique()->mapWithKeys(fn ($id) => [$id => $people->personLabel($request->user(), $city, $id)])->filter()->all() : [];

        return view('people.show', $data + [
            'canManage' => $canManage, 'metadata' => $metadata, 'options' => $options,
            'providerLabels' => $providerLabels,
            'familySearch' => $filters['family_search'] ?? '',
            'familyChoices' => $canManage && ($filters['family_search'] ?? '') !== ''
                ? $people->searchFamilies($request->user(), $city, $filters['family_search']) : collect(),
        ]);
    }

    public function update(PeopleWriteRequest $request, string $locale, string $person, PeopleService $people): RedirectResponse
    {
        $people->update($request->user(), $this->id($person), $request->payload(), $request->validated('version') ?? '', $request->validated('references') ?? [], $request->boolean('confirm_duplicate'));

        return $this->back($locale, $person);
    }

    public function family(PeopleWriteRequest $request, string $locale, string $person, PeopleService $people): RedirectResponse
    {
        $people->attachFamily($request->user(), $this->id($person), (int) $request->validated('family_id'), $request->payload(), $request->validated('version') ?? '', $request->validated('related_person_id') ? (int) $request->validated('related_person_id') : null, $request->validated('references') ?? [], $request->validated('membership_id') ? (int) $request->validated('membership_id') : null);

        return $this->back($locale, $person);
    }

    public function storeFamily(PeopleWriteRequest $request, string $locale, PeopleService $people): RedirectResponse
    {
        $this->createOnce($request, 'family', fn () => $people->createFamily($request->user(), (int) $request->validated('city_id'), $request->payload(), $request->boolean('confirm_duplicate')));

        return redirect()->route('people.index', ['locale' => $locale])->with('status', __('people.family_saved'));
    }

    public function detail(PeopleWriteRequest $request, string $locale, string $person, string $kind, PeopleService $people): RedirectResponse
    {
        $people->saveDetail($request->user(), $this->id($person), $kind, $request->payload(), $request->validated('version') ?? '', $request->validated('detail_id') ? (int) $request->validated('detail_id') : null, $request->validated('references') ?? []);

        return $this->back($locale, $person);
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }

    private function createToken(Request $request, string $kind): string
    {
        $tokens = $request->session()->get('people.create_tokens', []);
        $old = $request->old('create_token');
        if (is_string($old) && isset($tokens[$old]) && $tokens[$old]['kind'] === $kind && $tokens[$old]['account'] === $request->user()->id && $tokens[$old]['id'] === null) {
            return $old;
        }
        $token = Str::random(64);
        $tokens[$token] = ['kind' => $kind, 'account' => $request->user()->id, 'id' => null];
        $request->session()->put('people.create_tokens', array_slice($tokens, -32, null, true));

        return $token;
    }

    /** Routes hold the session lock so a double click cannot create two roots. */
    private function createOnce(PeopleWriteRequest $request, string $kind, callable $create): int
    {
        $token = $request->validated('create_token');
        $tokens = $request->session()->get('people.create_tokens', []);
        $receipt = is_string($token) ? ($tokens[$token] ?? null) : null;
        abort_unless($receipt && $receipt['kind'] === $kind && $receipt['account'] === $request->user()->id, 419);
        if ($receipt['id'] !== null) {
            abort_unless(app(AccountAccess::class)->check($request->user(), (int) $receipt['city'], 'people.manage'), 403);

            return (int) $receipt['id'];
        }
        $id = $create();
        $tokens[$token]['id'] = $id;
        $tokens[$token]['city'] = (int) $request->validated('city_id');
        $request->session()->put('people.create_tokens', $tokens);

        return $id;
    }

    private function back(string $locale, string $person): RedirectResponse
    {
        return redirect()->route('people.show', ['locale' => $locale, 'person' => $person])->with('status', __('people.saved'));
    }
}
