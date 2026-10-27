@php
    // Printable application form in the checklist's print style. It prints what is stored, nothing is computed.
    $ar = app()->isLocale('ar');
    $t = fn ($key) => __('applications.print.'.$key);
    $label = function (string $name) {
        $key = str_starts_with($name, 'c_') ? substr($name, 2) : $name;
        $l = __('applications.fields.'.$key);
        if ($l === 'applications.fields.'.$key) $l = __('people.fields.'.$key);
        $l = $l === 'people.fields.'.$key ? str_replace('_', ' ', $key) : $l;
        return trim(preg_replace('/\s*\([^()]*\)\s*$/u', '', $l)); // form hints such as "(0 = no, 1 = yes)" are not printed
    };
    $yesNo = function ($name, $value) {
        if ($name === 'c_P_Sex' && in_array($value, \App\Services\Applications\ApplicationService::SEX_VALUES, true)) {
            return __('people.sex.'.$value);
        }
        return preg_match('/^c_(P_Has|P_IsLiving|MP_Agreement|HasWork|P_IsMilitary)/', $name) && in_array((string) $value, ['0', '1'], true)
            ? ((string) $value === '1' ? __('lifecycle.checklist.yes') : __('lifecycle.checklist.no')) : $value;
    };
    $text = fn ($value) => $value === null || $value === '' ? '—' : (string) $value;
    // [label, value] pairs for one row: its fields in metadata order, then its described references.
    $pairs = function (object $row, array $meta) use ($label, $yesNo, $text) {
        $out = [];
        foreach ($meta as $name => $field) { $out[] = [$label($name), $text($yesNo($name, $row->{$name} ?? null))]; }
        foreach ($row->referenceDescriptions ?? [] as $key => $description) { $out[] = [$label($key), $description]; }
        return $out;
    };
    // Columns for a table of rows: fields and references that carry a value in at least one row.
    $columns = function ($rows, array $meta) use ($label) {
        $cols = [];
        foreach ($meta as $name => $field) {
            if ($rows->contains(fn ($row) => ($row->{$name} ?? null) !== null && $row->{$name} !== '')) $cols[$name] = $label($name);
        }
        foreach ($rows as $row) {
            foreach ($row->referenceDescriptions ?? [] as $key => $description) {
                if ($description !== '—' && ! isset($cols['ref:'.$key])) $cols['ref:'.$key] = $label($key);
            }
        }
        return $cols;
    };
    $cell = fn (object $row, string $column) => str_starts_with($column, 'ref:') ? ($row->referenceDescriptions[substr($column, 4)] ?? '—') : $text($yesNo($column, $row->{$column} ?? null));
    $stage = in_array($application->c_ApplicationState, ['zero', 'project'], true) ? __('applications.states.'.$application->c_ApplicationState) : $application->c_ApplicationState;
    // Project: the identifying fields first, then the rest in metadata order; donor assignment belongs to finance, not the form.
    $first = ['c_MP_Name', 'c_MP_DateOfRequest', 'c_MP_Speciality', 'c_MP_RequestedAmount'];
    $project = clone $application;
    $project->referenceDescriptions = array_diff_key($project->referenceDescriptions ?? [], array_flip(['MP_Donor', 'DonorGroup_ID']));
    $projectPairs = array_merge([[__('applications.fields.MP_ID'), (string) $application->c_MP_ID], [$t('stage'), $stage], [__('lifecycle.checklist.p.city'), $ar ? $application->city_name_ar : $application->city_name_en]],
        $pairs($project, array_replace(array_intersect_key(array_flip($first), $fields['application']), array_intersect_key($fields['application'], array_flip($first)), array_diff_key($fields['application'], array_flip($first)))));
    $personName = fn ($person) => $person === null ? '—' : trim(implode(' ', array_filter([$person->c_P_FirstName ?? null, $person->c_P_FatherName ?? null, $person->c_P_LastName ?? null])));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $ar ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $t('title') }} · {{ $application->c_MP_ID }} · {{ __('ui.brand') }}</title>
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
        table.grid th { font-weight: 500; background: #fafafa; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .info td { width: 50%; }
        b { font-weight: 500; }
        .box { border: 1px solid var(--line); border-top: 0; padding: .35rem .5rem; white-space: pre-wrap; overflow-wrap: anywhere; }
        .sign td { height: 3.2rem; vertical-align: bottom; }
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
    <div class="bar"><button type="button" onclick="window.print()">{{ $t('button') }}</button><a href="{{ route('applications.show', ['locale' => app()->getLocale(), 'application' => $applicationId]) }}">{{ __('applications.back') }}</a></div>
    <table class="frame">
        <thead><tr><td><img class="letterhead" src="{{ asset('assets/brand/checklist-header.png') }}" alt="{{ __('ui.brand') }}"></td></tr></thead>
        <tbody><tr><td>
    <h1>{{ $t('title') }}</h1>

    <section>
        <div class="sec">{{ $t('project') }}</div>
        <table class="grid info"><tbody>
        @foreach(array_chunk($projectPairs, 2) as $chunk)<tr>@foreach($chunk as [$l, $v])<td><b>{{ $l }}:</b> <bdi>{{ $v }}</bdi></td>@endforeach @if(count($chunk) === 1)<td></td>@endif</tr>@endforeach
        </tbody></table>
    </section>

    @forelse($applicants as $applicant)
    @php $isPartner = ($applicant['link']->c_PersonType ?? '') === 'شريك'; @endphp
    <section>
        <div class="sec">{{ $isPartner ? $t('partner') : $t('applicant') }}: {{ $personName($applicant['person']) }}</div>
        @if($applicant['person'] === null)
            <div class="box">{{ __('people.unavailable') }}</div>
        @else
        <table class="grid info"><tbody>
        @foreach(array_chunk($pairs($applicant['person'], $fields['person']), 2) as $chunk)<tr>@foreach($chunk as [$l, $v])<td><b>{{ $l }}:</b> <bdi>{{ $v }}</bdi></td>@endforeach @if(count($chunk) === 1)<td></td>@endif</tr>@endforeach
        </tbody></table>
        @endif

        <div class="sub">{{ $t('family') }}</div>
        @forelse($applicant['households'] as $household)
            <div class="box"><b>{{ $t('family_number') }}:</b> <bdi>{{ $text($household['family']->c_F_Number ?? null) }}</bdi></div>
            @php $memberRows = collect($household['members'])->map(fn ($m) => $m['membership']); $memberCols = array_diff_key($columns($memberRows, array_diff_key($fields['membership'], array_flip(['c_PF_ID']))), array_flip(['ref:Person_ID', 'ref:Family_ID'])); @endphp
            <table class="grid"><thead><tr><th>{{ $t('member') }}</th>@foreach($memberCols as $column => $title)<th>{{ $title }}</th>@endforeach</tr></thead><tbody>
            @foreach($household['members'] as $member)<tr><td>{{ $personName($member['person']) }}</td>@foreach($memberCols as $column => $title)<td>{{ $cell($member['membership'], $column) }}</td>@endforeach</tr>@endforeach
            </tbody></table>
        @empty
            <div class="box">{{ $t('none') }}</div>
        @endforelse

        <div class="sub">{{ $t('story') }}</div>
        <table class="grid info"><tbody>
        @foreach(array_chunk($pairs($applicant['link'], $fields['people']), 2) as $chunk)<tr>@foreach($chunk as [$l, $v])<td><b>{{ $l }}:</b> <bdi>{{ $v }}</bdi></td>@endforeach @if(count($chunk) === 1)<td></td>@endif</tr>@endforeach
        </tbody></table>

        @foreach(['work' => 'work', 'education' => 'education', 'course' => 'courses'] as $kind => $title)
            <div class="sub">{{ $t($title) }}</div>
            @php $rows = $applicant[$kind]; $cols = $columns($rows, $fields[$kind]); @endphp
            @if($rows->isEmpty())<div class="box">{{ $t('none') }}</div>@else
            <table class="grid"><thead><tr>@foreach($cols as $column => $heading)<th>{{ $heading }}</th>@endforeach</tr></thead><tbody>
            @foreach($rows as $row)<tr>@foreach($cols as $column => $heading)<td>{{ $cell($row, $column) }}</td>@endforeach</tr>@endforeach
            </tbody></table>
            @endif
        @endforeach

        <div class="sub">{{ $t('support') }}</div>
        @foreach(['loss', 'illness', 'asset', 'aid', 'help', 'homepower'] as $kind)
            @php $rows = $applicant['details'][$kind]; $cols = $columns($rows, $fields[$kind]); @endphp
            <div class="box"><b>{{ $t($kind) }}:</b> @if($rows->isEmpty()){{ $t('none') }}@endif</div>
            @if($rows->isNotEmpty())
            <table class="grid"><thead><tr>@foreach($cols as $column => $heading)<th>{{ $heading }}</th>@endforeach</tr></thead><tbody>
            @foreach($rows as $row)<tr>@foreach($cols as $column => $heading)<td>{{ $cell($row, $column) }}</td>@endforeach</tr>@endforeach
            </tbody></table>
            @endif
        @endforeach
    </section>
    @empty
    <section><div class="sec">{{ $t('applicant') }}</div><div class="box">{{ $t('none') }}</div></section>
    @endforelse

    @foreach(['material' => 'budget', 'powersource' => 'powersource', 'risk' => 'risk', 'agriculture' => 'agriculture', 'rehab' => 'rehab', 'rehab_answers' => 'rehab'] as $kind => $title)
        @php $rows = $$kind; $cols = $columns($rows, $fields[$kind]); @endphp
        @if($rows->isNotEmpty() || in_array($kind, ['material'], true))
        <section>
            @if($kind !== 'rehab_answers')<div class="sec">{{ $t($title) }}</div>@else<div class="sub">{{ __('applications.rehab_answers') }}</div>@endif
            @if($rows->isEmpty())<div class="box">{{ $t('none') }}</div>@else
            <table class="grid"><thead><tr>@foreach($cols as $column => $heading)<th>{{ $heading }}</th>@endforeach</tr></thead><tbody>
            @foreach($rows as $row)<tr>@foreach($cols as $column => $heading)<td>{{ $cell($row, $column) }}</td>@endforeach</tr>@endforeach
            </tbody></table>
            @endif
        </section>
        @endif
    @endforeach

    <section>
        <div class="sec">{{ $t('signatures') }}</div>
        <table class="grid sign"><thead><tr><th>{{ $t('applicant_signature') }}</th><th>{{ $t('staff_signature') }}</th><th>{{ $t('date') }}</th></tr></thead><tbody><tr><td></td><td></td><td></td></tr></tbody></table>
    </section>
    <p class="foot">{{ $t('printed') }} {{ now()->format('Y-m-d H:i') }} (UTC)</p>
        </td></tr></tbody>
    </table>
</main>
</body>
</html>
