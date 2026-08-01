<?php

declare(strict_types=1);

namespace Dizzy\Events\Models;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

defined('ABSPATH') || exit;

/**
 * Event occurrence model.
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

    ) {

        if (
            $this->endDateTime !== null
            &&
            $this->endDateTime < $this->startDateTime
        ) {

            throw new InvalidArgumentException(
                'Occurrence end date cannot be before start date.'
            );

        }

    }



    /**
     * Hydrate from database row.
     */
    public static function hydrateFromRow(
        object $row
    ): self {


        $timezone =
            new DateTimeZone(
                'Europe/Amsterdam'
            );


        $start =
            new DateTimeImmutable(
                $row->start_datetime,
                $timezone
            );


        $end =
            ! empty(
                $row->end_datetime
            )
                ? new DateTimeImmutable(
                    $row->end_datetime,
                    $timezone
                )
                : null;



        return new self(

            id:
                (int) $row->id,


            eventId:
                (int) $row->event_id,


            startDateTime:
                $start,


            endDateTime:
                $end

        );
    }



    /**
     * Check if occurrence is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->startDateTime >
            new DateTimeImmutable(
                'now',
                new DateTimeZone(
                    'Europe/Amsterdam'
                )
            );
    }



    /**
     * Format date.
     */
    public function formattedDate(): string
    {
        return $this
            ->startDateTime
            ->format(
                'd F Y'
            );
    }



    /**
     * Format time.
     */
    public function formattedTime(): string
    {
        return $this
            ->startDateTime
            ->format(
                'H:i'
            );
    }
}