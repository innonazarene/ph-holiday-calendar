<?php

namespace CalendarActivities\Data;

/**
 * Activity DTO
 *
 * Intentionally flexible — all fields beyond `id`, `title`, and `date`
 * are optional so this works with whatever columns your HR table has.
 *
 * The `meta` bag catches any extra columns the query returns,
 * so you never lose data even if your schema evolves.
 *
 * Matches the TypeScript Activity interface in CalendarProvider.tsx.
 */
class Activity
{
    public function __construct(
        public readonly string|int  $id,
        public readonly string      $title,
        public readonly string      $date,          // "2026-04-10"
        public readonly string      $type,          // "institutional" | "departmental" | "personal"
        public readonly ?string     $description    = null,
        public readonly ?string     $end_date       = null,
        public readonly ?string     $department     = null,
        public readonly ?string     $created_by     = null,
        public readonly ?string     $color          = null,
        public readonly bool        $all_day        = true,
        public readonly ?string     $start_time     = null,  // "08:00"
        public readonly ?string     $end_time       = null,  // "17:00"
        public readonly array       $meta           = [],    // any extra columns
    ) {}

    /**
     * Hydrate from a raw DB row (stdClass or array).
     * Unknown columns are stuffed into `meta` so nothing is lost.
     */
    public static function fromRow(object|array $row): self
    {
        $r = is_object($row) ? (array) $row : $row;

        $known = [
            'id', 'title', 'date', 'type', 'description',
            'end_date', 'department', 'created_by', 'color',
            'all_day', 'start_time', 'end_time',
        ];

        $meta = array_diff_key($r, array_flip($known));

        return new self(
            id:          $r['id']          ?? 0,
            title:       $r['title']       ?? '(untitled)',
            date:        $r['date']        ?? '',
            type:        $r['type']        ?? 'institutional',
            description: $r['description'] ?? null,
            end_date:    $r['end_date']    ?? null,
            department:  $r['department']  ?? null,
            created_by:  $r['created_by']  ?? null,
            color:       $r['color']       ?? null,
            all_day:     (bool) ($r['all_day'] ?? true),
            start_time:  $r['start_time']  ?? null,
            end_time:    $r['end_time']    ?? null,
            meta:        $meta,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id'          => $this->id,
            'title'       => $this->title,
            'date'        => $this->date,
            'type'        => $this->type,
            'description' => $this->description,
            'end_date'    => $this->end_date,
            'department'  => $this->department,
            'created_by'  => $this->created_by,
            'color'       => $this->color,
            'all_day'     => $this->all_day,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            ...$this->meta,
        ], fn ($v) => $v !== null);
    }
}
