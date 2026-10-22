<?php

declare(strict_types=1);

namespace App\Services\Staff;

/**
 * The columns of the desktop task board (`TaskBoard_Form.bind_f_tasks` / `bind_a_tasks`) as legacy task ids with the desktop's headers.
 * The headers are only a fallback: the task's own name from the city's catalog is shown when the catalog has it.
 */
final class TaskBoardColumns
{
    /** Financed-project board: task id => header. */
    public const FUNDED = [
        3 => 'القصة انكليزي', 4 => 'تدقيق القصة', 7 => 'صورة شخصية', 11 => 'توقيع العقود', 13 => 'طلب المشتريات', 15 => 'تصوير المشتريات', 16 => 'مشتريات',
        17 => 'تسليم كامل المبلغ و تحديد الأقساط', 19 => 'تصوير بعد افتتاح المشروع', 21 => 'Map Location', 20 => 'Web', 25 => 'Opening Visit', 47 => 'تصوير أثناء العمل',
        26 => 'M1', 27 => 'M2', 28 => 'M3', 29 => 'MFinal', 31 => 'Clearance', 48 => 'تصوير الزيارة الختامية', 36 => 'Video Captured', 37 => 'Video Edited', 38 => 'Video Uploaded',
    ];

    /** Application-stage board. */
    public const APPLICATION = [
        39 => 'الطائفة', 40 => 'المفتاحيين', 41 => 'أشخاص مهمين', 42 => 'تحديد موعد الزيارة', 43 => 'زيارة المنزل', 44 => 'زيارة المحل', 45 => 'اجتماع الفريق', 46 => 'اجتماع اللجنة',
    ];

    /** Legacy state ids listed on the financed-project board: Accepted, Financed, Finished, Canceled, Closed. */
    public const FUNDED_STATES = [1, 4, 5, 7, 8];

    /** The task whose completion tells the communication staff that purchases are finished (header "مشتريات"). */
    public const PURCHASES = 16;

    public static function for(string $kind): array
    {
        return $kind === 'a' ? self::APPLICATION : self::FUNDED;
    }
}
