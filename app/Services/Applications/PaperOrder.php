<?php

declare(strict_types=1);

namespace App\Services\Applications;

/**
 * The paper form's order. The desktop Application_New_Form lays its controls out by hand in the order of the printed
 * application the data-entry team copies from; the web's field metadata comes from the schema manifest in column order.
 * This map restores the paper order for every form kind, says which fields the desktop hides on the preliminary form
 * (`ApplicationState == "zero"`, Application_New_Form.cs:564–596), which columns are internal, and how each field is
 * entered (yes/no, year, list). Keys are `c_` columns or relationship keys; keys absent from a kind's metadata are ignored.
 */
final class PaperOrder
{
    /** Ordered groups per kind: group name => keys in paper order (right to left, then the next row). */
    private const GROUPS = [
        'application' => [
            // استمارة المشروع (First_tableLayoutPanel rows 4–8)
            'project' => ['SubCategory_ID', 'Career_ID', 'c_MP_Speciality', 'MP_Type_ID', 'c_MP_DateOfRequest', 'MP_SubType_ID', 'c_MP_Name', 'c_MP_RequestedAmount', 'MP_FundType_ID'],
            // معلومات خاصة بالمشروع (t20_tableLayoutPanel)
            'premises' => ['MP_PlaceType_ID', 'c_MP_PlaceAddress', 'c_MP_PlaceNote', 'c_MP_AmperType', 'c_MP_AmperNumber', 'c_MP_Agreement1', 'c_MP_Agreement2', 'c_MP_Agreement3', 'c_MP_Agreement4', 'c_MP_Agreement5'],
            // معلومات إضافية (t12_tableLayoutPanel)
            'extra' => ['c_MP_Profit_Description'],
            // Columns no desktop application form shows (set by other screens); kept editable but last.
            'other' => ['c_MP_IncomeKind', 'c_MP_IncomeKind_Note', 'c_MP_Note', 'MP_Street_ID'],
        ],
        // person_microproject: the story block and the has/has-not flags that sit above the desktop grids
        'people' => [
            'story' => ['c_P_Loss', 'c_P_Goal', 'c_P_GoalHow', 'c_P_OtherNotes'],
            'work' => ['c_P_HasWork', 'c_P_WorkYears', 'c_P_HasEducation', 'c_P_HasCourses'],
            'family' => ['c_P_IsLivingWithFamily', 'c_P_LiveWithFamily_Note', 'c_P_HasLoss', 'c_P_HasIllness', 'c_P_HasAssets'],
            'aid' => ['c_P_HasAidSource', 'c_P_AidSourceNote', 'c_P_HasDebts', 'c_P_DebtsAmount', 'c_P_DebtsNote', 'c_P_HasHelp'],
        ],
        // المعلومات الشخصية لمقدم الطلب (t3_tableLayoutPanel rows 3–21)
        'person' => [
            'identity' => ['c_P_FirstName', 'c_P_LastName', 'c_P_Sex', 'c_P_FullName_en', 'c_P_FatherName', 'c_P_MotherName', 'c_P_RegistrationPlace', 'c_P_NationalNumber', 'c_P_DOB', 'P_MaritalStatus_ID', 'P_Parish_ID', 'c_P_IntermidiarySide', 'P_Priest_ID', 'P_Priest2_ID'],
            'contact' => ['c_P_Mobile', 'c_P_HomeTel', 'c_P_Mobile2', 'Street_ID', 'c_P_HomeAddress', 'P_HomeType_ID', 'c_P_HomeState_Note'],
            'military' => ['c_P_MilitaryService', 'c_P_MilitaryService_Note', 'c_P_MilitaryPeriod', 'c_P_MilitaryService_Date', 'c_P_IsMilitaryInWar'],
            'other' => ['c_P_MaristesCourse', 'P_State_ID', 'c_P_WorkYears', 'c_P_Goal', 'c_P_GoalHow', 'c_P_OtherNotes'],
        ],
        'family' => ['main' => ['c_F_Number']],
        // أفراد العائلة grid columns
        'families' => ['main' => ['Relation_ID', 'EducationLevel_ID', 'Education_ID', 'c_HasWork', 'c_Work_Name']],
        // الخبرات العملية grid columns
        'workexp' => ['main' => ['Work_ID', 'W_WorkType_ID', 'c_W_Description', 'c_W_BeginDate', 'c_W_EndDate', 'c_W_PlaceState', 'c_W_Place', 'c_W_Contact', 'c_W_Salary', 'c_W_CauseOfLoseDescription', 'c_W_Status']],
        'education' => ['main' => ['Level_ID', 'Education_ID']],
        'course' => ['main' => ['SubCategory_ID', 'c_Name', 'c_Place', 'c_Donor', 'c_Date']],
        'loss' => ['main' => ['Loss_ID', 'c_Year', 'c_Note']],
        'illness' => ['main' => ['Illness_ID', 'c_Note', 'c_Has_MedicalAid', 'c_Donor', 'AidSource_ID', 'AidPeriod_ID', 'MedicalNeeds_ID']],
        'asset' => ['main' => ['Asset_ID', 'c_Year', 'Asset_Type_ID', 'c_Description', 'Asset_Benefits_ID', 'c_Note']],
        'aid' => ['main' => ['AidSource_ID', 'c_DateYear', 'c_Amount', 'c_ProjectType', 'c_Notes']],
        'help' => ['main' => ['AidSource_ID', 'Aid_ID', 'Aidperiod_ID', 'c_Notes']],
        'homepower' => ['main' => ['PowerSource_ID', 'c_Note', 'c_Cost', 'c_Responsability']],
        'powersource' => ['main' => ['PowerSource_ID', 'c_Note', 'c_Cost', 'c_Responsability']],
        'material' => ['main' => ['c_Name', 'c_Comments', 'c_Amount', 'c_Price', 'c_LocalContribution']],
        'risk' => ['main' => ['Risk_ID']],
        'agriculture' => ['main' => ['Question_ID', 'Answer_ID']],
        'rehab' => ['main' => ['c_PropertyOwnership', 'c_PropertyArea']],
        'rehab_answers' => ['main' => ['Question_ID', 'Answer_ID', 'c_Note', 'c_Number', 'c_Price', 'c_Result']],
    ];

    /** One key that identifies a kind from its metadata, for partials that receive fields without a kind name. */
    private const SIGNATURES = ['c_MP_Name' => 'application', 'c_P_Loss' => 'people', 'c_P_FirstName' => 'person', 'c_F_Number' => 'family', 'Relation_ID' => 'families', 'c_W_Description' => 'workexp', 'Level_ID' => 'education', 'c_Donor' => 'course', 'Loss_ID' => 'loss', 'Illness_ID' => 'illness', 'Asset_ID' => 'asset', 'c_DateYear' => 'aid', 'Aid_ID' => 'help', 'c_Responsability' => 'powersource', 'c_LocalContribution' => 'material', 'Risk_ID' => 'risk', 'c_PropertyArea' => 'rehab', 'c_Number' => 'rehab_answers', 'Question_ID' => 'agriculture'];

    /** Hidden on the desktop's preliminary form (Application_New_Form.cs:564–596). */
    private const PRELIMINARY_HIDDEN = [
        'application' => ['c_MP_RequestedAmount', 'MP_FundType_ID', 'MP_PlaceType_ID', 'c_MP_PlaceAddress', 'c_MP_PlaceNote', 'c_MP_AmperType', 'c_MP_AmperNumber', 'c_MP_Agreement1', 'c_MP_Agreement2', 'c_MP_Agreement3', 'c_MP_Agreement4', 'c_MP_Agreement5'],
        'person' => ['c_P_FullName_en', 'c_P_RegistrationPlace', 'P_Priest_ID', 'P_Priest2_ID', 'c_P_Mobile2', 'c_P_MilitaryService', 'c_P_MilitaryService_Note', 'c_P_MilitaryPeriod', 'c_P_MilitaryService_Date', 'c_P_IsMilitaryInWar'],
        'people' => ['c_P_Loss', 'c_P_Goal', 'c_P_GoalHow', 'c_P_OtherNotes', 'c_P_HasDebts', 'c_P_DebtsAmount', 'c_P_DebtsNote'],
    ];

    /** Columns the desktop computes or sets itself; never offered as inputs. */
    private const INTERNAL = [
        'application' => ['c_Partnership'],
        'person' => ['c_IsProjectOwner', 'c_P_NumAtHome', 'c_P_NumOfMembers'],
    ];

    /** Yes/no columns stored as 1/0 (desktop radio pairs). */
    public const FLAGS = ['c_MP_Agreement1', 'c_MP_Agreement2', 'c_MP_Agreement3', 'c_MP_Agreement4', 'c_MP_Agreement5', 'c_P_HasWork', 'c_P_HasEducation', 'c_P_HasCourses', 'c_P_IsLivingWithFamily', 'c_P_HasLoss', 'c_P_HasIllness', 'c_P_HasAssets', 'c_P_HasAidSource', 'c_P_HasDebts', 'c_P_HasHelp', 'c_P_IsMilitaryInWar', 'c_Has_MedicalAid', 'c_HasWork'];

    /** Columns entered as a four-digit year (desktop `P_DOB_textBox` MaxLength 4, `ParseExact "yyyy"`). */
    public const YEAR_DATES = ['c_P_DOB'];

    /** Desktop radio values of خدمة العلم (Application_New_Form.cs:4580–4590). */
    public const MILITARY_VALUES = ['وحيد', 'مسرح', 'عسكري', 'معفى/بدل', 'مؤجل'];

    /** c_MP_AmperType: 1 = أمبير, 0 = واط (Application_New_Form.cs:5378–5384). */
    public const AMPER_TYPES = ['1' => 'amper', '0' => 'watt'];

    /** Child list => [parent list key, child lookup table, parent column of that table]; the desktop filters the child by the parent (:1170–1275). */
    public const CASCADES = ['Career_ID' => ['SubCategory_ID', 'career', 'SubCategory_ID'], 'MP_SubType_ID' => ['MP_Type_ID', 'microprojectsubtype', 'Type_ID'], 'Answer_ID' => ['Question_ID', null, 'Question_ID']];

    /** Lookup tables the desktop sorts by name; every other list keeps its original id order (Application_New_Form.cs:832–845). */
    public const SORT_BY_NAME = ['subcategory', 'career', 'priest', 'street', 'work'];

    /** @return array<string, list<string>> */
    public static function groups(string $kind): array
    {
        return self::GROUPS[$kind] ?? [];
    }

    /** All keys of a kind in paper order. @return list<string> */
    public static function sequence(string $kind): array
    {
        return array_merge(...array_values(self::groups($kind)) ?: [[]]);
    }

    /** The kind whose signature key appears in the given metadata keys, or null. @param list<string> $keys */
    public static function kindOf(array $keys): ?string
    {
        foreach (self::SIGNATURES as $signature => $kind) {
            if (in_array($signature, $keys, true)) {
                return $kind;
            }
        }

        return null;
    }

    /**
     * Reorders metadata (fields or relations keyed by name) into paper order; unknown keys keep their position after
     * the known ones. @template T @param array<string, T> $metadata @return array<string, T>
     */
    public static function sort(string $kind, array $metadata): array
    {
        $known = array_intersect_key(array_replace(array_fill_keys(self::sequence($kind), null), $metadata), $metadata);

        return $known + $metadata;
    }

    /** @return list<string> */
    public static function preliminaryHidden(string $kind): array
    {
        return self::PRELIMINARY_HIDDEN[$kind] ?? [];
    }

    /** @return list<string> */
    public static function internal(string $kind): array
    {
        return self::INTERNAL[$kind] ?? [];
    }

    public static function isFlag(string $key): bool
    {
        return in_array($key, self::FLAGS, true);
    }
}
