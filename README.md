# 🇵🇭 innonazarene/calendar-activities

A Laravel package that returns **Philippine public holidays** (from the Official Gazette) **and HR activities** (from your database) in a single API response — ready to plug into your `CalendarProvider.tsx`.

---

## What it returns

```json
{
  "ph_holidays": [
    {
      "date": "2026-04-17",
      "localName": "Biyernes Santo",
      "name": "Good Friday",
      "countryCode": "PH",
      "fixed": false,
      "global": true,
      "types": ["Public"]
    }
  ],
  "activities_list": []
}
```

> `activities_list` is **empty `[]` by default** — it will populate once your existing table has rows. No migration needed.

---

## Installation

```bash
composer require innonazarene/calendar-activities
```

Auto-discovery registers the service provider and `CalendarActivities` facade.

### Publish config

```bash
php artisan vendor:publish --tag=calendar-activities-config
```

> No migration needed — the package reads your existing table as-is.

---

## Configuration (`config/calendar-activities.php`)

```php
'activities' => [
    'table'       => env('CALENDAR_ACTIVITIES_TABLE', 'activities'), // your table name
    'date_column' => env('CALENDAR_ACTIVITIES_DATE_COL', 'date'),    // column to filter by
    'connection'  => env('CALENDAR_ACTIVITIES_CONNECTION', null),     // null = default DB
],
```

Or via `.env`:

```env
CALENDAR_ACTIVITIES_TABLE=hr_activities
CALENDAR_ACTIVITIES_DATE_COL=activity_date
```

---

## Usage

### Facade

```php
use CalendarActivities\Facades\CalendarActivities;

// Combined holidays + activities for a month
$data = CalendarActivities::forMonth(year: 2026, month: 4);
// [
//   'ph_holidays'     => [ ... ],
//   'activities_list' => []       ← empty until your table has rows
// ]

// Combined for a full year
$data = CalendarActivities::forYear(2026);

// Holidays only
$holidays = CalendarActivities::holidays(2026);

// Activities only (from your DB)
$activities = CalendarActivities::activities(year: 2026, month: 4);

// Check a specific date
CalendarActivities::isHoliday('2026-12-25');      // true
CalendarActivities::holidayOn('2026-12-25');       // Holiday DTO or null
```

### Dependency injection

```php
use CalendarActivities\CalendarActivities;

class MyService
{
    public function __construct(private CalendarActivities $calendar) {}

    public function getCalendar(): array
    {
        return $this->calendar->forMonth(2026, 4);
    }
}
```

---

## API Routes

Add to `routes/api.php`:

```php
use App\Http\Controllers\Api\CalendarController;

Route::prefix('calendar')->group(function () {
    Route::get('/',           [CalendarController::class, 'index']);      // holidays + activities
    Route::get('/holidays',   [CalendarController::class, 'holidays']);   // holidays only
    Route::get('/activities', [CalendarController::class, 'activities']); // activities only
    Route::get('/check',      [CalendarController::class, 'check']);      // is date a holiday?
});
```

See [`examples/CalendarController.php`](examples/CalendarController.php) for the full controller.

---


## Customising the Activity query

By default, the package does `SELECT * FROM your_table WHERE YEAR(date) = ?`.

To add joins, scopes, or filters, bind your own repository in `AppServiceProvider`:

```php
use CalendarActivities\Contracts\ActivityRepository;
use App\Repositories\HrActivityRepository;

public function register(): void
{
    $this->app->bind(ActivityRepository::class, HrActivityRepository::class);
}
```

Your repository just needs to implement:

```php
public function get(int $year, int $month = 0): Collection; // Collection<Activity>
```

---

## Holiday types

| `types` value | Meaning | Pay rule |
|---|---|---|
| `"Public"` | Regular Holiday | 100% pay even if absent |
| `"Optional"` | Special Non-Working Day | No work, no pay |
| `"WorkDay"` | Special Working Day | Normal working day |

---

## License

MIT — [Tom Ramos Pedales](https://rustompedales-portfolio.vercel.app)
