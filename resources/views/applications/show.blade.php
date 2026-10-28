@php
    use App\Services\Applications\PaperOrder;
    // The application page is the paper form (desktop Application_New_Form), top to bottom: project form · story ·
    // applicant personal information · work and education · project information · family · losses/illness/assets ·
    // aid, debts and help · additional information. One Save writes the project, the beneficiary's link row and the
    // beneficiary's person row together (applications.update). Grids keep their own forms, placed in their paper
    // position through the `form` attribute, until P4.6 folds them into the same save.
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $recovering = old('_form') === 'application';
    $formId = 'paper-form';
    $link = $beneficiary['link'] ?? null;
    $person = $beneficiary['person'] ?? null;
    $personEditable = $canManage && ($beneficiary['canEdit'] ?? false);
    $appGroups = PaperOrder::groups('application');
    $peopleGroups = PaperOrder::groups('people');
    $personGroups = PaperOrder::groups('person');
    $pick = fn (array $all, array $keys) => array_intersect_key($all, array_flip($keys));
    $projectBlock = function (array $keys) use ($metadata, $options, $application, $formId) {
        return ['fields' => array_intersect_key($metadata['application'], array_flip($keys)), 'options' => array_intersect_key($options['application'], array_flip($keys)), 'row' => $application, 'prefix' => 'application', 'formMarker' => false, 'formId' => $formId];
    };
    $stageFull = $application->c_ApplicationState === 'project';
    $partners = $people->filter(fn ($row) => $link === null || $row->id !== $link->id);
    $supportKinds = ['loss', 'illness', 'asset', 'aid', 'help', 'homepower'];
@endphp
<x-layouts.app :title="$application->c_MP_Name" :operational="true">
    <a class="back-link" href="{{ route('applications.index', ['locale' => $locale]) }}">{{ __('applications.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $application->city_name_ar : $application->city_name_en }} · {{ $application->c_MP_ID }}</p><h1>{{ $application->c_MP_Name }}</h1><p>{{ __('applications.state') }}: {{ in_array($application->c_ApplicationState, ['zero', 'project'], true) ? __('applications.states.'.$application->c_ApplicationState) : $application->c_ApplicationState }}</p></div></div>
    <p class="field-hint"><a class="record-link" href="{{ route('details.states.export', ['locale' => $locale, 'application' => $application->id, 'format' => 'xlsx']) }}">{{ __('search.export_states') }}</a> · <a class="record-link" href="{{ route('details.states.export', ['locale' => $locale, 'application' => $application->id, 'format' => 'csv']) }}">{{ __('reports.export_csv') }}</a></p>
    @include('people.messages')
    @include('lifecycle.panel')
    @if($assessees->isNotEmpty() || $financeLinks !== [] || $fieldLinks !== [])
    <section class="card people-form"><h2>{{ __('applications.related_screens') }}</h2><div class="button-row">
        @foreach($assessees as $assessee)<a class="button button-secondary" href="{{ route('checklists.show', ['locale' => $locale, 'application' => $application->id, 'person' => $assessee->id]) }}">{{ __('lifecycle.checklist_link') }} · {{ $assessee->label }}</a>@endforeach
        @foreach(array_keys($financeLinks) as $routeName)<a class="button button-secondary" href="{{ route($routeName, ['locale' => $locale, 'application' => $application->id]) }}">{{ __('finance.links.'.explode('.', $routeName)[0]) }}</a>@endforeach
        @foreach(array_keys($fieldLinks) as $routeName)<a class="button button-secondary" href="{{ route($routeName, ['locale' => $locale, 'application' => $application->id]) }}">{{ __('field.links.'.explode('.', $routeName)[0]) }}</a>@endforeach
    </div></section>
    @endif

    @if($canManage)
    <form id="{{ $formId }}" method="POST" action="{{ route('applications.update', ['locale' => $locale, 'application' => $application->id]) }}">@csrf
        <input type="hidden" name="_form" value="application"><input type="hidden" name="city_id" value="{{ $cityId }}"><input type="hidden" name="kind" value="application">
        <input type="hidden" name="application_id" value="{{ $application->id }}"><input type="hidden" name="version" value="{{ $recovering ? old('version', '') : $version }}">
        @if($link)<input type="hidden" name="link_id" value="{{ $link->id }}">@endif
        @if($personEditable && $person)<input type="hidden" name="person_id" value="{{ $person->id }}">@endif
        @if($recovering && old('draft_id'))<input type="hidden" name="draft_id" value="{{ old('draft_id') }}"><input type="hidden" name="draft_version" value="{{ old('draft_version') }}">@endif
    </form>
    @endif

    {{-- 1. استمارة المشروع --}}
    <section class="card people-form paper-section"><h2>{{ $stageFull ? __('forms.groups.application.project') : __('applications.preliminary_form') }}</h2>
        <dl class="project-details paper-readonly"><div><dt>{{ __('applications.fields.MP_ID') }}</dt><dd><bdi>{{ $application->c_MP_ID }}</bdi></dd></div><div><dt>{{ __('applications.full_name') }}</dt><dd>{{ $link ? ($link->referenceDescriptions['Person_ID'] ?? '—') : __('applications.no_beneficiary') }}</dd></div>@if($person)<div><dt>{{ __('people.fields.P_Mobile') }}</dt><dd><bdi>{{ $person->c_P_Mobile ?? '—' }}</bdi></dd></div>@endif</dl>
        @if($canManage) @include('forms.fields', $projectBlock($appGroups['project']) + ['hidden' => $stageFull ? [] : PaperOrder::preliminaryHidden('application'), 'required' => ['c_MP_Name', 'c_MP_DateOfRequest', 'SubCategory_ID', 'Career_ID', 'MP_Type_ID', 'MP_SubType_ID']])
        @else @include('applications.read', ['fields' => $pick($metadata['application'], $appGroups['project']), 'row' => $application]) @endif
    </section>

    {{-- 2. قصة المستفيد (full form) --}}
    @if($stageFull || $application->c_ApplicationState !== 'zero' || $canManage)
    <section class="card people-form paper-section"><h2>{{ __('forms.groups.people.story') }}</h2>
        @if($link === null)<p>{{ __('applications.no_beneficiary') }}</p>
        @elseif($canLinkPeople) @include('forms.fields', ['fields' => $pick($metadata['people'], $peopleGroups['story']), 'options' => [], 'row' => $link, 'prefix' => 'link', 'recovering' => $recovering, 'fieldName' => 'link[fields]', 'nullName' => 'link[null_fields][]', 'oldBase' => 'link.', 'formMarker' => false, 'formId' => $formId])
        @else @include('applications.read', ['fields' => $pick($metadata['people'], $peopleGroups['story']), 'row' => $link]) @endif
    </section>
    @endif

    {{-- 3. المعلومات الشخصية لمقدّم الطلب --}}
    <section class="card people-form paper-section"><h2>{{ __('forms.groups.person.identity') }}</h2>
        @if($person === null)<p>{{ __('applications.no_beneficiary') }}</p>
        @elseif($personEditable) @include('forms.fields', ['fields' => $pick($beneficiary['fields'], array_merge($personGroups['identity'], $personGroups['contact'], $personGroups['military'])), 'options' => $pick($beneficiary['options'], array_merge($personGroups['identity'], $personGroups['contact'])), 'row' => $person, 'prefix' => 'person', 'recovering' => $recovering, 'fieldName' => 'person[fields]', 'refName' => 'person[references]', 'nullName' => 'person[null_fields][]', 'oldBase' => 'person.', 'groups' => true, 'hidden' => $stageFull ? [] : PaperOrder::preliminaryHidden('person'), 'formMarker' => false, 'formId' => $formId])
        @else <dl class="project-details">@foreach($pick($beneficiary['fields'], array_merge($personGroups['identity'], $personGroups['contact'], $personGroups['military'])) as $name => $field)<div><dt>{{ __('people.fields.'.$field['label']) }}</dt><dd class="preserve-text">{{ $person->{$name} ?? '—' }}</dd></div>@endforeach @include('people.references', ['row' => $person])</dl>@endif
        @if($person)<p class="field-hint"><a class="record-link" href="{{ route('people.show', ['locale' => $locale, 'person' => $person->id]) }}">{{ __('applications.edit_household') }}</a></p>@endif
    </section>

    {{-- 4. الخبرات العمليّة والتحصيل العلمي --}}
    <section class="card people-form paper-section"><h2>{{ __('forms.groups.people.work') }}</h2>
        @if($link && $canLinkPeople) @include('forms.fields', ['fields' => $pick($metadata['people'], $peopleGroups['work']), 'options' => [], 'row' => $link, 'prefix' => 'link', 'recovering' => $recovering, 'fieldName' => 'link[fields]', 'nullName' => 'link[null_fields][]', 'oldBase' => 'link.', 'formMarker' => false, 'formId' => $formId]) @endif
        <p class="field-hint">{{ __('applications.grids_on_person') }}@if($person) <a class="record-link" href="{{ route('people.show', ['locale' => $locale, 'person' => $person->id]) }}">{{ __('people.workexp') }} · {{ __('people.education') }} · {{ __('people.course') }}</a>@endif</p>
    </section>

    {{-- 5. معلومات خاصة بالمشروع (full form) --}}
    <section class="card people-form paper-section"><h2>{{ __('forms.groups.application.premises') }}</h2>
        @if($canManage) @include('forms.fields', $projectBlock($appGroups['premises'])) @else @include('applications.read', ['fields' => $pick($metadata['application'], $appGroups['premises']), 'row' => $application]) @endif
        @foreach(['powersource', 'risk', 'material'] as $kind)
        <h3 class="form-group-title">{{ __('applications.'.$kind) }}</h3>
        @include('applications.detail-section', ['kind' => $kind])
        @endforeach
    </section>

    {{-- 6. معلومات عائلة المستفيد --}}
    <section class="card people-form paper-section"><h2>{{ __('forms.groups.people.family') }}</h2>
        <dl class="project-details paper-readonly"><div><dt>{{ __('applications.family_number') }}</dt><dd><bdi>{{ $beneficiary['family'] ?? '—' }}</bdi></dd></div></dl>
        @if($link && $canLinkPeople) @include('forms.fields', ['fields' => $pick($metadata['people'], $peopleGroups['family']), 'options' => [], 'row' => $link, 'prefix' => 'link', 'recovering' => $recovering, 'fieldName' => 'link[fields]', 'nullName' => 'link[null_fields][]', 'oldBase' => 'link.', 'formMarker' => false, 'formId' => $formId]) @endif
        <p class="field-hint">{{ __('applications.members_on_person') }}@if($person) <a class="record-link" href="{{ route('people.show', ['locale' => $locale, 'person' => $person->id]) }}">{{ __('people.families') }}</a>@endif</p>
        @if($link && $canLinkPeople && isset($applicantDetails[$link->id])) @include('applications.applicant-details', ['link' => $link, 'kinds' => ['loss', 'illness', 'asset']]) @endif
    </section>

    {{-- 7. الاستفادة من جهة أخرى، الديون، مصادر المساعدة، مصادر الطاقة في المنزل --}}
    <section class="card people-form paper-section"><h2>{{ __('forms.groups.people.aid') }}</h2>
        @if($link && $canLinkPeople) @include('forms.fields', ['fields' => $pick($metadata['people'], $peopleGroups['aid']), 'options' => [], 'row' => $link, 'prefix' => 'link', 'recovering' => $recovering, 'fieldName' => 'link[fields]', 'nullName' => 'link[null_fields][]', 'oldBase' => 'link.', 'formMarker' => false, 'formId' => $formId]) @endif
        @if($link && $canLinkPeople && isset($applicantDetails[$link->id])) @include('applications.applicant-details', ['link' => $link, 'kinds' => ['aid', 'help', 'homepower']]) @endif
    </section>

    {{-- 8. معلومات إضافية --}}
    <section class="card people-form paper-section"><h2>{{ __('forms.groups.application.extra') }}</h2>
        @if($canManage) @include('forms.fields', $projectBlock(array_merge($appGroups['extra'], $appGroups['other']))) @else @include('applications.read', ['fields' => $pick($metadata['application'], array_merge($appGroups['extra'], $appGroups['other'])), 'row' => $application]) @endif
    </section>

    {{-- 9. الملاحق (زراعي / ترميم) --}}
    <section class="card people-form paper-section"><h2>{{ __('applications.annexes') }}</h2>
        @foreach(['agriculture', 'rehab', 'rehab_answers'] as $kind)
        <h3 class="form-group-title">{{ __('applications.'.$kind) }}</h3>
        @include('applications.detail-section', ['kind' => $kind])
        @endforeach
    </section>

    {{-- 10. الشركاء وربط الأشخاص --}}
    <section class="card people-form paper-section"><h2>{{ __('applications.people') }}</h2>
        @foreach($partners as $partner)
        <details class="people-history" @if(old('_form') === 'people-'.$partner->id || collect($supportKinds)->contains(fn ($kind) => str_starts_with((string) old('_form'), $kind.'-'.$partner->id.'-'))) open @endif><summary>{{ $partner->referenceDescriptions['Person_ID'] ?? '#'.$partner->id }} · {{ $partner->c_PersonType }}</summary>
        @if($canLinkPeople && in_array($partner->c_PersonType, ['مستفيد', 'شريك'], true))
            @include('applications.person-form', ['row' => $partner, 'prefix' => 'people-'.$partner->id])
        @else @include('applications.read', ['fields' => $metadata['people'], 'row' => $partner]) @endif
        @if($canLinkPeople && isset($applicantDetails[$partner->id])) @include('applications.applicant-details', ['link' => $partner, 'kinds' => $supportKinds]) @endif
        </details>
        @endforeach
        @if($link && $canLinkPeople)<details class="people-history" @if(old('_form') === 'people-'.$link->id) open @endif><summary>{{ __('applications.beneficiary_link') }} · {{ $link->referenceDescriptions['Person_ID'] ?? '#'.$link->id }}</summary>@include('applications.person-form', ['row' => $link, 'prefix' => 'people-'.$link->id, 'roleOnly' => true])</details>@endif
        @if($canLinkPeople)<details class="people-history" @if(old('_form') === 'people-new') open @endif><summary>{{ __('applications.link_person') }}</summary>@include('applications.person-form', ['row' => null, 'prefix' => 'people-new'])</details>@endif
    </section>

    @if($canManage)
    <div class="form-actions-bar" role="toolbar" aria-label="{{ __('applications.form_actions') }}">
        <button class="button button-primary" type="submit" form="{{ $formId }}">{{ __('applications.save_form') }}</button>
        <button class="button button-secondary" type="submit" form="{{ $formId }}" formnovalidate formaction="{{ route('applications.drafts.save', ['locale' => $locale]) }}">{{ __('applications.save_draft') }}</button>
        @if($canLinkPeople)<a class="button button-secondary" href="{{ route('applications.print', ['locale' => $locale, 'application' => $application->id]) }}" target="_blank" rel="noopener">{{ __('applications.print.open') }}</a>@endif
        <a class="button button-secondary" href="{{ route('applications.drafts', ['locale' => $locale, 'city_id' => $cityId]) }}">{{ __('applications.drafts') }}</a>
        @if($application->c_ApplicationState === 'zero')
        <form method="POST" action="{{ route('applications.complete', ['locale' => $locale, 'application' => $application->id]) }}" title="{{ __('applications.complete_hint') }}">@csrf
            <input type="hidden" name="city_id" value="{{ $cityId }}"><input type="hidden" name="version" value="{{ old('_form') === 'complete' ? old('version', '') : $version }}"><input type="hidden" name="_form" value="complete">
            <button class="button button-secondary" type="submit">{{ __('applications.complete') }}</button>
        </form>
        @endif
    </div>
    @elseif($canLinkPeople)<p><a class="button button-secondary" href="{{ route('applications.print', ['locale' => $locale, 'application' => $application->id]) }}" target="_blank" rel="noopener">{{ __('applications.print.open') }}</a></p>@endif
</x-layouts.app>
