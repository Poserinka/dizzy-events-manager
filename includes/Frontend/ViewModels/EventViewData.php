<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend\ViewModels;

use Dizzy\Events\Models\Event;
use Dizzy\Events\Models\EventDetails;
use Dizzy\Events\Models\Occurrence;

defined('ABSPATH') || exit;

/**
 * Frontend event presentation data.
 *
 * @package Dizzy\Events\Frontend\ViewModels
 */
readonly class EventViewData
{
    /**
     * Create event view data.
     *
     * @param array<Occurrence> $occurrences Event occurrences.
     */
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
        /** @var array<OccurrenceViewData> */
        public array $dates,
    ) {
    }

    /**
     * Create view data from application data.
     *
     * @param array<Occurrence> $occurrences Event occurrences.
     */
    public static function from(
        Event $event,
        EventDetails $details,
        array $occurrences
    ): self {
        $dates = array_map(
            static function (Occurrence $occurrence): OccurrenceViewData {
                return OccurrenceViewData::from($occurrence);
            },
            $occurrences
        );

        return new self(
            id: $event->id,
            title: $event->title,
            url: get_permalink($event->id),
            image: get_the_post_thumbnail_url($event->id, 'large') ?: '',
            excerpt: $event->excerpt ?? '',
            artist: $details->artist,
            genre: $details->genre,
            venue: $details->venue,
            ticketUrl: $details->ticketUrl,
            ticketPrice: $details->ticketPrice,
            featured: $details->featured,
            dates: $dates,
        );
    }
}
