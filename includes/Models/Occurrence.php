<?php

declare(strict_types=1);

namespace Dizzy\Events\Models;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

defined('ABSPATH') || exit;

/**
 * Event occurrence model.
 *
 * @package Dizzy\Events\Models
 */
readonly class Occurrence
{
    /**
     * Create an occurrence.
     */
    public function __construct(
        public int $id,
        public int $eventId,
        public DateTimeImmutable $startDateTime,
        public ?DateTimeImmutable $endDateTime,
    ) {
        if (
            $this->endDateTime !== null
            && $this->endDateTime < $this->startDateTime
        ) {
            throw new InvalidArgumentException(
                'Occurrence end date cannot be before start date.'
            );
        }
    }

    /**
     * Hydrate from database row.
     */
    public static function hydrateFromRow(object $row): self
    {
        $timezone = self::resolveTimezone(
            isset($row->timezone) ? (string) $row->timezone : ''
        );

        $start = new DateTimeImmutable(
            (string) $row->start_datetime,
            $timezone
        );

        $end = ! empty($row->end_datetime)
            ? new DateTimeImmutable((string) $row->end_datetime, $timezone)
            : null;

        return new self(
            id: (int) $row->id,
            eventId: (int) $row->event_id,
            startDateTime: $start,
            endDateTime: $end
        );
    }

    /**
     * Check whether the occurrence starts in the future.
     */
    public function isUpcoming(): bool
    {
        return $this->startDateTime > new DateTimeImmutable(
            'now',
            $this->startDateTime->getTimezone()
        );
    }

    /**
     * Format date.
     */
    public function formattedDate(): string
    {
        return $this->startDateTime->format('d F Y');
    }

    /**
     * Format time.
     */
    public function formattedTime(): string
    {
        return $this->startDateTime->format('H:i');
    }

    /**
     * Resolve a stored timezone safely.
     */
    private static function resolveTimezone(string $timezone): DateTimeZone
    {
        if ($timezone !== '') {
            try {
                return new DateTimeZone($timezone);
            } catch (Exception) {
                // Fall back to the current WordPress timezone.
            }
        }

        return wp_timezone();
    }
}
