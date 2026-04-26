<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhHolidayCalendar\Facades\PhHolidayCalendar;

/**
 * CalendarController
 *
 * Exposes the combined holidays + activities endpoint
 * consumed by CalendarProvider.tsx.
 *
 * Add to routes/api.php:
 *
 *   use App\Http\Controllers\Api\CalendarController;
 *
 *   Route::prefix('calendar')->group(function () {
 *       Route::get('/',              [CalendarController::class, 'index']);
 *       Route::get('/holidays',      [CalendarController::class, 'holidays']);
 *       Route::get('/activities',    [CalendarController::class, 'activities']);
 *       Route::get('/check',         [CalendarController::class, 'check']);
 *   });
 */
class CalendarController
{
    /**
     * GET /api/calendar?year=2026&month=4
     *
     * Returns:
     * {
     *   "ph_holidays":   [ { date, localName, name, countryCode, fixed, global, types } ],
     *   "activities_list": [ { id, title, date, type, ...yourColumns } ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);

        return response()->json(
            PhHolidayCalendar::forMonth($year, $month)
        );
    }

    /**
     * GET /api/calendar/holidays?year=2026
     *
     * Returns only the holidays array — same shape as Nager.Date API.
     */
    public function holidays(Request $request): JsonResponse
    {
        $year = (int) $request->query('year', now()->year);

        return response()->json(
            PhHolidayCalendar::holidays($year)
                ->map(fn ($h) => $h->toArray())
                ->values()
        );
    }

    /**
     * GET /api/calendar/activities?year=2026&month=4
     *
     * Returns only the activities array from your DB table.
     */
    public function activities(Request $request): JsonResponse
    {
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', 0);

        return response()->json(
            PhHolidayCalendar::activities($year, $month)
                ->map(fn ($a) => $a->toArray())
                ->values()
        );
    }

    /**
     * GET /api/calendar/check?date=2026-12-25
     *
     * Returns:
     * { "isHoliday": true, "holiday": { ... } }
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);

        $date    = $request->query('date');
        $holiday = PhHolidayCalendar::holidayOn($date);

        return response()->json([
            'isHoliday' => $holiday !== null,
            'holiday'   => $holiday?->toArray(),
        ]);
    }
}
