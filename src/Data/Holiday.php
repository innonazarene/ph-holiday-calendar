<?php

namespace PhHolidayCalendar\Data;

/**
 * Holiday DTO — matches the TypeScript Holiday interface exactly.
 *
 * types:
 *   "Public"   → Regular Holiday       (paid even if absent)
 *   "Optional" → Special Non-Working   (no work, no pay)
 *   "WorkDay"  → Special Working Day   (normal working day)
 */
class Holiday
{
    public function __construct(
        public readonly string $date,        // "2026-01-01"
        public readonly string $localName,   // Filipino name
        public readonly string $name,        // English name
        public readonly string $countryCode, // "PH"
        public readonly bool   $fixed,
        public readonly bool   $global,
        public readonly array  $types,       // ["Public"] | ["Optional"] | ["WorkDay"]
    ) {}

    public function toArray(): array
    {
        return [
            'date'        => $this->date,
            'localName'   => $this->localName,
            'name'        => $this->name,
            'countryCode' => $this->countryCode,
            'fixed'       => $this->fixed,
            'global'      => $this->global,
            'types'       => $this->types,
        ];
    }

    public function isRegular(): bool
    {
        return in_array('Public', $this->types, true);
    }

    public function isSpecialNonWorking(): bool
    {
        return in_array('Optional', $this->types, true);
    }

    public function isSpecialWorkingDay(): bool
    {
        return in_array('WorkDay', $this->types, true);
    }
}
