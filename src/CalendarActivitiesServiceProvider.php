<?php

namespace CalendarActivities;

use Illuminate\Support\ServiceProvider;
use CalendarActivities\Contracts\ActivityRepository;
use CalendarActivities\Models\DatabaseActivityRepository;

class CalendarActivitiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/calendar-activities.php', 'calendar-activities');

        // Bind the activity repository — override in AppServiceProvider to swap your own
        $this->app->bind(ActivityRepository::class, DatabaseActivityRepository::class);

        $this->app->singleton(CalendarActivities::class, function ($app) {
            return new CalendarActivities(
                activities: $app->make(ActivityRepository::class),
                config:     config('calendar-activities', []),
            );
        });

        $this->app->alias(CalendarActivities::class, 'calendar-activities');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/calendar-activities.php' => config_path('calendar-activities.php'),
            ], 'calendar-activities-config');

            // No migrations published — package works with your existing schema.
        }
    }
}
