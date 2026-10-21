@php
    $ar = app()->isLocale('ar');
    $locale = app()->getLocale();
    $mine = old('_form') === 'purchasing';
    $slots = $canManage ? 3 : 0;
    $day = fn ($value) => substr((string) $value, 0, 10);
@endphp
<x-layouts.app :title="__('finance.purchasing.title')" :operational="true">
    <a class="back-link" href="{{ route('applications.show', ['locale' => $locale, 'application' => $applicationId]) }}">{{ __('finance.back') }}</a>
    <div class="page-heading"><div><p class="eyebrow">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ $project->c_MP_ID }}</p><h1>{{ __('finance.purchasing.title') }}</h1><p class="page-description">{{ __('finance.purchasing.scope') }}</p></div></div>
    @include('people.messages')
    <section class="card people-form">
        @if($canManage)<form method="POST" action="{{ route('purchasing.save', ['locale' => $locale, 'application' => $applicationId]) }}">@csrf<input type="hidden" name="_form" value="purchasing"><input type="hidden" name="version" value="{{ $version }}">@endif
        <div class="table-wrap"><table class="table finance-table"><thead><tr><th>#</th><th>{{ __('finance.purchasing.name') }}</th><th>{{ __('finance.purchasing.brand') }}</th><th>{{ __('finance.purchasing.category') }}</th><th>{{ __('finance.purchasing.quantity') }}</th><th>{{ __('finance.purchasing.price') }}</th><th>{{ __('finance.loan.currency') }}</th><th>{{ __('finance.purchasing.invoice_date') }}</th><th>{{ __('finance.purchasing.invoice_number') }}</th><th>{{ __('finance.purchasing.trader') }}</th></tr></thead><tbody>
        @foreach($items as $i => $item)
            @if($canManage)
            @php $v = fn ($key, $stored) => $mine ? old('rows.'.$i.'.'.$key, $stored) : $stored; @endphp
            <tr><td>{{ $item->id }}<input type="hidden" name="rows[{{ $i }}][id]" value="{{ $item->id }}"></td>
                <td><input class="input" name="rows[{{ $i }}][name]" value="{{ $v('name', $item->c_ItemName) }}" maxlength="300" aria-label="{{ __('finance.purchasing.name') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][brand]" value="{{ $v('brand', $item->c_Brand) }}" maxlength="300" aria-label="{{ __('finance.purchasing.brand') }}"></td>
                <td><select class="input" name="rows[{{ $i }}][category_id]" aria-label="{{ __('finance.purchasing.category') }}"><option value="">—</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) $v('category_id', $item->category_id) === (string) $category->id)>{{ $ar ? ($category->c_Name ?? $category->c_Name_en) : ($category->c_Name_en ?? $category->c_Name) }}</option>@endforeach</select></td>
                <td><input class="input" name="rows[{{ $i }}][quantity]" value="{{ $v('quantity', $item->c_Quantity) }}" inputmode="numeric" aria-label="{{ __('finance.purchasing.quantity') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][price]" value="{{ $v('price', \App\Services\Finance\Money::canonical($item->c_Price)) }}" inputmode="decimal" aria-label="{{ __('finance.purchasing.price') }}"></td>
                <td><select class="input" name="rows[{{ $i }}][currency]" aria-label="{{ __('finance.loan.currency') }}">@foreach(\App\Services\Finance\PurchasingService::CURRENCIES as $code)<option value="{{ $code }}" @selected((string) $v('currency', $item->c_Currency) === $code)>{{ $code }}</option>@endforeach</select></td>
                <td><input class="input" type="date" name="rows[{{ $i }}][invoice_date]" value="{{ $v('invoice_date', $day($item->c_InvoiceDate)) }}" aria-label="{{ __('finance.purchasing.invoice_date') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][invoice_number]" value="{{ $v('invoice_number', $item->c_InvoiceNumber) }}" maxlength="100" aria-label="{{ __('finance.purchasing.invoice_number') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][trader]" value="{{ $v('trader', $item->c_TraderName) }}" maxlength="300" aria-label="{{ __('finance.purchasing.trader') }}"></td></tr>
            @else
            <tr><td>{{ $item->id }}</td><td>{{ $item->c_ItemName }}</td><td>{{ $item->c_Brand }}</td><td>{{ optional($categories->firstWhere('id', $item->category_id))->c_Name }}</td><td>{{ $item->c_Quantity }}</td><td><bdi>{{ \App\Services\Finance\Money::canonical($item->c_Price) }}</bdi></td><td>{{ $item->c_Currency }}</td><td>{{ $day($item->c_InvoiceDate) }}</td><td>{{ $item->c_InvoiceNumber }}</td><td>{{ $item->c_TraderName }}</td></tr>
            @endif
        @endforeach
        @for($s = 0; $s < $slots; $s++)
            @php $i = $items->count() + $s; $n = fn ($key) => $mine ? old('rows.'.$i.'.'.$key) : ''; @endphp
            <tr><td>+<input type="hidden" name="rows[{{ $i }}][id]" value=""></td>
                <td><input class="input" name="rows[{{ $i }}][name]" value="{{ $n('name') }}" maxlength="300" aria-label="{{ __('finance.purchasing.name') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][brand]" value="{{ $n('brand') }}" maxlength="300" aria-label="{{ __('finance.purchasing.brand') }}"></td>
                <td><select class="input" name="rows[{{ $i }}][category_id]" aria-label="{{ __('finance.purchasing.category') }}"><option value="">—</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) $n('category_id') === (string) $category->id)>{{ $ar ? ($category->c_Name ?? $category->c_Name_en) : ($category->c_Name_en ?? $category->c_Name) }}</option>@endforeach</select></td>
                <td><input class="input" name="rows[{{ $i }}][quantity]" value="{{ $n('quantity') }}" inputmode="numeric" aria-label="{{ __('finance.purchasing.quantity') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][price]" value="{{ $n('price') }}" inputmode="decimal" aria-label="{{ __('finance.purchasing.price') }}"></td>
                <td><select class="input" name="rows[{{ $i }}][currency]" aria-label="{{ __('finance.loan.currency') }}">@foreach(\App\Services\Finance\PurchasingService::CURRENCIES as $code)<option value="{{ $code }}" @selected((string) $n('currency') === $code)>{{ $code }}</option>@endforeach</select></td>
                <td><input class="input" type="date" name="rows[{{ $i }}][invoice_date]" value="{{ $n('invoice_date') }}" aria-label="{{ __('finance.purchasing.invoice_date') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][invoice_number]" value="{{ $n('invoice_number') }}" maxlength="100" aria-label="{{ __('finance.purchasing.invoice_number') }}"></td>
                <td><input class="input" name="rows[{{ $i }}][trader]" value="{{ $n('trader') }}" maxlength="300" aria-label="{{ __('finance.purchasing.trader') }}"></td></tr>
        @endfor
        @if($items->isEmpty() && ! $canManage)<tr><td colspan="10">{{ __('finance.purchasing.none') }}</td></tr>@endif
        </tbody></table></div>
        @if($canManage)<x-ui.button type="submit">{{ __('finance.save') }}</x-ui.button></form>@endif
    </section>
</x-layouts.app>
