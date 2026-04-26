<?php

namespace CalendarActivities\Contracts;

use Illuminate\Support\Collection;

interface HolidayProvider
{
    /** @return Collection<int, \CalendarActivities\Data\Holiday> */
    public function get(int $year): Collection;
}
