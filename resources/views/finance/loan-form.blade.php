@php
    $mineForm = old('_form') === $scope;
    $pick = fn (string $key, $stored) => $mineForm ? old($key, $stored) : $stored;
@endphp
<form method="POST" action="{{ route('finance.loan', ['locale' => app()->getLocale(), 'application' => $applicationId]) }}">@csrf
    <input type="hidden" name="_form" value="{{ $scope }}"><input type="hidden" name="version" value="{{ $version }}"><input type="hidden" name="loan_id" value="{{ $loanIdValue }}">
    <div class="people-fields">
        <div class="field"><label for="{{ $scope }}-currency">{{ __('finance.loan.currency') }}</label>
            <select class="input" id="{{ $scope }}-currency" name="currency" required>
                @php $currencyValue = (string) $pick('currency', $loan?->c_Currency ?? ''); @endphp
                <option value="">—</option>@foreach(['USD', 'SP'] as $code)<option value="{{ $code }}" @selected($currencyValue === $code)>{{ $code }}</option>@endforeach
            </select></div>
        <div class="field"><label for="{{ $scope }}-rate">{{ __('finance.loan.exchange_rate') }}</label><input class="input" id="{{ $scope }}-rate" name="exchange_rate" value="{{ $pick('exchange_rate', $values['rate_exchange'] ?? '') }}" inputmode="decimal"></div>
        <div class="field"><label for="{{ $scope }}-amount">{{ __('finance.loan.amount') }}</label><input class="input" id="{{ $scope }}-amount" name="amount" value="{{ $pick('amount', $values['amount'] ?? '') }}" inputmode="decimal" required></div>
        <div class="field"><label for="{{ $scope }}-date">{{ __('finance.loan.date') }}</label><input class="input" id="{{ $scope }}-date" type="date" name="date" value="{{ $pick('date', substr((string) ($loan?->c_Loan_DateTaken ?? ''), 0, 10)) }}"></div>
        <div class="field"><label for="{{ $scope }}-returned">{{ __('finance.loan.returned') }}</label><input class="input" id="{{ $scope }}-returned" name="returned" value="{{ $pick('returned', $values['returned'] ?? '') }}" inputmode="decimal"></div>
        <div class="field"><label for="{{ $scope }}-payment">{{ __('finance.loan.payment') }}</label><input class="input" id="{{ $scope }}-payment" name="payment" value="{{ $pick('payment', $values['payment'] ?? '') }}" inputmode="decimal"><small>{{ __('finance.loan.derived') }}</small></div>
        <div class="field"><label for="{{ $scope }}-receipt">{{ __('finance.loan.receipt') }}</label><input class="input" id="{{ $scope }}-receipt" name="receipt" value="{{ $pick('receipt', $loan?->c_Loan_ReceiptID ?? '') }}" maxlength="50"></div>
        <div class="field"><label for="{{ $scope }}-group">{{ __('finance.donor.group') }}</label>
            <select class="input" id="{{ $scope }}-group" name="group_id"><option value="">{{ __('finance.loan.keep_group') }}</option>
                @foreach($groups as $candidate)<option value="{{ $candidate->id }}" @selected((string) $pick('group_id', $group?->id) === (string) $candidate->id)>{{ $candidate->c_Name }}</option>@endforeach
            </select></div>
        <div class="field"><label for="{{ $scope }}-note">{{ __('finance.loan.note') }}</label><textarea class="input" id="{{ $scope }}-note" name="note" rows="3" maxlength="65535">{{ $pick('note', $loan?->c_Loan_Note ?? '') }}</textarea></div>
    </div>
    <x-ui.button type="submit">{{ __('finance.loan.save') }}</x-ui.button>
</form>
