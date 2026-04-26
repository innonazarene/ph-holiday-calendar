<?php

namespace CalendarActivities;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use CalendarActivities\Contracts\ActivityRepository;
use CalendarActivities\Contracts\HolidayProvider;
use CalendarActivities\Data\Activity;
use CalendarActivities\Data\Holiday;
use CalendarActivities\Models\DatabaseActivityRepository;
use CalendarActivities\Scrapers\FallbackHolidayProvider;
use CalendarActivities\Scrapers\OfficialGazetteScraper;

/**
 * CalendarActivities — main service.
 *
 * Combines:
 *   1. ph_holidays     → scraped from Official Gazette, with fallback
 *   2. activities_list → from your existing DB table (blank/empty until data exists)
 *
 * Response shape:
 *
 *   {
 *     "ph_holidays": [
 *       { "date": "2026-01-01", "localName": "Bagong Taon", "name": "New Year's Day",
 *         "countryCode": "PH", "fixed": true, "global": true, "types": ["Public"] }
 *     ],
 *     "activities_list": []   ← empty until your table has rows
 *   }
 */
class CalendarActivities
{
    private HolidayProvider    $holidays;
    private ActivityRepository $activities;
    private array              $config;

    public function __construct(
        ?HolidayProvider    $holidays   = null,
        ?ActivityRepository $activities = null,
        array               $config     = [],
    ) {
        $this->config     = array_merge(config('calendar-activities', []), $config);
        $this->holidays   = $holidays   ?? $this->makeHolidayProvider();
        $this->activities = $activities ?? new DatabaseActivityRepository();
    }

    // ── Holidays ───────────────────────────────────────────────────────────

    /**
     * @return Collection<int, Holiday>
     */
    public function holidays(int $year = 0): Collection
    {
        $year = $year ?: (int) now()->year;

        if ($this->cacheEnabled()) {
            return Cache::remember(
                $this->holidayCacheKey($year),
                $this->cacheTtl(),
                fn () => $this->resolveHolidays($year)
            );
        }

        return $this->resolveHolidays($year);
    }

    public function isHoliday(string|\DateTimeInterface $date): bool
    {
        $iso  = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date;
        $year = (int) substr($iso, 0, 4);
        return $this->holidays($year)->contains(fn (Holiday $h) => $h->date === $iso);
    }

    public function holidayOn(string|\DateTimeInterface $date): ?Holiday
    {
        $iso  = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date;
        $year = (int) substr($iso, 0, 4);
        return $this->holidays($year)->first(fn (Holiday $h) => $h->date === $iso);
    }

    // ── Activities ─────────────────────────────────────────────────────────

    /**
     * @return Collection<int, Activity>
     */
    public function activities(int $year = 0, int $month = 0): Collection
    {
        $year = $year ?: (int) now()->year;
        return $this->activities->get($year, $month);
    }

    // ── Combined (what your API endpoint returns) ──────────────────────────

    /**
     * Returns both holidays and activities for a given year/month.
     *
     * [
     *   'holidays'   => [...],
     *   'activities' => [...],
     * ]
     */
    public function forMonth(int $year = 0, int $month = 0): array
    {
        $year  = $year  ?: (int) now()->year;
        $month = $month ?: (int) now()->month;

        return [
            'ph_holidays'     => $this->holidays($year)
                                      ->filter(fn (Holiday $h) => (int) date('n', strtotime($h->date)) === $month)
                                      ->map(fn (Holiday $h) => $h->toArray())
                                      ->values()
                                      ->all(),
            'activities_list' => $this->activities($year, $month)
                                      ->map(fn (Activity $a) => $a->toArray())
                                      ->values()
                                      ->all(),
        ];
    }

    /**
     * Full year — all holidays + all activities.
     */
    public function forYear(int $year = 0): array
    {
        $year = $year ?: (int) now()->year;

        return [
            'ph_holidays'     => $this->holidays($year)
                                      ->map(fn (Holiday $h) => $h->toArray())
                                      ->values()
                                      ->all(),
            'activities_list' => $this->activities($year)
                                      ->map(fn (Activity $a) => $a->toArray())
                                      ->values()
                                      ->all(),
        ];
    }

    public function clearHolidayCache(int $year = 0): void
    {
        Cache::forget($this->holidayCacheKey($year ?: (int) now()->year));
    }

    // ── Internals ──────────────────────────────────────────────────────────

    private function resolveHolidays(int $year): Collection
    {
        try {
            $primary = new OfficialGazetteScraper();
            $result  = $primary->get($year);

            if ($result->count() < 5) {
                throw new \RuntimeException('Too few holidays parsed — layout may have changed.');
            }

            return $result;
        } catch (\Throwable $e) {
            logger()->warning('[CalendarActivities] Official Gazette scrape failed, using fallback.', [
                'year'  => $year,
                'error' => $e->getMessage(),
            ]);

            return (new FallbackHolidayProvider())->get($year);
        }
    }

    private function makeHolidayProvider(): HolidayProvider
    {
        return new OfficialGazetteScraper();
    }

    private function cacheEnabled(): bool
    {
        return (bool) ($this->config['cache']['enabled'] ?? true);
    }

    private function cacheTtl(): int
    {
        return (int) ($this->config['cache']['ttl'] ?? 86400);
    }

    private function holidayCacheKey(int $year): string
    {
        $prefix = $this->config['cache']['prefix'] ?? 'cal_activities';
        return "{$prefix}_holidays_{$year}";
    }
}
