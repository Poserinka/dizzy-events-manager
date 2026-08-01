<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend\ViewModels;

use Dizzy\Events\Models\Occurrence;

defined('ABSPATH') || exit;

/**
 * Frontend occurrence data.
 *
 * @package Dizzy\Events\Frontend\ViewModels
 */
readonly class OccurrenceViewData
{
    /**
     * Create occurrence view data.
     */
    public function __construct(
        public string $date,
        public string $time,
    ) {
    }

    /**
     * Create from occurrence model.
     */
    public static function from(Occurrence $occurrence): self
    {
        $timestamp = $occurrence->startDateTime->getTimestamp();
        $timezone  = $occurrence->startDateTime->getTimezone();

        return new self(
            date: wp_date(
                (string) get_option('date_format'),
                $timestamp,
                $timezone
            ),
            time: wp_date(
                (string) get_option('time_format'),
                $timestamp,
                $timezone
            ),
        );
    }
}
