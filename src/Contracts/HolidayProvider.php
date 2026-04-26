<?php

namespace PhHolidayCalendar\Contracts;

use Illuminate\Support\Collection;

interface HolidayProvider
{
    /** @return Collection<int, \PhHolidayCalendar\Data\Holiday> */
    public function get(int $year): Collection;
}
