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
