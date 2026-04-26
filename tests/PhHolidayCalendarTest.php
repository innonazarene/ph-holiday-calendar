<?php

namespace PhHolidayCalendar\Tests;

use PhHolidayCalendar\PhHolidayCalendar;
use PhHolidayCalendar\PhHolidayCalendarServiceProvider;
use PhHolidayCalendar\Data\Activity;
use PhHolidayCalendar\Data\Holiday;
use PhHolidayCalendar\Scrapers\FallbackHolidayProvider;
use Orchestra\Testbench\TestCase;

class PhHolidayCalendarTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [PhHolidayCalendarServiceProvider::class];
    }

    private function makeService(): PhHolidayCalendar
    {
        // Use fallback only so tests don't hit the internet or DB
        return new PhHolidayCalendar(
            holidays: new FallbackHolidayProvider(),
        );
    }

    public function test_holidays_returns_collection(): void
    {
        $holidays = $this->makeService()->holidays(2026);
        $this->assertNotEmpty($holidays);
        $this->assertInstanceOf(Holiday::class, $holidays->first());
    }

    public function test_christmas_is_regular_holiday(): void
    {
        $h = $this->makeService()->holidays(2026)
                  ->first(fn (Holiday $h) => $h->date === '2026-12-25');
        $this->assertNotNull($h);
        $this->assertTrue($h->isRegular());
        $this->assertSame('PH', $h->countryCode);
    }

    public function test_edsa_is_special_working_day(): void
    {
        $h = $this->makeService()->holidays(2026)
                  ->first(fn (Holiday $h) => $h->date === '2026-02-25');
        $this->assertNotNull($h);
        $this->assertTrue($h->isSpecialWorkingDay());
    }

    public function test_black_saturday_is_special_non_working(): void
    {
        $h = $this->makeService()->holidays(2026)
                  ->first(fn (Holiday $h) => $h->date === '2026-04-18');
        $this->assertNotNull($h);
        $this->assertTrue($h->isSpecialNonWorking());
    }

    public function test_is_holiday(): void
    {
        $svc = $this->makeService();
        $this->assertTrue($svc->isHoliday('2026-12-25'));
        $this->assertFalse($svc->isHoliday('2026-12-26'));
    }

    public function test_holiday_dto_matches_typescript_interface(): void
    {
        $arr  = $this->makeService()->holidays(2026)->first()->toArray();
        $keys = ['date', 'localName', 'name', 'countryCode', 'fixed', 'global', 'types'];
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $arr);
        }
    }

    public function test_activity_from_row_handles_unknown_columns(): void
    {
        $row = [
            'id'          => 1,
            'title'       => 'Team Building',
            'date'        => '2026-03-15',
            'type'        => 'departmental',
            'venue'       => 'Bataan',    // extra column
            'organizer'   => 'HR Dept',   // extra column
        ];

        $activity = Activity::fromRow($row);

        $this->assertSame('Team Building', $activity->title);
        $this->assertSame('departmental', $activity->type);
        $this->assertSame('Bataan', $activity->meta['venue']);
        $this->assertSame('HR Dept', $activity->meta['organizer']);
    }

    public function test_activity_to_array_includes_meta(): void
    {
        $row = ['id' => 1, 'title' => 'Test', 'date' => '2026-01-10', 'type' => 'personal', 'venue' => 'Manila'];
        $arr = Activity::fromRow($row)->toArray();
        $this->assertArrayHasKey('venue', $arr);
    }

    public function test_for_year_returns_both_keys(): void
    {
        $result = $this->makeService()->forYear(2026);
        $this->assertArrayHasKey('ph_holidays', $result);
        $this->assertArrayHasKey('activities_list', $result);
    }
}
