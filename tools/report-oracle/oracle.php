<?php

/*
 * P6.3 report oracle: compares what the web reports and exports show with what the desktop's own SQL gives on the restored source
 * databases, city by city. Read-only on the sources. Run it from a normal terminal against a running web instance whose data came
 * from those sources (the 100-application sample, or the full import in Phase 9):
 *
 *   php tools/report-oracle/oracle.php --web=http://127.0.0.1:8024 --user=sample.tester --source-config=D:/path/connection.json [--out=report.json] [--sample]
 *
 * --sample is for the 100-application sample: it copies catalog rows only through resolved links, so a catalog name that is blank in the web
 * but present in the desktop is listed as a known sample difference. Never use it on the full import, where such a blank is a failure.
 *
 * The web password is read from the ORACLE_WEB_PASSWORD environment variable (never put it on the command line). The source
 * connection file is JSON with host, port, source_username and source_password; the databases are micro_projects_source_<city>.
 * Nothing private is written to the report: only counts, project numbers and the differing values.
 *
 * Checks (each compares only the projects the web instance holds, in both directions where completeness can be judged):
 *   applications  every web row exists in the desktop with the same name, request date and current status; filter matrix counts
 *   statistics    status-by-city and year counts equal the desktop aggregates over the same projects
 *   loans         the 15 loan report figures of Search_Form.Loan_bind per loan (desktop BuildLoanReportQuery)
 *   instalments   count and amount by city, project, currency and paid/unpaid; totals by currency; completeness per project
 *   family        person attributes, family numbers and project links per person (FamilyMember_bind)
 *   details       the twelve detail-table search modes: every row of each, on both sides, as a multiset
 *   other modes   applications, attachments, M&E visits, monitoring visits, check lists, special cases, educations, profession meetings
 *   lookups       21 lookup tables exported whole against the desktop's tables (with --sample: the web's rows must all exist in the desktop)
 *   state history every project's state-history export against the desktop's microproject_state rows
 *   loan filters  the loan report's fund type, type, sub type, category, sub category, career, partnership and funded-amount (USD) filters: the number of loans under each filter against the desktop's own conditions
 *
 * Known, documented differences are listed separately from failures: the desktop rounds floating-point columns, so "Total instalments"
 * may differ by one cent, "Remaining instalments" is rounded to two decimals where the desktop value is unrounded, and the desktop leaves\n * "Paid amount" blank where the web shows 0.
 */

declare(strict_types=1);

$options = getopt('', ['web:', 'user:', 'source-config:', 'out::', 'sample']);
$sample = array_key_exists('sample', $options);
foreach (['web', 'user', 'source-config'] as $required) {
    if (empty($options[$required])) {
        fwrite(STDERR, "missing --$required\n");
        exit(2);
    }
}
$password = getenv('ORACLE_WEB_PASSWORD') ?: '';
if ($password === '') {
    fwrite(STDERR, "set ORACLE_WEB_PASSWORD\n");
    exit(2);
}
$web = rtrim($options['web'], '/');
$config = json_decode((string) file_get_contents($options['source-config']), true, flags: JSON_THROW_ON_ERROR);

// ---- Web side: log in and download the exports as CSV -------------------------------------------------
$jar = tempnam(sys_get_temp_dir(), 'oracle');
$http = function (string $url, array $post = []) use ($jar): string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 600]);
    if ($post !== []) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string) curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if ($status >= 400) {
        throw new RuntimeException("HTTP $status for $url");
    }

    return $body;
};
$login = $http($web.'/en/login');
preg_match('/name="_token" value="([^"]+)"/', $login, $token);
$http($web.'/en/login', ['_token' => $token[1] ?? '', 'username' => $options['user'], 'password' => $password]);

/** @return list<array<string, string>> */
$csv = function (string $path) use ($http, $web): array {
    $body = $http($web.$path.(str_contains($path, '?') ? '&' : '?').'format=csv');
    $handle = fopen('php://temp', 'w+');
    fwrite($handle, preg_replace('/^\xEF\xBB\xBF/', '', $body));
    rewind($handle);
    $head = fgetcsv($handle, 0, ',', '"', '');
    $rows = [];
    while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
        $rows[] = array_combine($head, $row);
    }
    fclose($handle);

    return $rows;
};

$applications = $csv('/en/applications/export');
$cities = array_values(array_unique(array_column($applications, 'City')));
$report = ['web' => $web, 'cities' => $cities, 'checks' => []];
$failures = 0;
$note = function (string $check, string $item, string $detail) use (&$report, &$failures): void {
    $report['checks'][$check]['mismatches'][$item][] = $detail;
    $failures++;
};
$count = function (string $check, string $key, int|float|string $value) use (&$report): void {
    $report['checks'][$check]['counts'][$key] = $value;
};

// ---- Desktop side ------------------------------------------------------------------------------------
$sources = [];
foreach ($cities as $city) {
    $sources[$city] = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname=micro_projects_source_'.strtolower($city).';charset=utf8mb4', $config['source_username'], $config['source_password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sources[$city]->exec("SET SESSION sql_mode=''");
}
$webProjects = [];
foreach ($applications as $row) {
    $webProjects[$row['City']][(int) $row['Project number']] = $row;
}
$inList = fn (array $numbers) => implode(',', array_map('intval', $numbers ?: [0]));
$day = fn ($value) => $value === null || $value === '' ? '' : substr((string) $value, 0, 10);

// 1. Applications --------------------------------------------------------------------------------------
$desktopApps = [];
foreach ($cities as $city) {
    $sql = 'select MP.MP_ID pn, MP.MP_Name name, MP.MP_DateOfRequest req, state.Name_en status from microproject MP
        left join microproject_state mps on mps.MicroProject_ID = MP.MP_ID and mps.Current_State = 1 left join state on state.ID = mps.State_ID
        where MP.MP_ID in ('.$inList(array_keys($webProjects[$city])).')';
    foreach ($sources[$city]->query($sql, PDO::FETCH_ASSOC) as $d) {
        $desktopApps[$city][(int) $d['pn']] = $d;
    }
}
$checked = 0;
foreach ($webProjects as $city => $rows) {
    foreach ($rows as $number => $w) {
        $d = $desktopApps[$city][$number] ?? null;
        $checked++;
        if ($d === null) {
            $note('applications', 'missing in desktop', "$city|$number");

            continue;
        }
        foreach ([['name', trim((string) $d['name']), trim($w['Name'])], ['request date', $day($d['req']), $w['Request date']], ['status', (string) $d['status'], $w['Lifecycle status']]] as [$field, $expected, $actual]) {
            if ($expected !== $actual) {
                $note('applications', $field, "$city|$number desktop=$expected web=$actual");
            }
        }
    }
}
$count('applications', 'web rows checked', $checked);
// Filter matrix: the web export under a filter must equal the desktop rows satisfying the same predicate.
$flat = [];
foreach ($webProjects as $city => $rows) {
    foreach ($rows as $number => $w) {
        $flat[] = ['city' => $city] + $desktopApps[$city][$number] + ['number' => $number];
    }
}
$cityIds = [];
$perDay = array_count_values(array_filter(array_map(fn ($r) => $day($r['req']), $flat)));
arsort($perDay);
$busiest = (string) (array_key_first($perDay) ?? '2025-01-01');
$matrix = [
    'state=Financed' => ['/en/applications/export?state=Financed', fn ($r) => $r['status'] === 'Financed'],
    'date 2025' => ['/en/applications/export?date_from=2025-01-01&date_to=2025-12-31', fn ($r) => $day($r['req']) >= '2025-01-01' && $day($r['req']) <= '2025-12-31'],
    'date from 2026-01-01' => ['/en/applications/export?date_from=2026-01-01', fn ($r) => $day($r['req']) >= '2026-01-01'],
    'boundary: a day that has records (inclusive both ends)' => ['/en/applications/export?date_from='.$busiest.'&date_to='.$busiest, fn ($r) => $day($r['req']) === $busiest],
    'boundary: the day before it is excluded' => ['/en/applications/export?date_from=2000-01-01&date_to='.date('Y-m-d', strtotime($busiest.' -1 day')), fn ($r) => $day($r['req']) <= date('Y-m-d', strtotime($busiest.' -1 day')) && $day($r['req']) >= '2000-01-01'],
    'boundary: year end and year start' => ['/en/applications/export?date_from=2025-12-31&date_to=2026-01-01', fn ($r) => $day($r['req']) >= '2025-12-31' && $day($r['req']) <= '2026-01-01'],
    'state and 2026' => ['/en/applications/export?state=Financed&date_from=2026-01-01', fn ($r) => $r['status'] === 'Financed' && $day($r['req']) >= '2026-01-01'],
    'empty (future dates)' => ['/en/applications/export?date_from=2099-01-01', fn ($r) => false],
];
foreach ($cities as $city) {
    $matrix['city '.$city] = ['/en/applications/export?cities[]='.urlencode((string) ($cityIds[$city] ??= cityId($csv, $city, $http, $web))), fn ($r) => $r['city'] === $city];
}
foreach ($matrix as $name => [$path, $predicate]) {
    $actual = count($csv($path));
    $expected = count(array_filter($flat, $predicate));
    $count('applications', "filter: $name", "web $actual / desktop $expected");
    if ($actual !== $expected) {
        $note('applications', 'filter count', "$name web=$actual desktop=$expected");
    }
}

// 2. Statistics ------------------------------------------------------------------------------------------
$stats = $csv('/en/statistics/export');
$webCells = [];
foreach ($stats as $row) {
    if (in_array($row['Section'], ['Lifecycle status', 'Request year'], true)) {
        $webCells[$row['Section'].'|'.$row['Category'].'|'.$row['City']] = (int) $row['Records'];
    }
}
$desktopCells = [];
foreach ($flat as $r) {
    $status = $r['status'] === null || $r['status'] === '' ? 'No status recorded' : $r['status'];
    $desktopCells["Lifecycle status|$status|{$r['city']}"] = ($desktopCells["Lifecycle status|$status|{$r['city']}"] ?? 0) + 1;
    $year = $day($r['req']) === '' ? 'No request date' : substr($day($r['req']), 0, 4);
    $desktopCells["Request year|$year|"] = ($desktopCells["Request year|$year|"] ?? 0) + 1;
}
foreach ($webCells as $key => $value) {
    if ($value > 0 || isset($desktopCells[$key])) {
        if (($desktopCells[$key] ?? 0) !== $value) {
            $note('statistics', 'cell', "$key web=$value desktop=".($desktopCells[$key] ?? 0));
        }
    }
}
foreach ($desktopCells as $key => $value) {
    if (! isset($webCells[$key]) || $webCells[$key] !== $value) {
        $note('statistics', 'cell', "$key web=".($webCells[$key] ?? 'absent')." desktop=$value");
    }
}
$count('statistics', 'cells compared', count($desktopCells));

// 3. Loans -----------------------------------------------------------------------------------------------
$webLoans = [];
foreach ($csv('/en/search-reports/export?mode=loans') as $row) {
    $webLoans[$row['City'].'|'.$row['Project number']] = $row;
}
$loanSql = <<<'SQL'
select L.MicroProject_ID as pn,
 Round(CASE WHEN Currency = 'SP' THEN Loan_Amount WHEN Currency = 'USD' THEN Loan_Amount * ExchangeRate END,2) AS sp,
 Round(CASE WHEN Currency = 'SP' THEN Loan_Amount / ExchangeRate WHEN Currency = 'USD' THEN Loan_Amount END,2) AS usd,
 Round((L.Loan_ReturnedAmount * 100)/L.Loan_Amount, 2) as rate, L.Loan_ReturnedAmount as ret, L.Loan_PaymentsAmount as inst,
 (select sum(`Pay_Amount`) from `payment` where `Loan_ID` = L.Loan_ID and `Pay_IsPaid` like N'Paid') as paid,
 IF((L.Loan_ReturnedAmount - (select sum(`Pay_Amount`) from `payment` where `Pay_IsPaid` like N'Paid' and `Loan_ID` = L.Loan_ID))<0,0,(L.Loan_ReturnedAmount - (select sum(`Pay_Amount`) from `payment` where `Pay_IsPaid` like N'Paid' and `Loan_ID` = L.Loan_ID))) as remaining,
 Round( (L.Loan_ReturnedAmount / L.Loan_PaymentsAmount),2) as total_count,
 (select count(Pay_Amount) from `payment` where `Loan_ID` = L.Loan_ID and `Pay_IsPaid` like N'Paid') as paid_count,
 ((IF(((L.Loan_Amount * L.Loan_Rate / 100) - (select sum(`Pay_Amount`) from `payment` where `Pay_IsPaid` like N'Paid' and `Loan_ID` = L.Loan_ID))<0,0,((L.Loan_Amount * L.Loan_Rate / 100) - (select sum(`Pay_Amount`) from `payment` where `Pay_IsPaid` like N'Paid' and `Loan_ID` = L.Loan_ID))))/ L.Loan_PaymentsAmount) as remaining_count,
 (SELECT `Pay_DueDate` FROM `payment` WHERE `Loan_ID` = L.Loan_ID ORDER BY `payment`.`Pay_DueDate` ASC LIMIT 1) as first_due,
 (SELECT `Pay_RecievedOnDate` FROM `payment` WHERE `Loan_ID` = L.Loan_ID and `Pay_IsPaid` like 'Paid' ORDER BY `payment`.`Pay_RecievedOnDate` DESC LIMIT 1) as last_paid,
 (SELECT `Pay_DueDate` FROM `payment` WHERE `Loan_ID` = L.Loan_ID and `Pay_IsPaid` like 'Not Paid' AND state.Name_ar like 'ممول' ORDER BY `payment`.`Pay_DueDate` DESC LIMIT 1) as due_date,
 IF(IF((L.Loan_ReturnedAmount - (select sum(`Pay_Amount`) from `payment` where `Pay_IsPaid` like N'Paid' and `Loan_ID` = L.Loan_ID)) < 0,0,(L.Loan_ReturnedAmount - (select sum(`Pay_Amount`) from `payment` where `Pay_IsPaid` like N'Paid' and `Loan_ID` = L.Loan_ID))) = 0,0,
   (SELECT TIMESTAMPDIFF(MONTH, payment.Pay_DueDate , DATE_ADD( Now(),INTERVAL 1 MONTH)) from payment where payment.Loan_ID = L.Loan_ID and payment.Pay_IsPaid = 'Not Paid' and state.ID != 7 ORDER by Pay_DueDate desc LIMIT 1 )) as months_late,
 (select count(Pay_Amount) from `payment` where `Loan_ID` = L.Loan_ID and `Pay_IsPaid` like N'Not Paid') as unpaid_count
 from `loan` L LEFT OUTER join `microproject` MP on L.MicroProject_ID = MP.MP_ID
 LEFT OUTER JOIN `microproject_state` mps on mps.MicroProject_ID = MP.MP_ID LEFT OUTER JOIN `state` on state.ID = mps.State_ID
 where Current_State = 1 AND L.MicroProject_ID in (%s) Group By MP_ID
SQL;
$loanColumns = ['Loan (SP)' => 'sp', 'Loan (USD)' => 'usd', 'Return rate %' => 'rate', 'Amount to return' => 'ret', 'Instalment' => 'inst', 'Paid amount' => 'paid', 'Remaining amount' => 'remaining',
    'Total instalments' => 'total_count', 'Paid instalments' => 'paid_count', 'Remaining instalments' => 'remaining_count', 'First instalment date' => 'first_due', 'Last paid date' => 'last_paid',
    'Due instalment date' => 'due_date', 'Months late' => 'months_late', 'Unpaid instalments' => 'unpaid_count'];
$known = [];
$desktopLoanKeys = [];
foreach ($cities as $city) {
    foreach ($sources[$city]->query(sprintf($loanSql, $inList(array_keys($webProjects[$city]))), PDO::FETCH_ASSOC) as $d) {
        $key = $city.'|'.$d['pn'];
        $desktopLoanKeys[$key] = true;
        $w = $webLoans[$key] ?? null;
        if ($w === null) {
            $note('loans', 'missing in web', $key);

            continue;
        }
        foreach ($loanColumns as $column => $field) {
            $date = str_ends_with($column, 'date');
            $e = $d[$field] === null ? '' : ($date ? $day($d[$field]) : (string) $d[$field]);
            $a = $w[$column];
            if ($e === $a || (is_numeric($e) && is_numeric($a) && abs((float) $e - (float) $a) < 0.0001)) {
                continue;
            }
            if ($column === 'Total instalments' && is_numeric($e) && is_numeric($a) && abs((float) $e - (float) $a) <= 0.0101) {
                $known['Total instalments one-cent rounding (float column)'][] = "$key desktop=$e web=$a";
            } elseif ($column === 'Remaining instalments' && is_numeric($e) && is_numeric($a) && abs((float) $e - (float) $a) <= 0.0051) {
                $known['Remaining instalments rounded to 2 decimals (desktop value is unrounded)'][] = "$key desktop=$e web=$a";
            } elseif ($e === '' && $column === 'Paid amount' && (float) $a === 0.0) {
                $known['Paid amount blank in desktop, 0 in web'][] = $key;
            } else {
                $note('loans', $column, "$key desktop=$e web=$a");
            }
        }
    }
}
foreach (array_keys($webLoans) as $key) {
    if (! isset($desktopLoanKeys[$key])) {
        $note('loans', 'missing in desktop', $key);
    }
}
$count('loans', 'loans compared', count($desktopLoanKeys));

// 4. Instalments -----------------------------------------------------------------------------------------
$webPay = [];
$webTotals = [];
foreach (['pending', 'paid'] as $state) {
    foreach ($csv('/en/payments/export?state='.$state) as $row) {
        $key = $row['City'].'|'.$row['Project number'].'|'.$row['Currency'].'|'.$row['Paid'];
        $webPay[$key]['count'] = ($webPay[$key]['count'] ?? 0) + 1;
        $webPay[$key]['sum'] = bcadd($webPay[$key]['sum'] ?? '0', $row['Amount'], 2);
        $totalKey = $row['City'].'|'.$row['Currency'].'|'.$row['Paid'];
        $webTotals[$totalKey]['count'] = ($webTotals[$totalKey]['count'] ?? 0) + 1;
        $webTotals[$totalKey]['sum'] = bcadd($webTotals[$totalKey]['sum'] ?? '0', $row['Amount'], 2);
    }
}
$deskPay = [];
$deskTotals = [];
foreach ($cities as $city) {
    $sql = 'select L.MicroProject_ID pn, L.Currency cur, P.Pay_IsPaid paid, count(*) n, sum(P.Pay_Amount) s from payment P join loan L on L.Loan_ID = P.Loan_ID
        where L.MicroProject_ID in ('.$inList(array_keys($webProjects[$city])).') group by L.MicroProject_ID, L.Currency, P.Pay_IsPaid';
    foreach ($sources[$city]->query($sql, PDO::FETCH_ASSOC) as $d) {
        $label = $d['paid'] === 'Paid' ? 'Paid' : 'Not paid';
        $key = $city.'|'.$d['pn'].'|'.$d['cur'].'|'.$label;
        $deskPay[$key] = ['count' => (int) $d['n'], 'sum' => number_format((float) $d['s'], 2, '.', '')];
        $totalKey = $city.'|'.$d['cur'].'|'.$label;
        $deskTotals[$totalKey]['count'] = ($deskTotals[$totalKey]['count'] ?? 0) + (int) $d['n'];
        $deskTotals[$totalKey]['sum'] = bcadd($deskTotals[$totalKey]['sum'] ?? '0', number_format((float) $d['s'], 2, '.', ''), 2);
    }
}
foreach ($deskPay + $webPay as $key => $unused) {
    $d = $deskPay[$key] ?? null;
    $w = $webPay[$key] ?? null;
    if ($d === null || $w === null || $d['count'] !== $w['count'] || bccomp($d['sum'], $w['sum'], 2) !== 0) {
        $note('instalments', 'per project, currency and state', $key.' desktop='.json_encode($d).' web='.json_encode($w));
    }
}
foreach ($deskTotals + $webTotals as $key => $unused) {
    $d = $deskTotals[$key] ?? null;
    $w = $webTotals[$key] ?? null;
    $count('instalments', "total $key", ($w['count'] ?? 0).' instalments, '.($w['sum'] ?? '0'));
    if ($d === null || $w === null || $d['count'] !== $w['count'] || bccomp($d['sum'], $w['sum'], 2) !== 0) {
        $note('instalments', 'total by city, currency and state', $key.' desktop='.json_encode($d).' web='.json_encode($w));
    }
}

// 5. Family ----------------------------------------------------------------------------------------------
$webFamily = [];
foreach ($csv('/en/search-reports/export?mode=family') as $row) {
    $webFamily[$row['City']][$row['Person number']][] = $row;
}
$familySql = "select p.P_ID pid, f.F_Number book, CONCAT(P_FirstName, ' ', P_LastName) person, P_FatherName father, P_MotherName mother, p.P_NationalNumber nat, p.P_Sex sex, DATE(p.P_DOB) dob,
 case pf.HasWork when 1 then 'Yes' when 0 then 'No' else '' end works, pf.Work_Name work, PMP.MicroProject_ID project, pf.Family_ID family
 From person p left join person_family pf on p.P_ID = pf.Person_ID left join family f on f.F_ID = pf.Family_ID left join person_microproject PMP on PMP.Person_ID = p.P_ID";
$map = ['book' => 'Family book number', 'person' => 'Person', 'father' => 'Father', 'mother' => 'Mother', 'nat' => 'National number', 'sex' => 'Sex', 'dob' => 'Date of birth', 'works' => 'Works', 'work' => 'Work'];
$persons = 0;
foreach ($cities as $city) {
    $desktop = [];
    foreach ($sources[$city]->query($familySql, PDO::FETCH_ASSOC) as $d) {
        if (isset($webFamily[$city][$d['pid']])) {
            $desktop[$d['pid']][] = $d;
        }
    }
    foreach ($webFamily[$city] ?? [] as $pid => $rows) {
        $persons++;
        $dRows = $desktop[$pid] ?? [];
        $a = array_values(array_unique(array_filter(array_column($rows, 'Family number'))));
        $b = array_values(array_unique(array_filter(array_map('strval', array_column($dRows, 'family')))));
        sort($a);
        sort($b);
        if ($a !== $b) {
            $note('family', 'family numbers', "$city|$pid web=".implode(',', $a).' desktop='.implode(',', $b));
        }
        $webProjectsOfPerson = array_values(array_unique(array_filter(array_column($rows, 'Project number'))));
        if (array_diff($webProjectsOfPerson, array_map('strval', array_column($dRows, 'project'))) !== []) {
            $note('family', 'project link not in desktop', "$city|$pid");
        }
        foreach ($rows as $w) {
            $match = null;
            foreach ($dRows as $d) {
                if ((string) $d['family'] === $w['Family number'] && (string) $d['project'] === $w['Project number']) {
                    $match = $d;
                    break;
                }
            }
            if ($match === null) {
                $note('family', 'no matching desktop row', "$city|$pid");

                continue;
            }
            foreach ($map as $field => $column) {
                if (trim((string) $match[$field]) !== trim($w[$column])) {
                    $note('family', $column, "$city|$pid desktop=".trim((string) $match[$field]).' web='.trim($w[$column]));
                }
            }
        }
    }
}
$count('family', 'persons compared', $persons);

// 6. Detail search modes (bind_person_tables / bind_project_tables) ---------------------------------------
// Each mode: kind, desktop table, the desktop's select fragment (columns c1..cN, in the web's column order), extra where, date columns.
$subName = fn (string $t, string $fk, string $name = 'Name') => "(SELECT $name FROM `$t` WHERE `$t`.ID = $fk LIMIT 1)";
$detailModes = [
    'aid-sources' => ['person', 'person_aidsource', $subName('aidsource', 'mytable.AidSource_ID').' c1, mytable.DateYear c2, mytable.Amount c3, mytable.ProjectType c4, mytable.Notes c5', '', []],
    'assets' => ['person', 'person_asset', $subName('assettype', 'mytable.Asset_Type_ID').' c1, mytable.`Year` c2, '.$subName('asset', 'mytable.Asset_ID').' c3, mytable.Description c4, '.$subName('assetbenefits', 'mytable.Asset_Benefits_ID').' c5, mytable.Note c6', '', []],
    'courses' => ['person', 'person_course', $subName('subcategory', 'mytable.SubCategory_ID').' c1, mytable.Name c2, mytable.Place c3, mytable.Donor c4, mytable.`Date` c5', '', [4]],
    'goals' => ['person', 'person_goals', $subName('goal', 'mytable.Goal_ID').' c1', '', []],
    'relief-aids' => ['person', 'person_help', $subName('aidsource', 'mytable.AidSource_ID').' c1, '.$subName('aid', 'mytable.Aid_ID').' c2, '.$subName('aidperiod', 'mytable.AidPeriod_ID').' c3, mytable.Notes c4', '', []],
    'illness' => ['person', 'person_illness', $subName('illness', 'mytable.Illness_ID').' c1, mytable.Note c2, mytable.Has_MedicalAid c3, '.$subName('aidsource', 'mytable.AidSource_ID').' c4, '.$subName('aidperiod', 'mytable.AidPeriod_ID').' c5, '.$subName('medicalneeds', 'mytable.MedicalNeeds_ID').' c6', '', []],
    'losses' => ['person', 'person_loss', $subName('loss', 'mytable.Loss_ID').' c1, mytable.`Year` c2, mytable.Note c3', '', []],
    'home-power' => ['person', 'person_powersource', $subName('powersource', 'mytable.PowerSource_ID').' c1, mytable.Note c2, mytable.Cost c3, mytable.Responsability c4', '', []],
    'current-works' => ['person', 'person_workexp', '(SELECT W_Name FROM `work` WHERE `work`.W_ID = mytable.Work_ID LIMIT 1) c1, '.$subName('worktype', 'mytable.W_WorkType_ID').' c2, mytable.W_Description c3, mytable.W_BeginDate c4, mytable.W_PlaceState c5, mytable.W_Place c6, mytable.W_Contact c7, mytable.W_Salary c8', " and lower(mytable.W_Status) like 'yes'", [3]],
    'work-experiences' => ['person', 'person_workexp', '(SELECT W_Name FROM `work` WHERE `work`.W_ID = mytable.Work_ID LIMIT 1) c1, '.$subName('worktype', 'mytable.W_WorkType_ID').' c2, mytable.W_Description c3, mytable.W_BeginDate c4, mytable.W_EndDate c5, mytable.W_PlaceState c6, mytable.W_Place c7, mytable.W_Contact c8, mytable.W_CauseOfLoseDescription c9', " and lower(mytable.W_Status) like 'no'", [3, 4]],
    'materials' => ['project', 'material', 'mytable.Name c1, mytable.Comments c2, mytable.Amount c3, mytable.Price c4', '', []],
    'project-power' => ['project', 'microproject_powersource', $subName('powersource', 'mytable.PowerSource_ID').' c1, mytable.Note c2, mytable.Cost c3, mytable.Responsability c4', '', []],
];
$normalize = function ($value, bool $date = false) use ($day, &$known): string {
    if ($value === null) {
        return '';
    }
    $text = $date ? $day($value) : (string) $value;
    // The CSV export prefixes an apostrophe to text that starts like a formula (=, +, -, @, or a tab or carriage return) so a spreadsheet cannot run it; undo that for the comparison.
    if (preg_match("/\A'[=+\-@\t\r]/", $text) === 1) {
        $text = substr($text, 1);
        $known['CSV formula guard: apostrophe added to text starting with = + - @ tab or CR (workbook exports are unaffected)'][] = 'cell';
    }
    $text = trim($text);
    if ($date && str_starts_with($text, '0000')) {
        return '';
    }

    return is_numeric($text) ? (string) (float) $text : preg_replace('/\s+/', ' ', $text);
};
/** Compares two multisets of normalized lines; with --sample a blank web catalog cell where the desktop has text is a known difference. */
$compareLines = function (string $check, string $mode, array $webLines, array $desktopLines) use ($count, $note, &$known, $sample): void {
    sort($webLines);
    sort($desktopLines);
    $count($check, $mode, 'web '.count($webLines).' / desktop '.count($desktopLines));
    if ($webLines !== $desktopLines) {
        $onlyWeb = array_values(array_diff($webLines, $desktopLines));
        $onlyDesktop = array_values(array_diff($desktopLines, $webLines));
        if ($sample) {
            foreach ($onlyWeb as $wi => $webLine) {
                $cells = explode('|', $webLine);
                foreach ($onlyDesktop as $di => $desktopLine) {
                    $dCells = explode('|', $desktopLine);
                    if (count($cells) !== count($dCells)) {
                        continue;
                    }
                    $onlyBlanks = true;
                    foreach ($cells as $ci => $cell) {
                        if ($cell !== $dCells[$ci] && $cell !== '') {
                            $onlyBlanks = false;
                            break;
                        }
                    }
                    if ($onlyBlanks) {
                        $known['Catalog name blank in the sample (the sample copies catalog rows only through resolved links); '.$mode][] = $webLine;
                        unset($onlyWeb[$wi], $onlyDesktop[$di]);
                        // Remove exactly one occurrence from each side: the same line can appear several times.
                        unset($webLines[array_search($webLine, $webLines, true)], $desktopLines[array_search($desktopLine, $desktopLines, true)]);
                        break;
                    }
                }
            }
            $onlyWeb = array_values($onlyWeb);
            $onlyDesktop = array_values($onlyDesktop);
            $webLines = array_values($webLines);
            $desktopLines = array_values($desktopLines);
        }
        if (count($webLines) !== count($desktopLines)) {
            $note($check, "$mode row count", 'web='.count($webLines).' desktop='.count($desktopLines));
        }
        foreach (array_slice($onlyWeb, 0, 2) as $x) {
            $note($check, "$mode only in web", $x);
        }
        foreach (array_slice($onlyDesktop, 0, 2) as $x) {
            $note($check, "$mode only in desktop", $x);
        }
        if ($onlyWeb === [] && $onlyDesktop === [] && $webLines !== $desktopLines) {
            $note($check, "$mode duplicates differ", 'same rows, different multiplicity');
        }
    }
};

foreach ($detailModes as $mode => [$kind, $table, $select, $extra, $dateColumns]) {
    $webRows = $csv('/en/search-reports/export?mode='.$mode);
    $webLines = [];
    foreach ($webRows as $row) {
        $values = array_values($row);
        $head = $kind === 'person' ? [$values[1], $values[2], $values[3]] : [$values[1], $values[2], $values[3]];
        $details = array_slice($values, 4);
        $line = [$row['City'], $head[0], $normalize($head[1]), $kind === 'person' ? $normalize($head[2]) : $normalize($head[2])];
        foreach ($details as $i => $cell) {
            $line[] = $normalize($cell, in_array($i, $dateColumns, true));
        }
        $webLines[] = implode('|', array_map(fn ($v) => is_numeric($v) ? (string) (float) $v : $v, $line));
    }
    $desktopLines = [];
    foreach ($cities as $city) {
        $numbers = $inList(array_keys($webProjects[$city]));
        if ($kind === 'person') {
            $sql = "select PMP.MicroProject_ID pn, CONCAT(P_FirstName, ' ', P_LastName) who, (SELECT Name FROM relation WHERE relation.ID = person_family.Relation_ID LIMIT 1) rel, $select
                FROM $table mytable left JOIN person P on mytable.Person_ID = P.P_ID LEFT JOIN person_family on person_family.Person_ID = mytable.Person_ID
                LEFT JOIN person_microproject PMP on person_family.P_Provider_ID = PMP.Person_ID left JOIN microproject MP on MP.MP_ID = PMP.MicroProject_ID
                LEFT JOIN microproject_state mps on mps.MicroProject_ID = MP.MP_ID LEFT JOIN state on state.ID = mps.State_ID
                where Current_State = 1 $extra and PMP.MicroProject_ID in ($numbers)";
        } else {
            $sql = "select MP.MP_ID pn, CONCAT(P_FirstName, ' ', P_LastName) who, P.P_FatherName rel, $select
                FROM $table mytable left JOIN microproject MP on mytable.MicroProject_ID = MP.MP_ID LEFT JOIN person_microproject PMP on mytable.MicroProject_ID = PMP.MicroProject_ID
                left JOIN person P on PMP.Person_ID = P.P_ID LEFT JOIN microproject_state mps on mps.MicroProject_ID = MP.MP_ID LEFT JOIN state on state.ID = mps.State_ID
                where Current_State = 1 $extra and MP.MP_ID in ($numbers)";
        }
        foreach ($sources[$city]->query($sql, PDO::FETCH_ASSOC) as $d) {
            $line = [$city, (string) (float) $d['pn'], $normalize($d['who']), $normalize($d['rel'])];
            for ($i = 1; $i <= 12; $i++) {
                if (! array_key_exists('c'.$i, $d)) {
                    break;
                }
                $line[] = $normalize($d['c'.$i], in_array($i - 1, $dateColumns, true));
            }
            $desktopLines[] = implode('|', array_map(fn ($v) => is_numeric($v) ? (string) (float) $v : $v, $line));
        }
    }
    $compareLines('details', $mode, $webLines, $desktopLines);
}

// 7. The other eight search modes: applications, attachments, M&E visits, monitoring visits, check lists, special cases, educations, meetings ---
$condition = ' where Current_State = 1 ';
$projectJoins = ' LEFT JOIN category C ON MP.Category_ID = C.ID LEFT JOIN subcategory sub ON MP.SubCategory_ID = sub.ID LEFT JOIN career ON MP.Career_ID = career.ID
    LEFT JOIN microproject_state mps ON mps.MicroProject_ID = MP.MP_ID LEFT JOIN state ON state.ID = mps.State_ID';
$full = "CONCAT(P_FirstName, ' ', P_LastName, ' ابن/ة ', P_FatherName)";
$name = "CONCAT(P_FirstName, ' ', P_LastName)";
$flag = fn (string $col, string $a, string $b) => "(CASE $col WHEN 1 THEN '$a' WHEN 0 THEN '$b' ELSE '' END)";
$visitor = fn (string $col) => "IFNULL((SELECT cm.Name FROM checklist_member cm WHERE cm.ID = checklist.$col LIMIT 1),'')";
$members = fn (string $loc) => "(SELECT COUNT(ID) FROM person_checklistmembers WHERE person_checklistmembers.Person_ID = PMP.Person_ID AND Location LIKE '$loc')";
$other = [
    'applications' => [
        ['Project number', 'Beneficiary', 'Father', 'Mother', 'Career', 'Project description', 'Specialty', 'Category', 'Sub category', 'Project type', 'Project sub type', 'Fund type', 'Request date', 'Project status', 'Status date', 'Status count',
            'Loan date', 'Loan amount', 'Donor', 'Group', 'Project replaced', 'Date of birth', 'Parish', 'National number', 'Mobile', 'Sex', 'Marital status', 'Applicant role', 'Application type', 'Waiting months', 'Vehicle plate', 'Family members', 'Members at home'],
        ['Request date', 'Status date', 'Loan date', 'Date of birth'],
        fn (string $in) => "select MP_ID, $name, P_FatherName, P_MotherName, career.Name, MP_Name, MP_Speciality, C.Name, sub.Name, microprojecttype.Name, microprojectsubtype.Name, fundtype.Name, MP_DateOfRequest, state.Name_en, mps.Date,
            (select count(*) from microproject_state where microproject_state.MicroProject_ID = MP_ID), Loan_DateTaken, IFNULL(Loan_Amount,0), IFNULL(donor.Name,''), IFNULL(donorgroup.Name,''), (CASE IsContentUpdated WHEN 0 THEN 'No' ELSE 'Yes' END),
            IFNULL(P_DOB,'1900'), parish.Name_en, P_NationalNumber, P_Mobile, P_Sex, maritalstatus.Name, PMP.PersonType, (CASE microproject.ApplicationState WHEN 'zero' THEN 'Preliminary' WHEN 'project' THEN 'Full' ELSE microproject.ApplicationState END),
            TIMESTAMPDIFF(MONTH, MP_DateOfRequest, Loan_DateTaken), IFNULL((select visit.VehicleNumber from visit where visit.Kind like 'V-op' and visit.MicroProject_ID = MP_ID limit 1),''), IFNULL(P_NumOfMembers,0), IFNULL(P_NumAtHome,0)
            From microproject LEFT JOIN person_microproject PMP on microproject.MP_ID = PMP.MicroProject_ID LEFT JOIN person on person.P_ID = PMP.Person_ID LEFT JOIN maritalstatus on person.P_MaritalStatus_ID = maritalstatus.ID
            LEFT JOIN parish on person.P_Parish_ID = parish.ID LEFT JOIN category C on microproject.Category_ID = C.ID LEFT JOIN subcategory sub on microproject.SubCategory_ID = sub.ID LEFT JOIN career on microproject.Career_ID = career.ID
            LEFT JOIN microproject_state mps on mps.MicroProject_ID = microproject.MP_ID LEFT JOIN state on state.ID = mps.State_ID LEFT JOIN loan on loan.MicroProject_ID = microproject.MP_ID LEFT JOIN donor on donor.ID = microproject.MP_Donor
            LEFT JOIN donorgroup on donorgroup.ID = microproject.DonorGroup_ID LEFT JOIN fundtype on microproject.MP_FundType_ID = fundtype.ID LEFT JOIN microprojecttype on microproject.MP_Type_ID = microprojecttype.ID
            LEFT JOIN microprojectsubtype on microproject.MP_SubType_ID = microprojectsubtype.ID where Current_State = 1 and MP_ID in ($in)",
    ],
    'attachments' => [
        ['Project number', 'Beneficiary', 'Career', 'Image type', 'Image type (English)', 'Category'], [],
        fn (string $in) => "select MP.MP_ID, $full, career.Name, imagetype.Name, imagetype.Name_en, imagetype.Category FROM image LEFT JOIN imagetype ON image.ImageType_ID = imagetype.ID LEFT JOIN microproject MP ON image.MicroProject_ID = MP.MP_ID
            LEFT JOIN person_microproject PMP ON PMP.MicroProject_ID = MP.MP_ID LEFT JOIN person P ON P.P_ID = PMP.Person_ID $projectJoins $condition AND MP.MP_ID in ($in)",
    ],
    'visit-dates' => [
        ['Project number', 'Career', 'Category', 'Sub category', 'Opening visit', 'M1', 'M2', 'M3', 'M4', 'M5', 'Closing visit'], ['Opening visit', 'M1', 'M2', 'M3', 'M4', 'M5', 'Closing visit'],
        function (string $in) use ($projectJoins) {
            $me = fn (int $n) => "(select mevisit.Date from mevisit where mevisit.Person_ID = (select PMP2.Person_ID from person_microproject PMP2 where PMP2.MicroProject_ID = MP.MP_ID order by (PMP2.PersonType = 'مستفيد') desc, PMP2.PMP_ID limit 1) and mevisit.Number like '$n' order by mevisit.ID limit 1)";

            return "select MP.MP_ID, career.Name, C.Name, sub.Name, (select Date from visit where visit.MicroProject_ID = MP.MP_ID and Kind like '%op' order by visit.ID limit 1), {$me(1)}, {$me(2)}, {$me(3)}, {$me(4)}, {$me(5)},
                (select Date from visit where visit.MicroProject_ID = MP.MP_ID and Kind like '%cl' order by visit.ID limit 1)
                from microproject MP $projectJoins where Current_State = 1 AND MP.MP_ID in ($in) group by MP.MP_ID";
        },
    ],
    'me-visits' => [
        ['Project number', 'Beneficiary', 'Career', 'Visit date', 'Visit number', 'Visit type', 'Net profit', 'Result', 'Evaluation', 'Visitor 1 mark', 'Visitor 2 mark', 'Donor', 'Category', 'Sub category'], ['Visit date'],
        fn (string $in) => "select MP.MP_ID, $full, career.Name, mevisit.Date, Number, (case Kind WHEN 'v' then 'Vehicles' else 'Other projects' End), Profit, Result,
            IFNULL((select concat(meresultranges.Evaluation,'/',meresultranges.Evaluation_en) from meresultranges where Result >= Min and Result <= Max limit 1),''),
            IFNULL((select mevisit_user.Mark FROM mevisit_user WHERE mevisit_user.Visit_ID = mevisit.ID ORDER by mevisit_user.ID ASC LIMIT 1),''), IFNULL((select mevisit_user.Mark FROM mevisit_user WHERE mevisit_user.Visit_ID = mevisit.ID ORDER by mevisit_user.ID DESC LIMIT 1),''),
            IFNULL(donor.Name,''), C.Name, sub.Name FROM mevisit LEFT JOIN person P ON P.P_ID = mevisit.Person_ID LEFT JOIN person_microproject PMP ON PMP.Person_ID = P.P_ID LEFT JOIN microproject MP ON MP.MP_ID = PMP.MicroProject_ID
            LEFT JOIN donor ON donor.ID = MP.MP_Donor $projectJoins where Current_State = 1 AND MP.MP_ID in ($in)",
    ],
    'checklists' => [
        ['Project number', 'Beneficiary', 'Father', 'Career', 'Category', 'Sub category', 'Project sub type', 'Project status', 'Years of experience', 'Home visit', 'Home visit date', 'Home visit time', 'Home visitor 1', 'Home visitor 2', 'Home visit notes',
            'Project visit', 'Project visit date', 'Project visit time', 'Project visitor 1', 'Project visit notes', 'Visits report', 'Needs professional test', 'Reason for the test', 'Professional test done', 'Test date', 'Test notes', 'Test result',
            'Professional training', 'Trainer (professional)', 'Place (professional)', 'Training start', 'Training end', 'Training notes (professional)', 'Training result (professional)', 'Management training', 'Trainer (management)',
            'Place (management)', 'Management training date', 'Management training result', 'General notes', 'Rejection message', 'Rejection message date', 'Key people', 'Team meeting date', 'Team members', 'Committee meeting date', 'Committee members'],
        ['Home visit date', 'Project visit date', 'Test date', 'Training start', 'Training end', 'Management training date', 'Rejection message date', 'Team meeting date', 'Committee meeting date'],
        fn (string $in) => "select PMP.MicroProject_ID, $name, P.P_FatherName, career.Name, C.Name, sub.Name, microprojectsubtype.Name, state.Name_en, PMP.P_WorkYears, {$flag('HasHomeVisit', 'Done', 'Not done')}, HVisit_Date, HVisit_Time, {$visitor('HVisit_Visitor1_ID')}, {$visitor('HVisit_Visitor2_ID')}, HVisit_Note,
            {$flag('HasProjectVisit', 'Done', 'Not done')}, PVisit_Date, PVisit_Time, {$visitor('PVisit_Visitor1_ID')}, PVisit_Note, VisitsReport, {$flag('NeedProTest', 'Needed', 'Not needed')}, NeedProTest_Note, {$flag('HasProTest', 'Yes', 'No')}, ProTest_Date, ProTest_Note, ProTest_Result,
            {$flag('HasProTrain', 'Yes', 'No')}, ProTrain_Trainer, ProTrain_Place, ProTrain_BDate, ProTrain_EDate, ProTrain_Note, ProTrain_Result, {$flag('HasMngTrain', 'Yes', 'No')}, MngTrain_Trainer, MngTrain_Place, MngTrain_Date, MngTrain_Result, Notes,
            {$flag('RejectMsg', 'Sent', 'Not sent')}, RejectMsgDate, {$members('k')}, TeamDate, {$members('t')}, CommityDate, {$members('c')},
            {$visitor('HVisit_Visitor2_ID')} pv2_desktop, {$visitor('PVisit_Visitor2_ID')} pv2_correct
            FROM checklist LEFT JOIN person_microproject PMP on checklist.Person_ID = PMP.Person_ID LEFT JOIN microproject MP on MP.MP_ID = PMP.MicroProject_ID LEFT JOIN person P on PMP.Person_ID = P.P_ID
            LEFT JOIN microprojectsubtype on microprojectsubtype.ID = MP.MP_SubType_ID $projectJoins where Current_State = 1 and PMP.MicroProject_ID in ($in) group by PMP.Person_ID",
    ],
    'special-cases' => [
        ['Project number', 'Beneficiary', 'Father', 'Career', 'Category', 'Sub category', 'Review date', 'Review type', 'Reason', 'Description', 'Action'], ['Review date'],
        fn (string $in) => "select MP_ID, $name, P.P_FatherName, career.Name, C.Name, sub.Name, sc.Date, specialcasetype.Name, sc.Reason, sc.Description, sc.Action FROM specialcase sc LEFT JOIN microproject MP on MP.MP_ID = sc.MicroProject_ID
            LEFT JOIN specialcasetype on specialcasetype.ID = sc.Type_ID LEFT JOIN person_microproject PMP on MP.MP_ID = PMP.MicroProject_ID LEFT JOIN person P on PMP.Person_ID = P.P_ID $projectJoins where Current_State = 1 and MP_ID in ($in)",
    ],
    'educations' => [
        ['Project number', 'Name', 'Relation', 'Education level', 'Degree', 'Specialization'], [],
        fn (string $in) => "select PMP.MicroProject_ID, $name, (SELECT Name FROM relation WHERE relation.ID = pf.Relation_ID LIMIT 1),
            IFNULL((SELECT Name FROM educationlevel WHERE educationlevel.ID = COALESCE(pf.EducationLevel_ID, pe.Level_ID) LIMIT 1),''),
            IFNULL((SELECT et.Name FROM educationtype et LEFT JOIN education e ON et.ID = e.Type_ID WHERE e.ID = COALESCE(pf.Education_ID, pe.Education_ID) LIMIT 1),''),
            IFNULL((SELECT Name FROM education WHERE education.ID = COALESCE(pf.Education_ID, pe.Education_ID) LIMIT 1),'')
            FROM person_family pf LEFT JOIN person P ON P.P_ID = pf.Person_ID LEFT JOIN person_microproject PMP ON PMP.Person_ID = pf.P_Provider_ID
            LEFT JOIN person_education pe ON pe.Person_ID = pf.P_Provider_ID AND pf.Person_ID = pf.P_Provider_ID LEFT JOIN microproject MP ON MP.MP_ID = PMP.MicroProject_ID $projectJoins where Current_State = 1 and PMP.MicroProject_ID in ($in)",
    ],
];
$normalizeOther = function ($value, bool $date) use ($normalize): string {
    return $normalize($value, $date);
};
foreach ($other as $mode => [$webColumns, $dateColumns, $sqlFor]) {
    $webLines = [];
    foreach ($csv('/en/search-reports/export?mode='.$mode) as $row) {
        $line = [$row['City']];
        foreach ($webColumns as $column) {
            $line[] = $normalizeOther($row[$column] ?? '', in_array($column, $dateColumns, true));
        }
        $webLines[] = $line;
    }
    $desktopLines = [];
    foreach ($cities as $city) {
        $in = $inList(array_keys($webProjects[$city]));
        foreach ($sources[$city]->query($sqlFor($in), PDO::FETCH_NUM) as $d) {
            $values = array_slice($d, 0, count($webColumns));
            $line = [$city];
            foreach ($values as $i => $value) {
                $line[] = $normalizeOther($value, in_array($webColumns[$i], $dateColumns, true));
            }
            if ($mode === 'checklists') {
                // The desktop SQL reads the home-visit second visitor for the project-visit second visitor; the web shows the project-visit one.
                [$buggy, $correct] = [$d[count($webColumns)], $d[count($webColumns) + 1]];
                if ($buggy !== $correct) {
                    $known['Check list: desktop shows the home-visit second visitor as the project-visit one (web shows the project-visit one; column not compared)'][] = 'differs';
                }
            }
            $desktopLines[] = $line;
        }
    }
    $join = fn (array $lines) => array_map(fn (array $l) => implode('|', array_map(fn ($v) => is_numeric($v) ? (string) (float) $v : $v, $l)), $lines);
    if ($mode === 'visit-dates') {
        // Names are not in the compared columns; the number of projects and their dates are.
    }
    $compareLines('other modes', $mode, $join($webLines), $join($desktopLines));
}
// Meetings are not tied to projects: every web meeting must exist, identically, in the desktop's meetings of the same city.
$meetingColumns = ['Meeting number', 'Meeting date', 'Career', 'Career (Arabic)', 'Category', 'Sub category', 'Place', 'Invited', 'Attended', 'Absent'];
$webMeetings = [];
foreach ($csv('/en/search-reports/export?mode=meetings') as $row) {
    $line = [$row['City']];
    foreach ($meetingColumns as $column) {
        $line[] = $normalize($row[$column] ?? '', $column === 'Meeting date');
    }
    $webMeetings[] = implode('|', array_map(fn ($v) => is_numeric($v) ? (string) (float) $v : $v, $line));
}
$desktopMeetings = [];
foreach ($cities as $city) {
    $sql = "SELECT pm.ID, pm.MeetingDate, IFNULL(career.Name_en,''), IFNULL(career.Name,''), IFNULL(C.Name,''), IFNULL(sub.Name,''), IFNULL(pm.Place,''), IFNULL(pm.InvitedCount,0), IFNULL(pm.AttendanceCount,0), IFNULL(pm.AbsenceCount,0)
        FROM profession_meeting pm LEFT JOIN career ON career.ID = pm.Career_ID LEFT JOIN subcategory sub ON sub.ID = pm.SubCategory_ID LEFT JOIN category C ON C.ID = sub.Category_ID";
    foreach ($sources[$city]->query($sql, PDO::FETCH_NUM) as $d) {
        $line = [$city];
        foreach ($d as $i => $value) {
            $line[] = $normalize($value, $i === 1);
        }
        $desktopMeetings[] = implode('|', array_map(fn ($v) => is_numeric($v) ? (string) (float) $v : $v, $line));
    }
}
$webKeys = array_map(fn ($l) => explode('|', $l)[0].'|'.explode('|', $l)[1], $webMeetings);
$desktopMeetings = array_values(array_filter($desktopMeetings, fn ($l) => in_array(explode('|', $l)[0].'|'.explode('|', $l)[1], $webKeys, true)));
$compareLines('other modes', 'meetings', $webMeetings, $desktopMeetings);

// 8. Loan report filters: fund type, project type and sub type, partnership, category, sub category, career, funded amount (USD) ------
$loanFrom = ' from loan L LEFT JOIN microproject MP ON L.MicroProject_ID = MP.MP_ID LEFT JOIN category C ON MP.Category_ID = C.ID LEFT JOIN subcategory sub ON MP.SubCategory_ID = sub.ID
    LEFT JOIN career ON MP.Career_ID = career.ID LEFT JOIN microproject_state mps ON mps.MicroProject_ID = MP.MP_ID LEFT JOIN state ON state.ID = mps.State_ID
    LEFT JOIN fundtype ON fundtype.ID = MP.MP_FundType_ID LEFT JOIN microprojecttype ON microprojecttype.ID = MP.MP_Type_ID LEFT JOIN microprojectsubtype ON microprojectsubtype.ID = MP.MP_SubType_ID';
$usdSql = "Round((CASE Currency WHEN 'SP' THEN Loan_Amount / ExchangeRate WHEN 'USD' THEN Loan_Amount END), 2)";
$desktopLoanCount = function (string $extra) use ($cities, $sources, $webProjects, $inList, $loanFrom): int {
    $total = 0;
    foreach ($cities as $city) {
        $sql = 'select count(*) from (select L.MicroProject_ID '.$loanFrom.' where Current_State = 1 and L.MicroProject_ID in ('.$inList(array_keys($webProjects[$city])).') '.$extra.' group by MP.MP_ID) t';
        $total += (int) $sources[$city]->query($sql)->fetchColumn();
    }

    return $total;
};
$quote = fn (array $names) => implode(',', array_map(fn ($n) => "'".str_replace("'", "''", $n)."'", $names));
// The most common names among the sample's loans, per list.
$lists = ['fund' => ['fundtype.Name', 'fund[]'], 'type' => ['microprojecttype.Name', 'type[]'], 'subtype' => ['microprojectsubtype.Name', 'subtype[]'],
    'category' => ['C.Name', 'category[]'], 'subcategory' => ['sub.Name', 'subcategory[]'], 'career' => ['career.Name', 'career[]']];
$webLoanCount = fn (array $params) => count($csv('/en/search-reports/export?mode=loans&'.http_build_query($params)));
$filterCases = 0;
foreach ($lists as $key => [$column, $unused]) {
    $frequency = [];
    foreach ($cities as $city) {
        $sql = "select $column n, count(*) c $loanFrom where Current_State = 1 and L.MicroProject_ID in (".$inList(array_keys($webProjects[$city])).") and $column is not null group by $column";
        foreach ($sources[$city]->query($sql, PDO::FETCH_ASSOC) as $d) {
            $frequency[$d['n']] = ($frequency[$d['n']] ?? 0) + (int) $d['c'];
        }
    }
    arsort($frequency);
    $top = array_slice(array_keys($frequency), 0, 2);
    foreach ([[$top[0] ?? null], $top, ['name that does not exist']] as $names) {
        if ($names === [null] || $names === []) {
            continue;
        }
        $webCount = $webLoanCount([$key => $names]);
        $desktopCount = $desktopLoanCount(" and $column in (".$quote($names).')');
        $count('loan filters', "$key: ".implode(' + ', $names), "web $webCount / desktop $desktopCount");
        $filterCases++;
        if ($webCount !== $desktopCount) {
            $note('loan filters', 'count', "$key=".implode(',', $names)." web=$webCount desktop=$desktopCount");
        }
    }
}
foreach (['individual' => 'and Partnership = 1', 'partnership' => 'and Partnership > 1'] as $choice => $condition) {
    $webCount = $webLoanCount(['partnership' => $choice]);
    $desktopCount = $desktopLoanCount(' '.$condition);
    $count('loan filters', "partnership: $choice", "web $webCount / desktop $desktopCount");
    $filterCases++;
    if ($webCount !== $desktopCount) {
        $note('loan filters', 'count', "partnership=$choice web=$webCount desktop=$desktopCount");
    }
}
// Funded-amount ranges taken from the real values: quartiles, an exact boundary, a single open end and an empty range.
$values = [];
foreach ($cities as $city) {
    $sql = "select $usdSql v $loanFrom where Current_State = 1 and L.MicroProject_ID in (".$inList(array_keys($webProjects[$city])).') and '.$usdSql.' is not null';
    foreach ($sources[$city]->query($sql, PDO::FETCH_COLUMN, 0) as $v) {
        $values[] = (float) $v;
    }
}
sort($values);
$at = fn (float $q) => $values[(int) floor((count($values) - 1) * $q)] ?? 0.0;
$ranges = [
    'q25 to q75' => [$at(0.25), $at(0.75)], 'exact boundary (one loan)' => [$at(0.5), $at(0.5)], 'from q90 (open end)' => [$at(0.9), null], 'to q10 (open start)' => [null, $at(0.1)],
    'from 0 to max' => [0.0, end($values) ?: 0.0], 'empty (above the maximum)' => [(end($values) ?: 0.0) + 1000, (end($values) ?: 0.0) + 2000],
];
foreach ($ranges as $label => [$from, $to]) {
    $params = array_filter(['funded_from' => $from, 'funded_to' => $to], fn ($v) => $v !== null);
    $webCount = $webLoanCount($params);
    // The desktop turns a blank end into 0; the web leaves it open, so the desktop side states the open end explicitly.
    $desktopCount = $desktopLoanCount(' and '.$usdSql.' BETWEEN '.($from ?? 0).' and '.($to ?? 1.0E+15));
    $count('loan filters', "funded USD $label", "web $webCount / desktop $desktopCount");
    $filterCases++;
    if ($webCount !== $desktopCount) {
        // Name the loans that differ: the desktop's USD value next to the web's.
        $webKeys = [];
        foreach ($csv('/en/search-reports/export?mode=loans&'.http_build_query($params)) as $row) {
            $webKeys[$row['City'].'|'.$row['Project number']] = $row['Loan (USD)'];
        }
        $desktopKeys = [];
        foreach ($cities as $city) {
            $sql = "select L.MicroProject_ID pn, $usdSql v $loanFrom where Current_State = 1 and L.MicroProject_ID in (".$inList(array_keys($webProjects[$city])).') and '.$usdSql.' BETWEEN '.($from ?? 0).' and '.($to ?? 1.0E+15).' group by MP.MP_ID';
            foreach ($sources[$city]->query($sql, PDO::FETCH_ASSOC) as $d) {
                $desktopKeys[$city.'|'.$d['pn']] = $d['v'];
            }
        }
        $detail = [];
        foreach (array_diff_key($webKeys, $desktopKeys) as $key => $value) {
            $detail[] = "only web $key usd=$value";
        }
        foreach (array_diff_key($desktopKeys, $webKeys) as $key => $value) {
            $detail[] = "only desktop $key usd=$value";
        }
        $note('loan filters', 'count', "funded $label [$from, $to] web=$webCount desktop=$desktopCount: ".implode('; ', $detail));
    }
}
// Combined: a name list, a partnership and an amount range together.
$combined = ['partnership' => 'individual', 'funded_from' => $at(0.1), 'funded_to' => $at(0.9)];
$webCount = $webLoanCount($combined);
$desktopCount = $desktopLoanCount(' and Partnership = 1 and '.$usdSql.' BETWEEN '.$at(0.1).' and '.$at(0.9));
$count('loan filters', 'individual + funded q10 to q90', "web $webCount / desktop $desktopCount");
$filterCases++;
if ($webCount !== $desktopCount) {
    $note('loan filters', 'count', "combined web=$webCount desktop=$desktopCount");
}
$count('loan filters', 'cases', $filterCases);

// 9. Lookup tables and the project state history export ----------------------------------------------------
$lookupTables = ['street', 'aidsource', 'donor', 'donorgroup', 'career', 'category', 'subcategory', 'statereason', 'worktype', 'relation', 'educationlevel', 'fundtype', 'microprojecttype', 'maritalstatus', 'loss', 'illness', 'goal', 'aid', 'aidperiod', 'powersource', 'imagetype'];
foreach ($lookupTables as $table) {
    $webSet = [];
    foreach ($csv('/en/lookups/'.$table.'/export') as $row) {
        $webSet[] = $row['City'].'|'.(string) (float) $row['ID'].'|'.trim($row['Name']);
    }
    $desktopSet = [];
    foreach ($cities as $city) {
        foreach ($sources[$city]->query("select ID, Name from `$table`", PDO::FETCH_NUM) as $d) {
            $desktopSet[] = $city.'|'.(string) (float) $d[0].'|'.trim((string) $d[1]);
        }
    }
    sort($webSet);
    sort($desktopSet);
    $missing = array_values(array_diff($webSet, $desktopSet));
    $absent = array_values(array_diff($desktopSet, $webSet));
    $count('lookups', $table, 'web '.count($webSet).' / desktop '.count($desktopSet));
    foreach (array_slice($missing, 0, 3) as $x) {
        $note('lookups', "$table row not in desktop", $x);
    }
    if ($sample) {
        // The sample copies catalog rows only through resolved links, so the web may hold fewer rows than the desktop, never more.
        if ($absent !== []) {
            $known['Lookup rows not in the sample ('.$table.')'][] = count($absent).' rows';
        }
    } else {
        foreach (array_slice($absent, 0, 3) as $x) {
            $note('lookups', "$table row missing in web", $x);
        }
    }
}
// The state history of every project the web holds, against the desktop's microproject_state rows.
$stateStates = 0;
$projectIds = [];
for ($page = 1; $page <= 200; $page++) {
    $html = $http($web.'/en/applications?page='.$page);
    preg_match_all('#/en/applications/(\d+)"#', $html, $m);
    $new = array_diff($m[1], $projectIds);
    if ($new === []) {
        break;
    }
    $projectIds = array_merge($projectIds, $new);
}
foreach (array_values(array_unique($projectIds)) as $id) {
    $rows = $csv('/en/applications/'.$id.'/states/export');
    if ($rows === []) {
        continue;
    }
    $city = $rows[0]['City'];
    $number = (int) $rows[0]['Project number'];
    $webStates = array_map(fn ($r) => $r['State'].'|'.$r['State date'].'|'.$r['Current'], $rows);
    $desktopStates = [];
    foreach ($sources[$city]->query('select state.Name_en n, DATE(mps.Date) d, mps.Current_State c from microproject_state mps left join state on state.ID = mps.State_ID where mps.MicroProject_ID = '.$number, PDO::FETCH_ASSOC) as $d) {
        $desktopStates[] = $d['n'].'|'.$d['d'].'|'.((int) $d['c'] === 1 ? 'Yes' : 'No');
    }
    sort($webStates);
    sort($desktopStates);
    $stateStates += count($webStates);
    if ($webStates !== $desktopStates) {
        $note('state history', 'project states', "$city|$number web=".implode(',', $webStates).' desktop='.implode(',', $desktopStates));
    }
}
if ($projectIds === []) {
    $note('state history', 'no projects', 'the application list returned no project to compare');
}
$count('state history', 'projects', count(array_unique($projectIds)));
$count('state history', 'state rows compared', $stateStates);

// ---- Report -------------------------------------------------------------------------------------------
$report['known_differences'] = array_map(fn ($items) => ['count' => count($items), 'examples' => array_slice($items, 0, 3)], $known);
$report['failures'] = $failures;
foreach ($report['checks'] as $check => $data) {
    $mismatches = $data['mismatches'] ?? [];
    echo str_pad($check, 13).(count($mismatches) === 0 ? 'OK' : 'MISMATCHES').'  '.json_encode($data['counts'] ?? [], JSON_UNESCAPED_UNICODE)."\n";
    foreach ($mismatches as $kind => $items) {
        echo '   '.$kind.': '.count($items).' e.g. '.implode(' ; ', array_slice($items, 0, 3))."\n";
    }
}
foreach (['applications', 'statistics', 'loans', 'instalments', 'family', 'details', 'other modes', 'loan filters', 'lookups', 'state history'] as $check) {
    if (! isset($report['checks'][$check])) {
        $report['checks'][$check] = ['mismatches' => []];
    }
}
foreach ($report['known_differences'] as $kind => $info) {
    echo "known difference: $kind ({$info['count']})\n";
}
echo $failures === 0 ? "RESULT: reconciled, 0 unexplained differences\n" : "RESULT: $failures unexplained differences\n";
if (! empty($options['out'])) {
    file_put_contents($options['out'], json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
@unlink($jar);
exit($failures === 0 ? 0 : 1);

function cityId(callable $csv, string $city, callable $http, string $web): int
{
    // City ids come from the applications page's city filter, so the oracle needs no database access to the web side.
    $html = $http($web.'/en/applications');
    preg_match_all('/name="cities\[\]"\s+value="(\d+)"[^>]*>\s*(?:<span>)?\s*([^<\s][^<]*?)\s*</', $html, $m, PREG_SET_ORDER);
    foreach ($m as $hit) {
        if (trim($hit[2]) === $city) {
            return (int) $hit[1];
        }
    }
    throw new RuntimeException("No city filter for $city");
}
