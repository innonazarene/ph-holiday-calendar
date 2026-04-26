<?php

namespace CalendarActivities\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use CalendarActivities\Contracts\ActivityRepository;
use CalendarActivities\Data\Activity;

/**
 * DatabaseActivityRepository
 *
 * Reads activities from your HR database table.
 * The table name and date column are configurable in calendar-activities.php.
 *
 * Schema is intentionally flexible — whatever columns your table has
 * will be returned. Unknown columns flow into Activity::$meta.
 *
 * To customise the query (e.g. add joins, scopes, filters), swap this
 * class out by binding your own ActivityRepository in AppServiceProvider:
 *
 *   $this->app->bind(
 *       \CalendarActivities\Contracts\ActivityRepository::class,
 *       \App\Repositories\MyActivityRepository::class,
 *   );
 */
class DatabaseActivityRepository implements ActivityRepository
{
    private string $table;
    private string $dateColumn;
    private string $connection;

    public function __construct()
    {
        $config           = config('calendar-activities', []);
        $this->table      = $config['activities']['table']       ?? 'activities';
        $this->dateColumn = $config['activities']['date_column']  ?? 'date';
        $this->connection = $config['activities']['connection']   ?? config('database.default');
    }

    /**
     * @return Collection<int, Activity>
     */
    public function get(int $year, int $month = 0): Collection
    {
        $query = DB::connection($this->connection)
                   ->table($this->table)
                   ->whereYear($this->dateColumn, $year);

        if ($month > 0) {
            $query->whereMonth($this->dateColumn, $month);
        }

        return $query
            ->orderBy($this->dateColumn)
            ->get()
            ->map(fn ($row) => Activity::fromRow($row));
    }
}
