{{-- The beneficiary's support grids (losses, illness, assets, aid from other sources, help, home power) in their paper section. --}}
@php($support = $applicantDetails[$link->id])
@php($kinds = $kinds ?? ['loss', 'illness', 'asset', 'aid', 'help', 'homepower'])
<div class="people-history">
    @foreach($kinds as $supportKind)
    <h3 class="form-group-title">{{ __('applications.print.'.$supportKind) }} ({{ $support['details'][$supportKind]->count() }})</h3>
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
    @endforeach
</div>
