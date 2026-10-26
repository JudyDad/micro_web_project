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
    $person = $people->firstWhere('id', $personId);
    $text = fn ($name) => ($v = $value($name)) === null || $v === '' ? '—' : $v;
    $p = fn ($key) => __('lifecycle.checklist.p.'.$key);
    $opinion = fn ($row) => match ((string) $row->c_Opinion) { '1' => __('lifecycle.checklist.for'), '0' => __('lifecycle.checklist.against'), default => __('lifecycle.checklist.na') };
    $visits = [
        [$p('home'), $text('c_HVisit_Date'), $text('c_HVisit_Time'), $text('c_HVisit_Note')],
        [$p('project'), $text('c_PVisit_Date'), $text('c_PVisit_Time'), $text('c_PVisit_Note')],
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
        @font-face { font-family: 'Somar Sans'; src: url('/assets/fonts/SomarSans-Regular.otf') format('opentype'); font-weight: 400; }
        @font-face { font-family: 'Somar Sans'; src: url('/assets/fonts/SOMARSANS-MEDIUM.OTF') format('opentype'); font-weight: 500; }
        @font-face { font-family: 'Somar Sans'; src: url('/assets/fonts/SOMARSANS-BOLD.OTF') format('opentype'); font-weight: 700; }
        :root { color-scheme: light; --red: #be1f24; --grey: #f1f2f2; --line: #888; }
        * { box-sizing: border-box; }
        body { font-family: "Somar Sans", "Segoe UI", Tahoma, Arial, sans-serif; font-size: 10.5pt; color: #231f20; background: #fff; margin: 0; padding: 1.5rem; line-height: 1.55; }
        main { max-width: 190mm; margin: 0 auto; }
        .frame { border: 0; }
        .frame > thead > tr > td, .frame > tbody > tr > td { border: 0; padding: 0; }
        .letterhead { width: 100%; display: block; }
        h1 { font-size: 22pt; font-weight: 700; text-align: center; margin: 1.2rem 0 1.4rem; }
        section { margin-bottom: 1.4rem; }
        .sec { background: var(--red); color: #fff; text-align: center; font-weight: 700; font-size: 12pt; padding: .2rem .5rem; -webkit-print-color-adjust: exact; print-color-adjust: exact; break-after: avoid; }
        .sub { background: var(--grey); text-align: center; font-weight: 500; padding: .15rem .5rem; margin-top: .4rem; -webkit-print-color-adjust: exact; print-color-adjust: exact; break-after: avoid; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.grid th, table.grid td { border: 1px solid var(--line); border-top: 0; padding: .3rem .5rem; text-align: start; vertical-align: top; overflow-wrap: anywhere; white-space: pre-wrap; }
        table.grid th { font-weight: 500; }
        .info td { width: 50%; }
        b { font-weight: 500; }
        .box { border: 1px solid var(--line); border-top: 0; padding: .35rem .5rem; white-space: pre-wrap; overflow-wrap: anywhere; }
        .bar { display: flex; gap: .75rem; margin-bottom: 1rem; }
        .bar a, .bar button { font: inherit; padding: .5rem 1rem; border: 1px solid #111; background: #fff; color: #111; border-radius: 4px; text-decoration: none; cursor: pointer; }
        .foot { color: #555; font-size: 9pt; }
        tr { break-inside: avoid; }
        @page { size: A4; margin: 10mm 12mm 14mm; }
        @media print { body { padding: 0; } .bar { display: none; } }
    </style>
</head>
<body>
<main>
    <div class="bar"><button type="button" onclick="window.print()">{{ __('lifecycle.checklist.print') }}</button><a href="{{ route('checklists.show', ['locale' => app()->getLocale(), 'application' => $applicationId, 'person' => $personId] + ($h ? ['header' => $h->id] : [])) }}">{{ __('applications.back') }}</a></div>
    <table class="frame">
        <thead><tr><td><img class="letterhead" src="{{ asset('assets/brand/checklist-header.png') }}" alt="{{ __('ui.brand') }}"></td></tr></thead>
        <tbody><tr><td>
    <h1>Check List</h1>
    <section>
        <div class="sec">{{ $p('info') }}</div>
        <table class="grid info"><tbody>
            <tr><td><b>{{ $p('name') }}:</b> {{ $person?->label ?? '—' }}</td><td><b>{{ $p('project_no') }}:</b> <bdi>{{ $project->c_MP_ID }}</bdi></td></tr>
            <tr><td><b>{{ $p('city') }}:</b> {{ $ar ? $project->city_name_ar : $project->city_name_en }}</td><td><b>{{ $p('project_name') }}:</b> {{ $project->c_MP_Name }}</td></tr>
        </tbody></table>
    </section>
    @if($h === null)
        <p>{{ __('lifecycle.checklist.none_saved') }}</p>
    @else
    @php $key = $members->where('c_Location', 'k')->values(); @endphp
    <section>
        <div class="sec">{{ __('lifecycle.checklist.key_people') }}</div>
        <table class="grid"><thead><tr><th>{{ $p('name_col') }}</th><th>{{ $p('position') }}</th><th>{{ $p('role') }}</th><th>{{ $p('notes') }}</th></tr></thead><tbody>
        @forelse($key as $row)<tr><td>{{ $row->c_NameNotes ?: '—' }}</td><td>{{ $label('k', $row) }}</td><td>{{ $row->c_RoleNotes ?: '—' }}</td><td>{{ $row->c_Notes ?: '—' }}</td></tr>@empty<tr><td colspan="4">—</td></tr>@endforelse
        </tbody></table>
    </section>
    <section>
        <div class="sec">{{ $p('visit') }}</div>
        <table class="grid"><thead><tr><th>{{ $p('visit_type') }}</th><th>{{ $p('visit_date') }}</th><th>{{ $p('visit_time') }}</th><th>{{ $p('notes') }}</th></tr></thead><tbody>
        @foreach($visits as $v)<tr>@foreach($v as $c)<td>{{ $c }}</td>@endforeach</tr>@endforeach
        </tbody></table>
        <div class="sub">{{ __('lifecycle.checklist.fields.visits_report') }}</div>
        <div class="box">{{ $text('c_VisitsReport') }}</div>
    </section>
    <section>
        <div class="sec">{{ $p('experience') }}</div>
        <div class="box"><b>{{ __('lifecycle.checklist.fields.c_NeedProTest') }}:</b> {{ $flag('c_NeedProTest') }}</div>
        <div class="sub">{{ $p('pro_test') }}</div>
        <table class="grid"><thead><tr><th>{{ $p('test_date') }}</th><th>{{ __('lifecycle.checklist.fields.c_HasProTest') }}</th><th>{{ $p('notes') }}</th><th>{{ $p('result') }}</th></tr></thead><tbody>
        <tr><td>{{ $text('c_ProTest_Date') }}</td><td>{{ $flag('c_HasProTest') }}</td><td>{{ $text('c_ProTest_Note') }}</td><td>{{ $text('c_ProTest_Result') }}</td></tr></tbody></table>
        <div class="sub">{{ $p('pro_train') }}</div>
        <table class="grid"><thead><tr><th>{{ $p('trainer') }}</th><th>{{ $p('place') }}</th><th>{{ $p('period') }}</th><th>{{ $p('result') }}</th></tr></thead><tbody>
        <tr><td>{{ $text('c_ProTrain_Trainer') }}</td><td>{{ $text('c_ProTrain_Place') }}</td><td>{{ $text('c_ProTrain_BDate') }}<br>{{ $text('c_ProTrain_EDate') }}</td><td>{{ $text('c_ProTrain_Result') }}</td></tr></tbody></table>
        <div class="sub">{{ $p('mng_train') }}</div>
        <table class="grid"><thead><tr><th>{{ $p('trainer') }}</th><th>{{ $p('place') }}</th><th>{{ $p('date') }}</th><th>{{ $p('result') }}</th></tr></thead><tbody>
        <tr><td>{{ $text('c_MngTrain_Trainer') }}</td><td>{{ $text('c_MngTrain_Place') }}</td><td>{{ $text('c_MngTrain_Date') }}</td><td>{{ $text('c_MngTrain_Result') }}</td></tr></tbody></table>
    </section>
    @foreach(['t' => ['team_opinion', 'c_TeamDate'], 'c' => ['committee_opinion', 'c_CommityDate']] as $location => [$title, $dateField])
        @php $group = $members->where('c_Location', $location)->values(); @endphp
        <section>
            <div class="sec">{{ $p($title) }}</div>
            <div class="box"><b>{{ $p('meeting_date') }}:</b> {{ $text($dateField) }}</div>
            <table class="grid"><thead><tr><th>{{ $p('name_col') }}</th><th>{{ $p('opinion') }}</th><th>{{ $p('reason') }}</th><th>{{ $p('signature') }}</th></tr></thead><tbody>
            @forelse($group as $row)<tr><td>{{ $label($location, $row) }}</td><td>{{ $opinion($row) }}</td><td>{{ $row->c_Notes ?: '' }}</td><td></td></tr>@empty<tr><td colspan="4">—</td></tr>@endforelse
            </tbody></table>
        </section>
    @endforeach
    <section>
        <div class="sec">{{ $p('notes') }}</div>
        <div class="box">{{ $text('c_Notes') }}</div>
        <div class="box"><b>{{ $p('reject') }}:</b> {{ $flag('c_RejectMsg') }} · {{ $text('c_RejectMsgDate') }}</div>
    </section>
    @endif
    <p class="foot">{{ __('lifecycle.checklist.printed') }} {{ now()->format('Y-m-d H:i') }} (UTC)</p>
        </td></tr></tbody>
    </table>
</main>
</body>
</html>
