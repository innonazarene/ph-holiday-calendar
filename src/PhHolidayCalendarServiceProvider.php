<?php

namespace PhHolidayCalendar;

use Illuminate\Support\ServiceProvider;

class PhHolidayCalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ph-holiday-calendar.php', 'ph-holiday-calendar');

        $this->app->singleton(PhHolidayCalendar::class, function ($app) {
            return new PhHolidayCalendar(
                config: config('ph-holiday-calendar', []),
            );
        });

        $this->app->alias(PhHolidayCalendar::class, 'ph-holiday-calendar');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/ph-holiday-calendar.php' => config_path('ph-holiday-calendar.php'),
            ], 'ph-holiday-calendar-config');

            // No migrations published
        }
    }
}
