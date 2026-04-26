<?php

namespace PhHolidayCalendar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection holidays(int $year = 0)
 * @method static array forMonth(int $year = 0, int $month = 0, array $activitiesList = [])
 * @method static array forYear(int $year = 0, array $activitiesList = [])
 * @method static bool isHoliday(string|\DateTimeInterface $date)
 * @method static \PhHolidayCalendar\Data\Holiday|null holidayOn(string|\DateTimeInterface $date)
 * @method static void clearHolidayCache(int $year = 0)
 *
 * @see \PhHolidayCalendar\PhHolidayCalendar
 */
class PhHolidayCalendar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ph-holiday-calendar';
    }
}
