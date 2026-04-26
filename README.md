# 🇵🇭 innonazarene/calendar-activities

A Laravel package that returns **Philippine public holidays** (from the Official Gazette) **and Activities** (from your database) in a single API response

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

> `activities_list` is **an array provided manually by the developer**. You handle your own database queries and inject the results here!

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



---

## Usage

### Facade

```php
use CalendarActivities\Facades\CalendarActivities;

// 1. Manually query your own database, format it however you want
$myActivities = [
    [
        'id' => 1,
        'title' => 'Personal Vacation',
        'date' => '2026-05-10',
        'type' => 'personal'
    ]
];

// 2. Combined holidays + your manual activities for a month
$data = CalendarActivities::forMonth(year: 2026, month: 5, activitiesList: $myActivities);
// [
//   'ph_holidays'     => [ ... ],
//   'activities_list' => [ ... ]
// ]

// Combined for a full year
$data = CalendarActivities::forYear(year: 2026, activitiesList: $myActivities);

// Holidays only
$holidays = CalendarActivities::holidays(2026);

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
        // Inject your custom activities here!
        $myActivities = [];
        return $this->calendar->forMonth(2026, 4, $myActivities);
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
    Route::get('/check',      [CalendarController::class, 'check']);      // is date a holiday?
});
```

See [`examples/CalendarController.php`](examples/CalendarController.php) for the full controller.

---




---

## Holiday types

| `types` value | Meaning | Pay rule |
|---|---|---|
| `"Public"` | Regular Holiday | 100% pay even if absent |
| `"Optional"` | Special Non-Working Day | No work, no pay |
| `"WorkDay"` | Special Working Day | Normal working day |

---

## License

MIT
