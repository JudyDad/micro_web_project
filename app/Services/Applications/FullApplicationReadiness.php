<?php

declare(strict_types=1);

namespace App\Services\Applications;

use App\Services\Operational\OperationalTables;
use Illuminate\Support\Collection;

/**
 * Read-only readiness check for promoting a preliminary application to a full one.
 *
 * It applies the desktop rules for a new beneficiary and a new project in the "project" state
 * (Application_New_Form.Save_Application, validate* flags all true) to the rows already stored for the
 * application's beneficiary. Nothing is written and no authorization happens here: the caller authorizes.
 *
 * Interpretation: the web has no per-section dirty tracking, so a conversion is gated as if every section were
 * touched. Rows that exist but fail a rule are reported with their identifier; legacy values are never changed.
 *
 * Project identity fields stay with ApplicationService::requireIntake; this class adds the full-form extras.
 * Not covered (listed in docs/delivery/full-application-readiness.md): family members' own work rows, goal
 * check boxes, national-number / family-number duplicate prompts and the paper-application user.
 */
final class FullApplicationReadiness
{
    private const MALE = 'ذكر';

    private const BENEFICIARY = 'مستفيد';

    private const NOT_WORKING = 'لا يعمل';

    private const OWNED = 'ملك';

    private const NOT_EDUCATED = 'غير متعلّم';

    /** @var list<array{section: string, code: string, field: string, row: ?int}> */
    private array $issues = [];

    public function __construct(private OperationalTables $tables) {}

    /** @return list<array{section: string, code: string, field: string, row: ?int}> */
    public function check(object $application): array
    {
        $this->issues = [];
        $city = (int) $application->city_id;
        $source = (int) $application->source_id;
        $link = $this->beneficiaryLink($application);
        if ($link === null) {
            $this->add('beneficiary', 'required', 'beneficiary');

            return $this->issues;
        }
        $personId = (int) $link->{$this->tables->relation('person_microproject', 'Person_ID')['resolved_column']};
        $person = $this->tables->find('person', $personId, $city, $source);
        if ($person === null) {
            $this->add('beneficiary', 'required', 'beneficiary');

            return $this->issues;
        }
        $this->beneficiary($person, $city, $source);
        $this->story($link);
        $this->work($link, $person, $city, $source);
        $this->education($link, $person, $city, $source);
        $this->projectInfo($application, $city, $source);
        $this->family($link, $person, $city, $source);
        $this->support($link, $person, $city, $source);

        return $this->issues;
    }

    private function add(string $section, string $code, string $field, ?int $row = null): void
    {
        $this->issues[] = ['section' => $section, 'code' => $code, 'field' => $field, 'row' => $row];
    }

    private function blank(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    private function text(string $section, object $row, string $column, ?string $field = null): void
    {
        if ($this->blank($row->{$column} ?? null)) {
            $this->add($section, 'required', $field ?? substr($column, 2), (int) $row->id);
        }
    }

    private function ref(string $section, string $table, object $row, string $key): ?int
    {
        $relation = $this->tables->relation($table, $key);
        if (($row->{$relation['state_column']} ?? null) === 'resolved' && $row->{$relation['resolved_column']} !== null) {
            return (int) $row->{$relation['resolved_column']};
        }
        $this->add($section, 'required', $key, (int) $row->id);

        return null;
    }

    /** Resolved relationship without reporting a missing value. */
    private function refOrNull(string $table, object $row, string $key): ?int
    {
        $relation = $this->tables->relation($table, $key);

        return ($row->{$relation['state_column']} ?? null) === 'resolved' ? (int) $row->{$relation['resolved_column']} : null;
    }

    /** A yes/no switch is "chosen" when it is 0 or 1; null and -1 mean not chosen. */
    private function chosen(string $section, object $row, string $column): ?int
    {
        $value = $row->{$column} ?? null;
        if ($value === null || $value === '' || ! in_array((int) $value, [0, 1], true)) {
            $this->add($section, 'choice', substr($column, 2), (int) $row->id);

            return null;
        }

        return (int) $value;
    }

    private function rowsOf(string $table, string $key, int $id, int $city, int $source): Collection
    {
        $relation = $this->tables->relation($table, $key);

        return $this->tables->query($table)->where('p.city_id', $city)->where('p.source_id', $source)
            ->where('p.'.$relation['resolved_column'], $id)->where('p.'.$relation['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function catalogName(string $table, ?int $id, int $city, int $source, string $column = 'c_Name'): ?string
    {
        if ($id === null) {
            return null;
        }

        return $this->tables->find($table, $id, $city, $source)?->{$column};
    }

    private function beneficiaryLink(object $application): ?object
    {
        $projectRef = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $personRef = $this->tables->relation('person_microproject', 'Person_ID');

        return $this->tables->query('person_microproject')->where('p.city_id', $application->city_id)->where('p.source_id', $application->source_id)
            ->where('p.'.$projectRef['resolved_column'], $application->id)->where('p.'.$personRef['state_column'], 'resolved')
            ->where('p.c_PersonType', self::BENEFICIARY)->orderBy('p.id')->first();
    }

    private function beneficiary(object $person, int $city, int $source): void
    {
        foreach (['c_P_FirstName', 'c_P_LastName', 'c_P_Sex', 'c_P_FatherName', 'c_P_MotherName', 'c_P_NationalNumber', 'c_P_DOB', 'c_P_IntermidiarySide', 'c_P_HomeAddress'] as $column) {
            $this->text('beneficiary', $person, $column);
        }
        foreach (['P_MaritalStatus_ID', 'P_Parish_ID', 'Street_ID', 'P_HomeType_ID'] as $key) {
            $this->ref('beneficiary', 'person', $person, $key);
        }
        if ($this->refOrNull('person', $person, 'P_Priest_ID') === null && $this->refOrNull('person', $person, 'P_Priest2_ID') === null) {
            $this->add('beneficiary', 'required', 'Priest', (int) $person->id);
        }
        if ($this->blank($person->c_P_Mobile ?? null) && $this->blank($person->c_P_HomeTel ?? null) && $this->blank($person->c_P_Mobile2 ?? null)) {
            $this->add('beneficiary', 'required', 'contact', (int) $person->id);
        }
        if (($person->c_P_Sex ?? null) === self::MALE && in_array($person->c_P_MilitaryService ?? null, ['مسرح', 'عسكري'], true)) {
            if ($this->blank($person->c_P_MilitaryPeriod ?? null)) {
                $this->add('beneficiary', 'required', 'MilitaryPeriod', (int) $person->id);
            }
            if ($person->c_P_IsMilitaryInWar === null || ! in_array((int) $person->c_P_IsMilitaryInWar, [0, 1], true)) {
                $this->add('beneficiary', 'choice', 'IsMilitaryInWar', (int) $person->id);
            }
        }
    }

    private function story(object $link): void
    {
        foreach (['c_P_Loss', 'c_P_Goal', 'c_P_GoalHow'] as $column) {
            $this->text('story', $link, $column);
        }
    }

    private function work(object $link, object $person, int $city, int $source): void
    {
        $has = $this->chosen('work', $link, 'c_P_HasWork');
        $this->text('work', $link, 'c_P_WorkYears');
        $rows = $this->rowsOf('person_workexp', 'Person_ID', (int) $person->id, $city, $source);
        $current = $rows->filter(fn ($row) => strtolower((string) $row->c_W_Status) === 'yes');
        $prior = $rows->filter(fn ($row) => strtolower((string) $row->c_W_Status) === 'no');
        if ($has === 1 && $current->isEmpty()) {
            $this->add('work', 'required_rows', 'current');
        }
        foreach ($current as $row) {
            $this->ref('work', 'person_workexp', $row, 'Work_ID');
            $this->ref('work', 'person_workexp', $row, 'W_WorkType_ID');
            foreach (['c_W_BeginDate', 'c_W_PlaceState', 'c_W_Place', 'c_W_Salary'] as $column) {
                $this->text('work', $row, $column);
            }
        }
        foreach ($prior as $row) {
            $work = $this->ref('work', 'person_workexp', $row, 'Work_ID');
            $this->text('work', $row, 'c_W_BeginDate');
            $this->text('work', $row, 'c_W_EndDate');
            if ($work !== null && $this->catalogName('work', $work, $city, $source, 'c_W_Name') !== self::NOT_WORKING) {
                $this->ref('work', 'person_workexp', $row, 'W_WorkType_ID');
                foreach (['c_W_PlaceState', 'c_W_Place', 'c_W_CauseOfLoseDescription'] as $column) {
                    $this->text('work', $row, $column);
                }
            }
        }
    }

    private function education(object $link, object $person, int $city, int $source): void
    {
        $hasEducation = $this->chosen('education', $link, 'c_P_HasEducation');
        $education = $this->rowsOf('person_education', 'Person_ID', (int) $person->id, $city, $source);
        if ($hasEducation === 1 && $education->isEmpty()) {
            $this->add('education', 'required_rows', 'education');
        }
        foreach ($education as $row) {
            $this->ref('education', 'person_education', $row, 'Level_ID');
            $this->ref('education', 'person_education', $row, 'Education_ID');
        }
        $hasCourses = $this->chosen('education', $link, 'c_P_HasCourses');
        $courses = $this->rowsOf('person_course', 'Person_ID', (int) $person->id, $city, $source);
        if ($hasCourses === 1 && $courses->isEmpty()) {
            $this->add('education', 'required_rows', 'courses');
        }
        foreach ($courses as $row) {
            $this->ref('education', 'person_course', $row, 'SubCategory_ID');
            $this->text('education', $row, 'c_Name');
            $this->text('education', $row, 'c_Date');
        }
    }

    private function projectInfo(object $application, int $city, int $source): void
    {
        $this->ref('project', 'microproject', $application, 'MP_PlaceType_ID');
        foreach (range(1, 5) as $n) {
            $value = $application->{'c_MP_Agreement'.$n} ?? null;
            if ($value === null || $value === '' || ! in_array((int) $value, [0, 1], true)) {
                $this->add('project', 'choice', 'MP_Agreement'.$n, (int) $application->id);
            }
        }
        $materials = $this->rowsOf('material', 'MicroProject_ID', (int) $application->id, $city, $source);
        if ($materials->isEmpty()) {
            $this->add('project', 'required_rows', 'material');
        }
        foreach ($materials as $row) {
            foreach (['c_Name', 'c_Price', 'c_Amount'] as $column) {
                $this->text('project', $row, $column);
            }
        }
        foreach ($this->rowsOf('microproject_powersource', 'MicroProject_ID', (int) $application->id, $city, $source) as $row) {
            $this->ref('project', 'microproject_powersource', $row, 'PowerSource_ID');
        }
    }

    private function family(object $link, object $person, int $city, int $source): void
    {
        $familyRef = $this->tables->relation('person_family', 'Family_ID');
        $personRef = $this->tables->relation('person_family', 'Person_ID');
        $providerRef = $this->tables->relation('person_family', 'P_Provider_ID');
        // Members are rows provided by this beneficiary; the beneficiary's own row carries the family number.
        $members = $this->tables->query('person_family')->where('p.city_id', $city)->where('p.source_id', $source)
            ->where('p.'.$providerRef['resolved_column'], $person->id)->where('p.'.$providerRef['state_column'], 'resolved')->orderBy('p.id')->get();
        $own = $this->tables->query('person_family')->where('p.city_id', $city)->where('p.source_id', $source)
            ->where('p.'.$personRef['resolved_column'], $person->id)->where('p.'.$personRef['state_column'], 'resolved')->orderBy('p.id')->first();
        $family = $own !== null && ($own->{$familyRef['state_column']} ?? null) === 'resolved'
            ? $this->tables->find('family', (int) $own->{$familyRef['resolved_column']}, $city, $source) : null;
        if ($family === null || $this->blank($family->c_F_Number ?? null)) {
            $this->add('family', 'required', 'FamilyNumber');
        }
        $this->chosen('family', $link, 'c_P_IsLivingWithFamily');
        foreach ($members as $row) {
            $memberId = $this->refOrNull('person_family', $row, 'Person_ID');
            if ($memberId === (int) $person->id) {
                continue; // The beneficiary's own row (relation "مستفيد") carries the family number, not a member.
            }
            $member = $memberId === null ? null : $this->tables->find('person', $memberId, $city, $source);
            if ($member === null) {
                $this->add('family', 'required', 'member', (int) $row->id);

                continue;
            }
            foreach (['c_P_FirstName', 'c_P_LastName', 'c_P_FatherName', 'c_P_DOB', 'c_P_Sex', 'c_P_NationalNumber'] as $column) {
                if ($this->blank($member->{$column} ?? null)) {
                    $this->add('family', 'required', substr($column, 2), (int) $row->id);
                }
            }
            $this->ref('family', 'person_family', $row, 'Relation_ID');
            $this->ref('family', 'person', $member, 'P_State_ID');
            $level = $this->ref('family', 'person_family', $row, 'EducationLevel_ID');
            if ($level !== null && $this->catalogName('educationlevel', $level, $city, $source) !== self::NOT_EDUCATED) {
                $this->ref('family', 'person_family', $row, 'Education_ID');
            }
        }
    }

    private function support(object $link, object $person, int $city, int $source): void
    {
        $id = (int) $person->id;
        $has = fn (string $column) => $this->chosen('support', $link, $column);

        if ($has('c_P_HasLoss') === 1 && $this->rowsOf('person_loss', 'Person_ID', $id, $city, $source)->isEmpty()) {
            $this->add('support', 'required_rows', 'loss');
        }
        foreach ($this->rowsOf('person_loss', 'Person_ID', $id, $city, $source) as $row) {
            $this->ref('support', 'person_loss', $row, 'Loss_ID');
            $this->text('support', $row, 'c_Year');
        }

        $illnesses = $this->rowsOf('person_illness', 'Person_ID', $id, $city, $source);
        if ($has('c_P_HasIllness') === 1 && $illnesses->isEmpty()) {
            $this->add('support', 'required_rows', 'illness');
        }
        foreach ($illnesses as $row) {
            $this->ref('support', 'person_illness', $row, 'Illness_ID');
            if ((int) ($row->c_Has_MedicalAid ?? 0) === 1) {
                $this->ref('support', 'person_illness', $row, 'AidSource_ID');
            }
        }

        $assets = $this->rowsOf('person_asset', 'Person_ID', $id, $city, $source);
        if ($has('c_P_HasAssets') === 1 && $assets->isEmpty()) {
            $this->add('support', 'required_rows', 'asset');
        }
        foreach ($assets as $row) {
            $this->ref('support', 'person_asset', $row, 'Asset_ID');
            $type = $this->ref('support', 'person_asset', $row, 'Asset_Type_ID');
            $this->ref('support', 'person_asset', $row, 'Asset_Benefits_ID');
            if ($type !== null && $this->catalogName('assettype', $type, $city, $source) === self::OWNED) {
                $this->text('support', $row, 'c_Year');
            }
        }

        $aid = $this->rowsOf('person_aidsource', 'Person_ID', $id, $city, $source);
        if ($has('c_P_HasAidSource') === 1 && $aid->isEmpty()) {
            $this->add('support', 'required_rows', 'aid');
        }
        foreach ($aid as $row) {
            $this->ref('support', 'person_aidsource', $row, 'AidSource_ID');
            $this->text('support', $row, 'c_DateYear');
        }
        $has('c_P_HasDebts');

        $help = $this->rowsOf('person_help', 'Person_ID', $id, $city, $source);
        if ($has('c_P_HasHelp') === 1 && $help->isEmpty()) {
            $this->add('support', 'required_rows', 'help');
        }
        foreach ($help as $row) {
            foreach (['AidSource_ID', 'Aid_ID', 'Aidperiod_ID'] as $key) {
                $this->ref('support', 'person_help', $row, $key);
            }
        }

        $power = $this->rowsOf('person_powersource', 'Person_ID', $id, $city, $source);
        if ($power->isEmpty()) {
            $this->add('support', 'required_rows', 'homepower');
        }
        foreach ($power as $row) {
            $this->ref('support', 'person_powersource', $row, 'PowerSource_ID');
            $this->text('support', $row, 'c_Note');
            $this->text('support', $row, 'c_Responsability');
        }
    }
}
