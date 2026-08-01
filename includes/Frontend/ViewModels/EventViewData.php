<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend\ViewModels;

use Dizzy\Events\Models\Event;
use Dizzy\Events\Models\EventDetails;
use Dizzy\Events\Repositories\OccurrenceRepository;

defined('ABSPATH') || exit;

/**
 * Frontend event presentation data.
 *
 * @package Dizzy\Events\Frontend\ViewModels
 */
readonly class EventViewData
{
    public function __construct(

        public int $id,

        public string $title,

        public string $url,

        public string $image,

        public string $excerpt,

        public ?string $artist,

        public ?string $genre,

        public ?string $venue,

        public ?string $ticketUrl,

        public ?float $ticketPrice,

        public bool $featured,

        /**
         * @var array<OccurrenceViewData>
         */
        public array $dates,

    ) {
    }


    /**
     * Create view data.
     */
    public static function from(
        Event $event,
        OccurrenceRepository $occurrenceRepository
    ): self {


        $details =
            EventDetails::fromMeta(
                get_post_meta(
                    $event->id
                )
            );


        $occurrences =
            $occurrenceRepository
                ->findByEventId(
                    $event->id
                );


        $dates = [];


        foreach ($occurrences as $occurrence) {

            $dates[] =
                OccurrenceViewData::from(
                    $occurrence
                );
        }


        return new self(

            id:
                $event->id,


            title:
                $event->title,


            url:
                get_permalink(
                    $event->id
                ),


            image:
                get_the_post_thumbnail_url(
                    $event->id,
                    'large'
                ) ?: '',


            excerpt:
                $event->excerpt ?? '',


            artist:
                $details->artist,


            genre:
                $details->genre,


            venue:
                $details->venue,


            ticketUrl:
                $details->ticketUrl,


            ticketPrice:
                $details->ticketPrice,


            featured:
                $details->featured,


            dates:
                $dates,

        );
    }
}