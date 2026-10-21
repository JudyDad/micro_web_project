@php($support = $applicantDetails[$link->id])
<div class="people-history">
    <p><a class="record-link" href="{{ route('people.show', ['locale' => app()->getLocale(), 'person' => $support['person_id']]) }}">{{ __('applications.edit_household') }}</a></p>
    <p>{{ __('applications.support_hint') }}</p>
    @foreach(['loss', 'illness', 'asset', 'aid', 'help', 'homepower'] as $supportKind)
    <details class="people-history" @if(str_starts_with((string) old('_form'), $supportKind.'-'.$link->id.'-')) open @endif>
        <summary>{{ __('applications.'.$supportKind) }} ({{ $support['details'][$supportKind]->count() }})</summary>
        @foreach($support['details'][$supportKind] as $supportRow)
        <details class="people-history" @if(old('_form') === $supportKind.'-'.$link->id.'-'.$supportRow->id) open @endif>
            <summary>{{ __('people.entry') }} #{{ $supportRow->id }}</summary>
            @include('applications.applicant-detail-form', ['row' => $supportRow, 'prefix' => $supportKind.'-'.$link->id.'-'.$supportRow->id])
        </details>
        @endforeach
        <details class="people-history" @if(old('_form') === $supportKind.'-'.$link->id.'-new') open @endif>
            <summary>{{ __('people.add_entry') }}</summary>
            @include('applications.applicant-detail-form', ['row' => null, 'prefix' => $supportKind.'-'.$link->id.'-new'])
        </details>
    </details>
    @endforeach
</div>
