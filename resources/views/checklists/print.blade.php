@php
    $ar = app()->isLocale('ar');
    $h = $header;
    $value = fn ($name) => $h->{$name} ?? null;
    $flag = fn ($name) => match ((string) ($h->{$name} ?? '')) { '1' => __('lifecycle.checklist.yes'), '0' => __('lifecycle.checklist.no'), '-1' => __('lifecycle.checklist.na'), '' => '—', default => (string) $h->{$name} };
    $memberRef = collect(\Database\Schema\OperationalManifest::load()['relationships'])->first(fn ($r) => $r['child_source_table'] === 'person_checklistmembers' && $r['child_columns'] === ['Member_ID']);
    $label = function ($location, $row) use ($catalog, $memberRef) {
        $id = $row->{$memberRef['state_column']} === 'resolved' ? (string) $row->{$memberRef['resolved_column']} : '';
        $member = collect($catalog[$location])->first(fn ($m) => (string) $m->id === $id);

        return $member?->label ?? ($id === '' ? '—' : __('people.retained_reference').' #'.$id);
    };
    $locations = ['k' => __('lifecycle.checklist.key_people'), 't' => __('lifecycle.checklist.team'), 'c' => __('lifecycle.checklist.committee')];
    $person = $people->firstWhere('id', $personId);
    $text = fn ($name) => ($v = $value($name)) === null || $v === '' ? '—' : $v;
    $rows = [
        __('lifecycle.checklist.visits') => [
            [__('lifecycle.checklist.fields.c_HasHomeVisit'), $flag('c_HasHomeVisit').' · '.$text('c_HVisit_Date').' · '.$text('c_HVisit_Time')], [__('lifecycle.checklist.fields.note'), $text('c_HVisit_Note')],
            [__('lifecycle.checklist.fields.c_HasProjectVisit'), $flag('c_HasProjectVisit').' · '.$text('c_PVisit_Date').' · '.$text('c_PVisit_Time')], [__('lifecycle.checklist.fields.note'), $text('c_PVisit_Note')],
            [__('lifecycle.checklist.fields.visits_report'), $text('c_VisitsReport')],
        ],
        __('lifecycle.checklist.experience') => [
            [__('lifecycle.checklist.fields.c_NeedProTest'), $flag('c_NeedProTest')], [__('lifecycle.checklist.fields.c_HasProTest'), $flag('c_HasProTest').' · '.$text('c_ProTest_Date')],
            [__('lifecycle.checklist.fields.note'), $text('c_ProTest_Note')], [__('lifecycle.checklist.fields.result'), $text('c_ProTest_Result')],
            [__('lifecycle.checklist.fields.c_HasProTrain'), $flag('c_HasProTrain')], [__('lifecycle.checklist.fields.c_ProTrain_Trainer'), $text('c_ProTrain_Trainer')],
            [__('lifecycle.checklist.fields.c_ProTrain_Place'), $text('c_ProTrain_Place')], [__('lifecycle.checklist.fields.c_ProTrain_BDate'), $text('c_ProTrain_BDate').' → '.$text('c_ProTrain_EDate')],
            [__('lifecycle.checklist.fields.c_ProTrain_Result'), $text('c_ProTrain_Result')],
            [__('lifecycle.checklist.fields.c_HasMngTrain'), $flag('c_HasMngTrain')], [__('lifecycle.checklist.fields.c_MngTrain_Trainer'), $text('c_MngTrain_Trainer')],
            [__('lifecycle.checklist.fields.c_MngTrain_Place'), $text('c_MngTrain_Place')], [__('lifecycle.checklist.fields.c_MngTrain_Date'), $text('c_MngTrain_Date')],
            [__('lifecycle.checklist.fields.c_MngTrain_Result'), $text('c_MngTrain_Result')],
        ],
        __('lifecycle.checklist.reviews') => [
            [__('lifecycle.checklist.fields.team_date'), $text('c_TeamDate')], [__('lifecycle.checklist.fields.committee_date'), $text('c_CommityDate')],
            [__('lifecycle.checklist.fields.c_RejectMsg'), $flag('c_RejectMsg').' · '.$text('c_RejectMsgDate')], [__('lifecycle.checklist.fields.notes'), $text('c_Notes')],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('lifecycle.checklist.title') }} · {{ $project->c_MP_ID }} · {{ __('ui.brand') }}</title>
    <style>
        :root { color-scheme: light; }
        body { font-family: "Somar Sans", "Segoe UI", Tahoma, Arial, sans-serif; color: #111; background: #fff; margin: 0; padding: 1.5rem; line-height: 1.5; }
        main { max-width: 190mm; margin: 0 auto; }
        h1 { font-size: 1.4rem; margin: 0 0 .25rem; } h2 { font-size: 1.05rem; margin: 1.25rem 0 .4rem; border-bottom: 2px solid #111; padding-bottom: .15rem; }
        .meta { color: #333; margin: 0 0 1rem; }
        table { width: 100%; border-collapse: collapse; margin-bottom: .5rem; }
        th, td { border: 1px solid #888; padding: .3rem .5rem; text-align: start; vertical-align: top; overflow-wrap: anywhere; white-space: pre-wrap; }
        th { width: 34%; background: #f1f1f1; font-weight: 600; }
        thead th { width: auto; }
        .bar { display: flex; gap: .75rem; margin-bottom: 1rem; }
        .bar a, .bar button { font: inherit; padding: .5rem 1rem; border: 1px solid #111; background: #fff; color: #111; border-radius: 4px; text-decoration: none; cursor: pointer; }
        tr, h2 { break-inside: avoid; }
        @page { size: A4; margin: 15mm; }
        @media print { body { padding: 0; } .bar { display: none; } }
    </style>
</head>
<body>
<main>
    <div class="bar"><button type="button" onclick="window.print()">{{ __('lifecycle.checklist.print') }}</button><a href="{{ route('checklists.show', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $personId] + ($h ? ['header' => $h->id] : [])) }}">{{ __('applications.back') }}</a></div>
    <h1>{{ __('lifecycle.checklist.title') }}</h1>
    <p class="meta">{{ $ar ? $project->city_name_ar : $project->city_name_en }} · {{ __('applications.fields.MP_ID') }} <bdi>{{ $project->c_MP_ID }}</bdi> · {{ $project->c_MP_Name }}<br>{{ __('lifecycle.checklist.beneficiary') }}: {{ $person?->label }}<br>{{ __('lifecycle.checklist.printed') }} {{ now()->format('Y-m-d H:i') }} (UTC)</p>
    @if($h === null)
        <p>{{ __('lifecycle.checklist.none_saved') }}</p>
    @else
        @foreach($rows as $heading => $lines)
            <h2>{{ $heading }}</h2>
            <table><tbody>@foreach($lines as [$name, $cell])<tr><th scope="row">{{ $name }}</th><td>{{ $cell }}</td></tr>@endforeach</tbody></table>
        @endforeach
    @endif
    @foreach($locations as $location => $heading)
        @php $group = $members->where('c_Location', $location)->values(); @endphp
        <h2>{{ $heading }}</h2>
        @if($group->isEmpty())<p>—</p>@else
        <table><thead><tr><th>{{ __('lifecycle.checklist.fields.member') }}</th>@if($location === 'k')<th>{{ __('lifecycle.checklist.fields.person_name') }}</th><th>{{ __('lifecycle.checklist.fields.role') }}</th>@else<th>{{ __('lifecycle.checklist.fields.opinion') }}</th>@endif<th>{{ __('lifecycle.checklist.fields.note') }}</th></tr></thead><tbody>
        @foreach($group as $row)<tr><td>{{ $label($location, $row) }}</td>@if($location === 'k')<td>{{ $row->c_NameNotes ?: '—' }}</td><td>{{ $row->c_RoleNotes ?: '—' }}</td>@else<td>{{ match ((string) $row->c_Opinion) { '1' => __('lifecycle.checklist.for'), '0' => __('lifecycle.checklist.against'), default => __('lifecycle.checklist.na') } }}</td>@endif<td>{{ $row->c_Notes ?: '—' }}</td></tr>@endforeach
        </tbody></table>@endif
    @endforeach
</main>
</body>
</html>
