{{-- One child grid of the application (power sources, risks, budget materials, agriculture and rehabilitation rows): existing rows and an add row, each its own form until P4.6. --}}
@forelse($$kind as $detail)
<details class="people-history" @if(old('_form') === $kind.'-'.$detail->id) open @endif><summary>{{ __('people.entry') }} #{{ $detail->id }}@if(in_array($kind, ['agriculture', 'rehab_answers'], true)) · {{ $detail->referenceDescriptions['Question_ID'] ?? '' }} — {{ $detail->referenceDescriptions['Answer_ID'] ?? '' }}@endif @if($kind === 'rehab_answers') · {{ $detail->referenceDescriptions['MicroRehab_ID'] ?? '' }}@endif</summary>
@if($canManage) @include('applications.detail-form', ['row' => $detail, 'prefix' => $kind.'-'.$detail->id])
@else @include('applications.read', ['fields' => $metadata[$kind], 'row' => $detail]) @endif
</details>@empty<p class="field-hint">{{ __('people.no_entries') }}</p>@endforelse
@if($canManage && ($kind !== 'rehab_answers' || $rehab->isNotEmpty()))<details class="people-history" @if(old('_form') === $kind.'-new') open @endif><summary>{{ __('people.add_entry') }}</summary>@include('applications.detail-form', ['row' => null, 'prefix' => $kind.'-new'])</details>@endif
