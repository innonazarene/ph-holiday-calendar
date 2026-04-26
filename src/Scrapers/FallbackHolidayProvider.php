<?php

namespace PhHolidayCalendar\Scrapers;

use Illuminate\Support\Collection;
use PhHolidayCalendar\Contracts\HolidayProvider;
use PhHolidayCalendar\Data\Holiday;

/**
 * Hardcoded fallback — Proclamation No. 1006, s. 2025 (for 2026).
 * Used automatically when the Official Gazette cannot be scraped.
 */
class FallbackHolidayProvider implements HolidayProvider
{
    public function get(int $year): Collection
    {
        $data = match ($year) {
            2026 => $this->holidays2026(),
            2025 => $this->holidays2025(),
            default => [],
        };

        return collect($data)->map(fn (array $h) => new Holiday(
            date:        $h['date'],
            localName:   $h['localName'],
            name:        $h['name'],
            countryCode: 'PH',
            fixed:       $h['fixed'],
            global:      true,
            types:       $h['types'],
        ));
    }

    private function holidays2026(): array
    {
        return [
            // Regular Holidays
            ['date'=>'2026-01-01','localName'=>'Bagong Taon','name'=>"New Year's Day",'fixed'=>true,'types'=>['Public']],
            ['date'=>'2026-04-09','localName'=>'Araw ng Kagitingan','name'=>'Day of Valor','fixed'=>false,'types'=>['Public']],
            ['date'=>'2026-04-16','localName'=>'Huwebes Santo','name'=>'Maundy Thursday','fixed'=>false,'types'=>['Public']],
            ['date'=>'2026-04-17','localName'=>'Biyernes Santo','name'=>'Good Friday','fixed'=>false,'types'=>['Public']],
            ['date'=>'2026-05-01','localName'=>'Araw ng mga Manggagawa','name'=>'Labor Day','fixed'=>true,'types'=>['Public']],
            ['date'=>'2026-06-12','localName'=>'Araw ng Kalayaan','name'=>'Independence Day','fixed'=>true,'types'=>['Public']],
            ['date'=>'2026-08-25','localName'=>'Araw ng mga Bayani','name'=>'National Heroes Day','fixed'=>false,'types'=>['Public']],
            ['date'=>'2026-11-30','localName'=>'Araw ni Bonifacio','name'=>'Bonifacio Day','fixed'=>false,'types'=>['Public']],
            ['date'=>'2026-12-25','localName'=>'Pasko','name'=>'Christmas Day','fixed'=>true,'types'=>['Public']],
            ['date'=>'2026-12-30','localName'=>'Araw ni Rizal','name'=>'Rizal Day','fixed'=>true,'types'=>['Public']],
            // Special Non-Working Days
            ['date'=>'2026-02-17','localName'=>'Bagong Taon ng Tsino','name'=>'Chinese New Year','fixed'=>false,'types'=>['Optional']],
            ['date'=>'2026-04-18','localName'=>'Sabado de Gloria','name'=>'Black Saturday','fixed'=>false,'types'=>['Optional']],
            ['date'=>'2026-08-21','localName'=>'Araw ni Ninoy Aquino','name'=>'Ninoy Aquino Day','fixed'=>true,'types'=>['Optional']],
            ['date'=>'2026-11-01','localName'=>'Araw ng mga Patay','name'=>"All Saints' Day",'fixed'=>true,'types'=>['Optional']],
            ['date'=>'2026-11-02','localName'=>'Undas','name'=>"All Souls' Day",'fixed'=>true,'types'=>['Optional']],
            ['date'=>'2026-12-08','localName'=>'Kapistahan ng Immaculada Concepcion','name'=>'Feast of the Immaculate Conception of Mary','fixed'=>true,'types'=>['Optional']],
            ['date'=>'2026-12-24','localName'=>'Bisperas ng Pasko','name'=>'Christmas Eve','fixed'=>true,'types'=>['Optional']],
            ['date'=>'2026-12-31','localName'=>'Bisperas ng Bagong Taon','name'=>'Last Day of the Year','fixed'=>true,'types'=>['Optional']],
            // Special Working Day
            ['date'=>'2026-02-25','localName'=>'Anibersaryo ng EDSA People Power Revolution','name'=>'EDSA People Power Revolution Anniversary','fixed'=>true,'types'=>['WorkDay']],
        ];
    }

    private function holidays2025(): array
    {
        return [
            ['date'=>'2025-01-01','localName'=>'Bagong Taon','name'=>"New Year's Day",'fixed'=>true,'types'=>['Public']],
            ['date'=>'2025-04-09','localName'=>'Araw ng Kagitingan','name'=>'Day of Valor','fixed'=>false,'types'=>['Public']],
            ['date'=>'2025-04-17','localName'=>'Huwebes Santo','name'=>'Maundy Thursday','fixed'=>false,'types'=>['Public']],
            ['date'=>'2025-04-18','localName'=>'Biyernes Santo','name'=>'Good Friday','fixed'=>false,'types'=>['Public']],
            ['date'=>'2025-05-01','localName'=>'Araw ng mga Manggagawa','name'=>'Labor Day','fixed'=>true,'types'=>['Public']],
            ['date'=>'2025-06-12','localName'=>'Araw ng Kalayaan','name'=>'Independence Day','fixed'=>true,'types'=>['Public']],
            ['date'=>'2025-08-25','localName'=>'Araw ng mga Bayani','name'=>'National Heroes Day','fixed'=>false,'types'=>['Public']],
            ['date'=>'2025-11-30','localName'=>'Araw ni Bonifacio','name'=>'Bonifacio Day','fixed'=>false,'types'=>['Public']],
            ['date'=>'2025-12-25','localName'=>'Pasko','name'=>'Christmas Day','fixed'=>true,'types'=>['Public']],
            ['date'=>'2025-12-30','localName'=>'Araw ni Rizal','name'=>'Rizal Day','fixed'=>true,'types'=>['Public']],
            ['date'=>'2025-02-12','localName'=>'Bagong Taon ng Tsino','name'=>'Chinese New Year','fixed'=>false,'types'=>['Optional']],
            ['date'=>'2025-04-19','localName'=>'Sabado de Gloria','name'=>'Black Saturday','fixed'=>false,'types'=>['Optional']],
            ['date'=>'2025-08-21','localName'=>'Araw ni Ninoy Aquino','name'=>'Ninoy Aquino Day','fixed'=>true,'types'=>['Optional']],
            ['date'=>'2025-11-01','localName'=>'Araw ng mga Patay','name'=>"All Saints' Day",'fixed'=>true,'types'=>['Optional']],
            ['date'=>'2025-11-02','localName'=>'Undas','name'=>"All Souls' Day",'fixed'=>true,'types'=>['Optional']],
            ['date'=>'2025-12-08','localName'=>'Kapistahan ng Immaculada Concepcion','name'=>'Feast of the Immaculate Conception of Mary','fixed'=>true,'types'=>['Optional']],
            ['date'=>'2025-12-24','localName'=>'Bisperas ng Pasko','name'=>'Christmas Eve','fixed'=>true,'types'=>['Optional']],
            ['date'=>'2025-12-31','localName'=>'Bisperas ng Bagong Taon','name'=>'Last Day of the Year','fixed'=>true,'types'=>['Optional']],
        ];
    }
}
