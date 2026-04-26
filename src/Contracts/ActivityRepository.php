<?php

namespace CalendarActivities\Contracts;

use Illuminate\Support\Collection;

interface ActivityRepository
{
    /**
     * Return activities for the given year/month.
     * Pass month = 0 to get the full year.
     *
     * @return Collection<int, \CalendarActivities\Data\Activity>
     */
    public function get(int $year, int $month = 0): Collection;
}
