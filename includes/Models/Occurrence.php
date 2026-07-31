<?php

declare(strict_types=1);

namespace Dizzy\Events\Models;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

defined('ABSPATH') || exit;

/**
 * Immutable occurrence model.
 *
 * Represents a single event occurrence.
 *
 * @package Dizzy\Events\Models
 */
readonly class Occurrence
{
    public function __construct(
        public int $id,
        public int $eventId,
        public DateTimeImmutable $startDateTime,
        public ?DateTimeImmutable $endDateTime,
        public bool $allDay,
        public string $timezone,
        public string $status,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * Create an Occurrence from a database row.
     *
     * @throws InvalidArgumentException
     */
    public static function fromRow(object $row): self
    {
        $timezone = new DateTimeZone(
            $row->timezone ?: 'Europe/Amsterdam'
        );

        return new self(
            id: (int) $row->id,
            eventId: (int) $row->event_id,

            startDateTime: new DateTimeImmutable(
                $row->start_datetime,
                $timezone
            ),

            endDateTime: empty($row->end_datetime)
                ? null
                : new DateTimeImmutable(
                    $row->end_datetime,
                    $timezone
                ),

            allDay: (bool) $row->all_day,

            timezone: $row->timezone ?: 'Europe/Amsterdam',

            status: (string) $row->status,

            createdAt: new DateTimeImmutable(
                $row->created_at,
                $timezone
            ),

            updatedAt: new DateTimeImmutable(
                $row->updated_at,
                $timezone
            )
        );
    }

    /**
     * Convert model to array.
     *
     * Mainly used by REST API responses.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [

            'id' => $this->id,

            'event_id' => $this->eventId,

            'start_datetime' =>
                $this->startDateTime->format('Y-m-d H:i:s'),

            'end_datetime' =>
                $this->endDateTime?->format('Y-m-d H:i:s'),

            'all_day' => $this->allDay,

            'timezone' => $this->timezone,

            'status' => $this->status,

            'created_at' =>
                $this->createdAt->format('Y-m-d H:i:s'),

            'updated_at' =>
                $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check whether occurrence has ended.
     */
    public function hasEnded(): bool
    {
        if ($this->endDateTime === null) {
            return false;
        }

        return $this->endDateTime < new DateTimeImmutable(
            'now',
            new DateTimeZone($this->timezone)
        );
    }

    /**
     * Check whether occurrence is currently running.
     */
    public function isRunning(): bool
    {
        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone($this->timezone)
        );

        if ($this->startDateTime > $now) {
            return false;
        }

        if ($this->endDateTime === null) {
            return true;
        }

        return $this->endDateTime >= $now;
    }

    /**
     * Returns true when occurrence starts in the future.
     */
    public function isUpcoming(): bool
    {
        return $this->startDateTime >
            new DateTimeImmutable(
                'now',
                new DateTimeZone($this->timezone)
            );
    }
}