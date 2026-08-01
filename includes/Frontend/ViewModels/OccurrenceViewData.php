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
    public function __construct(

        public string $date,

        public string $time,

    ) {
    }


    /**
     * Create from occurrence model.
     */
    public static function from(
        Occurrence $occurrence
    ): self {

        return new self(

            date:
                $occurrence
                    ->startDateTime
                    ->format(
                        'd F Y'
                    ),


            time:
                $occurrence
                    ->startDateTime
                    ->format(
                        'H:i'
                    ),

        );
    }
}