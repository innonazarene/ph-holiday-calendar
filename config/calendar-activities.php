<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Holiday Source
    |--------------------------------------------------------------------------
    |
    | Primary: Official Gazette of the Republic of the Philippines
    |   https://www.officialgazette.gov.ph/nationwide-holidays/{year}/
    |
    | On failure the package falls back to the hardcoded proclamation dataset.
    |
    */
    'source_url'   => 'https://www.officialgazette.gov.ph/nationwide-holidays/{year}/',
    'timeout'      => 15,
    'use_fallback' => true,

    /*
    |--------------------------------------------------------------------------
    | Activities Database
    |--------------------------------------------------------------------------
    |
    | Point this at your HR activities table.
    |
    | table       — the table name
    | date_column — the column used to filter by year/month
    | connection  — leave null to use the default DB connection
    |
    | The package reads whatever columns exist in the table and maps them
    | to the Activity DTO. No migration is required — it works with your
    | existing schema out of the box.
    |
    */
    'activities' => [
        'table'       => env('CALENDAR_ACTIVITIES_TABLE', 'activities'),
        'date_column' => env('CALENDAR_ACTIVITIES_DATE_COL', 'date'),
        'connection'  => env('CALENDAR_ACTIVITIES_CONNECTION', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Holidays are cached (activities are NOT cached — always fresh from DB).
    |
    */
    'cache' => [
        'enabled' => env('CALENDAR_ACTIVITIES_CACHE', true),
        'ttl'     => 86400,         // 24 hours
        'prefix'  => 'cal_activities',
    ],

];
