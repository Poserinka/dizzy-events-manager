<?php

declare(strict_types=1);

namespace Dizzy\Events\Models;

use DateTimeImmutable;
use DateTimeZone;
use Dizzy\Events\Contracts\HydratesFromRow;
use Dizzy\Events\Core\Config;
use InvalidArgumentException;

defined('ABSPATH') || exit;

/**
 * Immutable occurrence model.
 *
 * Represents a single occurrence of an event.
 *
 * @package Dizzy\Events\Models
 */
readonly class Occurrence implements HydratesFromRow
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
    public static function fromRow(object $row): static
    {
        $timezone = self::createTimezone(
            $row->timezone ?? Config::DEFAULT_TIMEZONE
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

            timezone: $timezone->getName(),

            status: (string) $row->status,

            createdAt: new DateTimeImmutable(
                $row->created_at,
                $timezone
            ),

            updatedAt: new DateTimeImmutable(
                $row->updated_at,
                $timezone
            ),
        );
    }

    /**
     * Convert the model to an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->eventId,
            'start_datetime' => $this->startDateTime->format('Y-m-d H:i:s'),
            'end_datetime' => $this->endDateTime?->format('Y-m-d H:i:s'),
            'all_day' => $this->allDay,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Determine whether the occurrence has ended.
     */
    public function hasEnded(): bool
    {
        if ($this->endDateTime === null) {
            return false;
        }

        return $this->endDateTime < $this->now();
    }

    /**
     * Determine whether the occurrence is currently running.
     */
    public function isRunning(): bool
    {
        $now = $this->now();

        if ($this->startDateTime > $now) {
            return false;
        }

        return $this->endDateTime === null
            || $this->endDateTime >= $now;
    }

    /**
     * Determine whether the occurrence starts in the future.
     */
    public function isUpcoming(): bool
    {
        return $this->startDateTime > $this->now();
    }

    /**
     * Create a timezone instance.
     */
    private static function createTimezone(string $timezone): DateTimeZone
    {
        return new DateTimeZone($timezone);
    }

    /**
     * Current time in the occurrence timezone.
     */
    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            'now',
            self::createTimezone($this->timezone)
        );
    }
}