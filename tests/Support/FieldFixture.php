<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\WebAccount;
use Illuminate\Support\Facades\DB;

/** Invented field-operations data on top of the application fixture. */
final class FieldFixture
{
    public const CAPABILITIES = ['reports.export', 'special_cases.view', 'special_cases.manage', 'visits.view', 'visits.manage', 'special_cases.delete', 'visits.delete', 'me_visits.view', 'me_visits.manage', 'me_visits.delete', 'profession_meetings.view', 'profession_meetings.manage', 'profession_meetings.delete'];

    public static function grant(WebAccount $account, array $cities = [1, 2], array $capabilities = self::CAPABILITIES): void
    {
        foreach ($cities as $city) {
            foreach ($capabilities as $capability) {
                DB::table('account_city_capabilities')->insert(['account_id' => $account->id, 'city_id' => $city, 'capability' => $capability, 'decision_locator' => 'synthetic:p52', 'granted_at' => now('UTC')]);
            }
        }
    }

    /** Special-case review types of a city; names sort so that the phone review comes first. */
    public static function specialTypes(int $city): array
    {
        return [
            'phone' => ApplicationFixture::row('specialcasetype', $city, ['c_ID' => 81, 'c_Name' => 'أ هاتفية', 'c_Name_en' => 'Phone']),
            'visit' => ApplicationFixture::row('specialcasetype', $city, ['c_ID' => 82, 'c_Name' => 'ب زيارة', 'c_Name_en' => 'Visit']),
        ];
    }

    /** The opening and closing tasks (legacy ids 25 and 31) and a street of the city. */
    public static function visitCatalog(int $city): array
    {
        $tasks = [];
        foreach ([25 => 'Opening visit', 31 => 'Closing visit'] as $raw => $name) {
            $tasks[$raw] = ApplicationFixture::row('task', $city, ['c_T_ID' => $raw, 'c_T_Name' => $name, 'c_T_State' => 0, 'c_T_Type' => 'f']);
        }

        return ['tasks' => $tasks, 'street' => ApplicationFixture::row('street', $city, ['c_ID' => 5, 'c_Name' => 'Synthetic street'])];
    }

    /** Grants the administrator-only capability that allows deleting and restoring imported records. */
    public static function grantImportedDelete(WebAccount $account, array $cities = [1, 2]): void
    {
        self::grant($account, $cities, ['imported.delete']);
    }

    /** Makes a fixture row look imported: legacy identity with a published synthetic origin, so it stays visible. */
    public static function markImported(int $id, string $table, int $city = 1): void
    {
        $tableId = (int) DB::table($table)->where('id', $id)->value('source_table_id');
        DB::table('operational_entities')->where('id', $id)->update(['identity_kind' => 'legacy_primary']);
        DB::table('source_records')->insert(['id' => 5000 + $id, 'source_id' => $city, 'table_id' => $tableId, 'snapshot_id' => 10]);
        DB::table('operational_origins')->insert(['source_record_id' => 5000 + $id, 'source_id' => $city, 'source_table_id' => $tableId, 'snapshot_id' => 10, 'entity_id' => $id]);
        if (! DB::table('source_publications')->where('source_id', $city)->where('snapshot_id', 10)->exists()) {
            DB::table('source_publications')->insert(['source_id' => $city, 'snapshot_id' => 10, 'publication_kind' => 'synthetic']);
        }
    }

    /** Assigns both visit tasks to a project, not done. */
    public static function assignVisitTasks(int $project, array $catalog, int $city = 1): void
    {
        foreach ($catalog['tasks'] as $task) {
            ApplicationFixture::row('task_microproject', $city, ['c_State' => 0, 'c_Date' => '2026-10-19 00:00:00'], ['MicroProject_ID' => $project, 'Task_ID' => $task]);
        }
    }

    /**
     * M&E catalog: tasks 26 and 27, two "other project" questions (marks 2/5 and 1/3), one vehicle question and two active profit ranges
     * (0–100 → 1, 101–1,000,000 → 4).
     *
     * @return array{tasks: array<int, int>, questions: array<int, array{id: int, answers: array<string, int>}>, vehicle: array{id: int, answers: array<string, int>}}
     */
    public static function meCatalog(int $city): array
    {
        $tasks = [];
        foreach ([26, 27] as $raw) {
            $tasks[$raw] = ApplicationFixture::row('task', $city, ['c_T_ID' => $raw, 'c_T_Name' => 'M&E visit '.($raw - 25), 'c_T_State' => 0, 'c_T_Type' => 'f']);
        }
        $question = function (int $legacy, string $name, string $type, array $marks) use ($city) {
            $id = ApplicationFixture::row('mequestion', $city, ['c_ID' => $legacy, 'c_Name' => $name, 'c_Type' => $type]);
            $answers = [];
            $n = 0;
            foreach ($marks as $label => $mark) {
                $answers[$label] = ApplicationFixture::row('meanswer', $city, ['c_ID' => $legacy * 10 + ++$n, 'c_Name' => $label, 'c_Mark' => $mark], ['Question_ID' => $id]);
            }

            return ['id' => $id, 'answers' => $answers];
        };
        $questions = [$question(19, 'Is the work regular?', 'o', ['No' => 2, 'Yes' => 5]), $question(20, 'Is the income enough?', 'o', ['No' => 1, 'Yes' => 3])];
        $vehicle = $question(1, 'Is the vehicle in use?', 'v', ['No' => 0, 'Yes' => 6]);
        ApplicationFixture::row('meprofitmarks', $city, ['c_ID' => 1, 'c_IsActive' => 1, 'c_Min' => 0, 'c_Max' => 100, 'c_Mark' => 1]);
        ApplicationFixture::row('meprofitmarks', $city, ['c_ID' => 2, 'c_IsActive' => 1, 'c_Min' => 101, 'c_Max' => 1000000, 'c_Mark' => 4]);
        ApplicationFixture::row('meprofitmarks', $city, ['c_ID' => 3, 'c_IsActive' => 0, 'c_Min' => 0, 'c_Max' => 1000000, 'c_Mark' => 99]);

        return ['tasks' => $tasks, 'questions' => $questions, 'vehicle' => $vehicle];
    }

    /**
     * A small profession-meeting survey catalog: two axes and nine questions covering every answer type.
     *
     * @return array{sections: array<int, int>, questions: array<string, int>, options: array<string, array<int, int>>}
     */
    public static function meetingCatalog(int $city): array
    {
        $sections = [1 => ApplicationFixture::row('profession_section', $city, ['c_ID' => 1, 'c_Name_Ar' => 'المحور الأول', 'c_Sort_Order' => 1]),
            2 => ApplicationFixture::row('profession_section', $city, ['c_ID' => 2, 'c_Name_Ar' => 'المحور الثاني', 'c_Sort_Order' => 2])];
        $question = function (string $number, int $section, string $type, string $text) use ($city, $sections) {
            return ApplicationFixture::row('profession_question', $city, ['c_ID' => 100 + (int) $number, 'c_Number' => $number, 'c_Text_Ar' => $text, 'c_Answer_Type' => $type, 'c_Is_Active' => 1, 'c_Sort_Order' => (int) $number], ['Section_ID' => $sections[$section]]);
        };
        $questions = [
            '1' => $question('1', 1, 'option_number', 'How did you learn the trade?'),
            '4' => $question('4', 1, 'multi_option', 'Who are the customers?'),
            '5' => $question('5', 1, 'text', 'Most requested services?'),
            '6' => $question('6', 1, 'competition', 'Competition level?'),
            '8' => $question('8', 1, 'season_table', 'Best seasons?'),
            '10' => $question('10', 2, 'brand_table', 'Best brands?'),
            '12' => $question('12', 2, 'equipment_table', 'Basic equipment?'),
            '16' => $question('16', 2, 'challenge_table', 'Problems?'),
            '19' => $question('19', 2, 'participant_table', 'Who has training content?'),
        ];
        $option = function (string $number, int $order, string $text, int $other = 0, int $details = 0) use ($city, $questions) {
            return ApplicationFixture::row('profession_option', $city, ['c_ID' => (int) $number * 100 + $order, 'c_Text_Ar' => $text, 'c_Is_Other' => $other, 'c_Has_Details' => $details, 'c_Sort_Order' => $order], ['Question_ID' => $questions[$number]]);
        };
        $options = [
            '1' => [1 => $option('1', 1, 'Courses'), 2 => $option('1', 2, 'Family trade'), 3 => $option('1', 3, 'Apprentice')],
            '4' => [1 => $option('4', 1, 'Families'), 2 => $option('4', 2, 'Shops'), 3 => $option('4', 3, 'Other', 1, 1)],
        ];

        return ['sections' => $sections, 'questions' => $questions, 'options' => $options];
    }
}
