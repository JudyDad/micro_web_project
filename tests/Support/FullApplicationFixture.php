<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\Operational\OperationalTables;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;

/** Invented data only: makes a preliminary application satisfy every full-application readiness rule. */
final class FullApplicationFixture
{
    public static function complete(int $project, int $person, array $intake, int $city = 1): void
    {
        $t = app(OperationalTables::class);
        $row = fn (string $kind, array $fields = [], array $references = []) => ApplicationFixture::row($kind, $city, $fields, $references);
        $catalog = function (string $kind, string $name = 'Synthetic') use ($row) {
            $columns = array_column(OperationalManifest::table($kind)['columns'], 'name');
            $key = collect(['c_ID', 'c_W_ID', 'c_Priest_ID'])->first(fn ($column) => in_array($column, $columns, true));
            $label = collect(['c_Name', 'c_W_Name', 'c_Name_ar', 'c_Priest_Name'])->first(fn ($column) => in_array($column, $columns, true));

            return $row($kind, [$key => random_int(1000, 9999), $label => $name]);
        };

        $homeType = $catalog('assettype', 'ملك');
        DB::table('op_person')->where('id', $person)->update($t->link('person', 'P_MaritalStatus_ID', $catalog('maritalstatus'))
            + $t->link('person', 'P_Parish_ID', $catalog('parish')) + $t->link('person', 'P_Priest_ID', $catalog('priest'))
            + $t->link('person', 'Street_ID', $catalog('street')) + $t->link('person', 'P_HomeType_ID', $homeType) + [
                'c_P_FirstName' => 'سامر', 'c_P_LastName' => 'تجريبي', 'c_P_Sex' => 'أنثى', 'c_P_FatherName' => 'أب', 'c_P_MotherName' => 'أم',
                'c_P_NationalNumber' => '000000001', 'c_P_DOB' => '1990-01-01', 'c_P_IntermidiarySide' => 'جهة', 'c_P_HomeAddress' => 'عنوان تجريبي', 'c_P_Mobile' => '0000',
            ]);
        DB::table('op_person_microproject')->where('c_PersonType', 'مستفيد')->update([
            'c_P_Loss' => 'قصة', 'c_P_Goal' => 'هدف', 'c_P_GoalHow' => 'كيف', 'c_P_HasWork' => 1, 'c_P_WorkYears' => '3', 'c_P_HasEducation' => 1, 'c_P_HasCourses' => 1,
            'c_P_IsLivingWithFamily' => 0, 'c_P_HasLoss' => 1, 'c_P_HasIllness' => 1, 'c_P_HasAssets' => 1, 'c_P_HasAidSource' => 1, 'c_P_HasDebts' => 0, 'c_P_HasHelp' => 1,
        ]);
        $work = $catalog('work');
        $workType = $catalog('worktype');
        $row('person_workexp', ['c_W_Status' => 'Yes', 'c_W_BeginDate' => '2020', 'c_W_PlaceState' => 'ملك', 'c_W_Place' => 'مكان', 'c_W_Salary' => '100'], ['Person_ID' => $person, 'Work_ID' => $work, 'W_WorkType_ID' => $workType]);
        $row('person_workexp', ['c_W_Status' => 'NO', 'c_W_BeginDate' => '2015', 'c_W_EndDate' => '2019', 'c_W_PlaceState' => 'إيجار', 'c_W_Place' => 'مكان', 'c_W_CauseOfLoseDescription' => 'سبب'], ['Person_ID' => $person, 'Work_ID' => $work, 'W_WorkType_ID' => $workType]);
        $level = $catalog('educationlevel');
        $education = $catalog('education');
        $row('person_education', [], ['Person_ID' => $person, 'Level_ID' => $level, 'Education_ID' => $education]);
        $row('person_course', ['c_Name' => 'دورة', 'c_Date' => '2022'], ['Person_ID' => $person, 'SubCategory_ID' => $intake['references']['SubCategory_ID']]);

        $parents = fn (string $key) => $t->relation('microproject', $key)['parent_source_table'];
        DB::table('op_microproject')->where('id', $project)->update($t->link('microproject', 'MP_PlaceType_ID', $row($parents('MP_PlaceType_ID'), ['c_ID' => 5]))
            + $t->link('microproject', 'MP_FundType_ID', $row($parents('MP_FundType_ID'), ['c_ID' => 6]))
            + ['c_MP_RequestedAmount' => '1500.5', 'c_MP_Agreement1' => 1, 'c_MP_Agreement2' => 0, 'c_MP_Agreement3' => 1, 'c_MP_Agreement4' => 1, 'c_MP_Agreement5' => 0]);
        $row('material', ['c_Name' => 'مادة', 'c_Price' => '2', 'c_Amount' => '3'], ['MicroProject_ID' => $project]);
        $power = $catalog('powersource');
        $row('microproject_powersource', [], ['MicroProject_ID' => $project, 'PowerSource_ID' => $power]);

        $family = $row('family', ['c_F_Number' => 'F-1']);
        $row('person_family', [], ['Family_ID' => $family, 'Person_ID' => $person, 'P_Provider_ID' => $person, 'Relation_ID' => $catalog('relation', 'مستفيد')]);
        $member = $row('person', ['c_P_FirstName' => 'Member', 'c_P_LastName' => 'Last', 'c_P_FatherName' => 'Father', 'c_P_DOB' => '2010-01-01', 'c_P_Sex' => 'ذكر', 'c_P_NationalNumber' => '000000002'], ['P_State_ID' => $catalog('personstate')]);
        $row('person_family', ['c_HasWork' => 9], ['Family_ID' => $family, 'Person_ID' => $member, 'P_Provider_ID' => $person, 'Relation_ID' => $catalog('relation', 'ابن'), 'EducationLevel_ID' => $level, 'Education_ID' => $education]);

        $row('person_loss', ['c_Year' => '2012'], ['Person_ID' => $person, 'Loss_ID' => $catalog('loss')]);
        $row('person_illness', ['c_Has_MedicalAid' => 0], ['Person_ID' => $person, 'Illness_ID' => $catalog('illness')]);
        $row('person_asset', ['c_Year' => '2018'], ['Person_ID' => $person, 'Asset_ID' => $catalog('asset'), 'Asset_Type_ID' => $homeType, 'Asset_Benefits_ID' => $catalog('assetbenefits')]);
        $aidSource = $catalog('aidsource');
        $row('person_aidsource', ['c_DateYear' => '2021'], ['Person_ID' => $person, 'AidSource_ID' => $aidSource]);
        $row('person_help', [], ['Person_ID' => $person, 'AidSource_ID' => $aidSource, 'Aid_ID' => $catalog('aid'), 'Aidperiod_ID' => $catalog('aidperiod')]);
        $row('person_powersource', ['c_Note' => 'شرح', 'c_Responsability' => 'جهة'], ['Person_ID' => $person, 'PowerSource_ID' => $power]);
    }
}
