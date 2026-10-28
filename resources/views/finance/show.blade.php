@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $stateName = $current ? ($ar ? ($current->state_name_ar ?? $current->state_name_en) : ($current->state_name_en ?? $current->state_name_ar)) : null;
    $canEdit = $canManage && $ineligible === null;
    $hasLoans = $loans->isNotEmpty();
    $form = old('_form');
@endphp
<x-layouts.app :title="__('finance.title')" :operational="true">
    <a class="back-link" href="{{ route('applications.show', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('finance.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('finance.title') }}</h1><p class="page-description">{{ __('finance.scope') }}</p></div></div>
    @include('people.messages')

    <section class="card people-form" id="donor"><h2>{{ __('finance.donor.title') }}</h2>
        <p>{{ __('finance.state') }}: <strong>{{ $stateName ?? __('finance.none') }}</strong> · {{ __('finance.donor.donor') }}: <strong>{{ $donor?->c_Name ?? __('finance.none') }}</strong> · {{ __('finance.donor.group') }}: <strong>{{ $group?->c_Name ?? __('finance.none') }}</strong></p>
        @if($ineligible)<div class="form-message" role="note">{{ __('finance.errors.ineligible_'.$ineligible) }}</div>@endif
        @if($canManage && $funded)
        <details class="people-history" @if($form === 'donor') open @endif><summary>{{ __('finance.donor.change') }}</summary>
            <form method="POST" action="{{ route('finance.donor', ['locale' => $locale, 'application' => $applicationId]) }}">@csrf
                <input type="hidden" name="_form" value="donor"><input type="hidden" name="version" value="{{ $version }}">
                <div class="people-fields"><div class="field"><label for="donor-id">{{ __('finance.donor.donor') }}</label>
                    <select class="input" id="donor-id" name="donor_id" required><option value="">—</option>
                        @foreach($donors as $candidate)<option value="{{ $candidate->id }}" @selected((string) old('donor_id', $donor?->id) === (string) $candidate->id)>{{ $candidate->c_Name }}</option>@endforeach
                    </select><small>{{ __('finance.donor.rules') }}</small></div></div>
                <x-ui.button type="submit">{{ __('finance.save') }}</x-ui.button>
            </form>
        </details>
        <details class="people-history" @if($form === 'group') open @endif><summary>{{ __('finance.donor.add_group') }}</summary>
            <form method="POST" action="{{ route('finance.group', ['locale' => $locale, 'application' => $applicationId]) }}">@csrf
                <input type="hidden" name="_form" value="group">
                <div class="people-fields">
                    <div class="field"><label for="group-name">{{ __('finance.donor.group_name') }}</label><input class="input" id="group-name" name="group_name" value="{{ old('group_name') }}" maxlength="50" required></div>
                    <div class="field"><label for="group-donor">{{ __('finance.donor.donor') }}</label><select class="input" id="group-donor" name="group_donor_id" required><option value="">—</option>
                        @foreach($donors as $candidate)<option value="{{ $candidate->id }}" @selected((string) old('group_donor_id', $donor?->id) === (string) $candidate->id)>{{ $candidate->c_Name }}</option>@endforeach
                    </select></div>
                </div>
                <x-ui.button type="submit">{{ __('finance.donor.add_group') }}</x-ui.button>
            </form>
        </details>
        @endif
    </section>

    @if($visitNoticesMissing)<div class="form-message" role="note">{{ __('finance.visit_notices_missing') }}</div>@endif
    @foreach($loans as $loan)
    @php
        $row = $loan['row']; $values = $loan['values']; $summary = $loan['summary']; $scope = 'loan-'.$row->id; $mine = $form === $scope;
    @endphp
    <section class="card people-form" id="loan-{{ $row->id }}"><h2>{{ __('finance.loan.title') }} #{{ $row->id }}@if($row->c_Loan_ReceiptID) · {{ __('finance.loan.receipt') }} <bdi>{{ $row->c_Loan_ReceiptID }}</bdi>@endif</h2>
        <dl class="project-details">
            <div><dt>{{ __('finance.loan.amount') }} ({{ $row->c_Currency }})</dt><dd><bdi>{{ $values['amount'] }}</bdi></dd></div>
            @if($loan['opposite'] !== null)<div><dt>{{ __('finance.loan.opposite', ['currency' => $row->c_Currency === 'SP' ? 'USD' : 'SP']) }}</dt><dd><bdi>{{ $loan['opposite'] }}</bdi></dd></div>@endif
            <div><dt>{{ __('finance.loan.returned') }}</dt><dd><bdi>{{ $values['returned'] }}</bdi></dd></div>
            <div><dt>{{ __('finance.loan.payment') }}</dt><dd><bdi>{{ $values['payment'] }}</bdi></dd></div>
            <div><dt>{{ __('finance.loan.count') }}</dt><dd><bdi>{{ $summary['all_count'] ?? '—' }}</bdi></dd></div>
            <div><dt>{{ __('finance.loan.paid') }}</dt><dd><bdi>{{ $summary['paid_amount'] }}</bdi> ({{ $summary['paid_count'] }})</dd></div>
            <div><dt>{{ __('finance.loan.remaining') }}</dt><dd @class(['is-flagged' => $summary['near_end']])><bdi>{{ $summary['remaining'] }}</bdi>@if($summary['near_end']) · {{ __('finance.loan.near_end') }}@endif</dd></div>
            <div><dt>{{ __('finance.loan.remaining_clamped') }}</dt><dd><bdi>{{ $summary['remaining_clamped'] }}</bdi></dd></div>
            <div><dt>{{ __('finance.loan.remaining_count') }}</dt><dd><bdi>{{ $summary['remaining_count'] ?? '—' }}</bdi></dd></div>
        </dl>
        @if($canEdit)
        <details class="people-history" @if($mine) open @endif><summary>{{ __('finance.loan.edit') }}</summary>
            @include('finance.loan-form', ['scope' => $scope, 'loan' => $row, 'values' => $values, 'loanIdValue' => $row->id, 'opposite' => $loan['opposite']])
        </details>
        @endif
        <h3>{{ __('finance.installments.title') }}</h3>
        <p><small>{{ __('finance.installments.hint') }}</small></p>
        @if($canEdit)
        <form method="POST" action="{{ route('finance.installments', ['locale' => $locale, 'application' => $applicationId]) }}">@csrf
            <input type="hidden" name="_form" value="{{ $scope }}"><input type="hidden" name="version" value="{{ $version }}"><input type="hidden" name="loan_id" value="{{ $row->id }}">
        @endif
        <div class="table-wrap"><table class="table finance-table"><thead><tr><th>#</th><th>{{ __('finance.installments.amount') }} ({{ $row->c_Currency }})</th><th>{{ __('finance.installments.due') }}</th><th>{{ __('finance.installments.status') }}</th><th>{{ __('finance.installments.received') }}</th><th>{{ __('finance.installments.note') }}</th></tr></thead><tbody>
        @php $slots = $canEdit ? 3 : 0; $rowsOut = $loan['payments']; @endphp
        @foreach($rowsOut as $i => $payment)
            @if($canEdit)
            <tr><td>{{ $payment->id }}<input type="hidden" name="rows[{{ $i }}][id]" value="{{ $payment->id }}"></td>
                <td><input class="input" name="rows[{{ $i }}][amount]" value="{{ $mine ? old('rows.'.$i.'.amount', $payment->amount) : $payment->amount }}" inputmode="numeric" aria-label="{{ __('finance.installments.amount') }}"></td>
                <td><input class="input" type="date" name="rows[{{ $i }}][due_date]" value="{{ $mine ? old('rows.'.$i.'.due_date', substr((string) $payment->c_Pay_DueDate, 0, 10)) : substr((string) $payment->c_Pay_DueDate, 0, 10) }}" aria-label="{{ __('finance.installments.due') }}"></td>
                <td><select class="input" name="rows[{{ $i }}][paid]" aria-label="{{ __('finance.installments.status') }}">@php $paidNow = $mine ? old('rows.'.$i.'.paid', $payment->is_paid ? '1' : '0') : ($payment->is_paid ? '1' : '0'); @endphp<option value="0" @selected((string) $paidNow === '0')>{{ __('finance.installments.not_paid') }}</option><option value="1" @selected((string) $paidNow === '1')>{{ __('finance.installments.paid') }}</option></select></td>
                <td><input class="input" type="date" name="rows[{{ $i }}][received_date]" value="{{ $mine ? old('rows.'.$i.'.received_date', substr((string) $payment->c_Pay_RecievedOnDate, 0, 10)) : substr((string) $payment->c_Pay_RecievedOnDate, 0, 10) }}" aria-label="{{ __('finance.installments.received') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][note]" value="{{ $mine ? old('rows.'.$i.'.note', $payment->c_Pay_Note) : $payment->c_Pay_Note }}" aria-label="{{ __('finance.installments.note') }}"></td></tr>
            @else
            <tr><td>{{ $payment->id }}</td><td><bdi>{{ $payment->amount }}</bdi></td><td>{{ substr((string) $payment->c_Pay_DueDate, 0, 10) }}</td><td>{{ $payment->is_paid ? __('finance.installments.paid') : __('finance.installments.not_paid') }}</td><td>{{ substr((string) $payment->c_Pay_RecievedOnDate, 0, 10) }}</td><td>{{ $payment->c_Pay_Note }}</td></tr>
            @endif
        @endforeach
        @for($s = 0; $s < $slots; $s++)
            @php $i = $rowsOut->count() + $s; @endphp
            <tr><td>+<input type="hidden" name="rows[{{ $i }}][id]" value=""></td>
                <td><input class="input" name="rows[{{ $i }}][amount]" value="{{ $mine ? old('rows.'.$i.'.amount') : '' }}" inputmode="numeric" aria-label="{{ __('finance.installments.amount') }}"></td>
                <td><input class="input" type="date" name="rows[{{ $i }}][due_date]" value="{{ $mine ? old('rows.'.$i.'.due_date') : '' }}" aria-label="{{ __('finance.installments.due') }}"></td>
                <td><select class="input" name="rows[{{ $i }}][paid]" aria-label="{{ __('finance.installments.status') }}"><option value="0">{{ __('finance.installments.not_paid') }}</option><option value="1" @selected($mine && (string) old('rows.'.$i.'.paid') === '1')>{{ __('finance.installments.paid') }}</option></select></td>
                <td><input class="input" type="date" name="rows[{{ $i }}][received_date]" value="{{ $mine ? old('rows.'.$i.'.received_date') : '' }}" aria-label="{{ __('finance.installments.received') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][note]" value="{{ $mine ? old('rows.'.$i.'.note') : '' }}" aria-label="{{ __('finance.installments.note') }}"></td></tr>
        @endfor
        @if($rowsOut->isEmpty() && ! $canEdit)<tr><td colspan="6">{{ __('finance.installments.none') }}</td></tr>@endif
        </tbody></table></div>
        @if($canEdit)<x-ui.button type="submit">{{ __('finance.installments.save') }}</x-ui.button></form>@endif
    </section>
    @endforeach

    @if(! $hasLoans)
    <section class="card people-form" id="loan"><h2>{{ __('finance.loan.title') }}</h2>
        @if($canEdit)
            <p>{{ __('finance.loan.none_yet') }}</p>
            @include('finance.loan-form', ['scope' => 'loan-new', 'loan' => null, 'values' => null, 'loanIdValue' => ''])
        @else<p>{{ $ineligible ? __('finance.errors.ineligible_'.$ineligible) : __('finance.loan.none') }}</p>@endif
    </section>
    @endif
</x-layouts.app>
