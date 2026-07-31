<?php

declare(strict_types=1);

namespace Dizzy\Events\Models;

use DateTimeImmutable;
use DateTimeZone;
use Dizzy\Events\Contracts\Hydrates;
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
readonly class Occurrence implements Hydrates
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
     * Create an occurrence from a source object.
     *
     * @throws InvalidArgumentException
     */
    public static function from(object $source): static
    {
        $timezone = self::createTimezone(
            $source->timezone ?? Config::DEFAULT_TIMEZONE
        );

        return new self(
            id: (int) $source->id,

            eventId: (int) $source->event_id,

            startDateTime: new DateTimeImmutable(
                $source->start_datetime,
                $timezone
            ),

            endDateTime: empty($source->end_datetime)
                ? null
                : new DateTimeImmutable(
                    $source->end_datetime,
                    $timezone
                ),

            allDay: (bool) $source->all_day,

            timezone: $timezone->getName(),

            status: (string) $source->status,

            createdAt: new DateTimeImmutable(
                $source->created_at,
                $timezone
            ),

            updatedAt: new DateTimeImmutable(
                $source->updated_at,
                $timezone
            ),
        );
    }

    /**
     * Convert model to array.
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
     * Determine whether occurrence has ended.
     */
    public function hasEnded(): bool
    {
        if ($this->endDateTime === null) {
            return false;
        }

        return $this->endDateTime < $this->now();
    }

    /**
     * Determine whether occurrence is currently running.
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
     * Determine whether occurrence starts in future.
     */
    public function isUpcoming(): bool
    {
        return $this->startDateTime > $this->now();
    }

    /**
     * Create timezone instance.
     */
    private static function createTimezone(
        string $timezone
    ): DateTimeZone {

        return new DateTimeZone($timezone);
    }

    /**
     * Current time in occurrence timezone.
     */
    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            'now',
            self::createTimezone($this->timezone)
        );
    }
}