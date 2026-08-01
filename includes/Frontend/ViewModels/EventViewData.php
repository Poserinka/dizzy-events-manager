<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend\ViewModels;

use Dizzy\Events\Models\Event;
use Dizzy\Events\Models\EventDetails;
use Dizzy\Events\Models\Occurrence;
use Throwable;

defined('ABSPATH') || exit;

/**
 * Frontend event presentation data.
 *
 * @package Dizzy\Events\Frontend\ViewModels
 */
readonly class EventViewData
{
    private const DEFAULT_CARD_DATE_LIMIT = 3;

    private const MAX_CARD_DATE_LIMIT = 10;

    /**
     * Create event view data.
     *
     * @param array<OccurrenceViewData> $dates Event occurrence view data.
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
        public ?string $address,
        public ?string $mapsUrl,
        public ?string $ticketUrl,
        public ?float $ticketPrice,
        public bool $featured,
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
        $dates = [];

        foreach ($occurrences as $occurrence) {
            if (! $occurrence instanceof Occurrence) {
                continue;
            }

            try {
                $dates[] = OccurrenceViewData::from($occurrence);
            } catch (Throwable $exception) {
                error_log(
                    sprintf(
                        'Dizzy Events skipped occurrence view data %d: %s',
                        $occurrence->id,
                        $exception->getMessage()
                    )
                );
            }
        }

        return new self(
            id: $event->id,
            title: $event->title,
            url: self::permalink($event->id),
            image: self::featuredImage($event->id),
            excerpt: self::excerpt($event),
            artist: $details->artist,
            genre: $details->genre,
            venue: $details->venue,
            address: $details->address,
            mapsUrl: $details->mapsUrl,
            ticketUrl: $details->ticketUrl,
            ticketPrice: $details->ticketPrice,
            featured: $details->featured,
            dates: $dates,
        );
    }

    /**
     * Get event-card date presentation data.
     *
     * @return array{
     *     visible: array<OccurrenceViewData>,
     *     remaining: int
     * }
     */
    public function cardDatePresentation(): array
    {
        $visible = array_slice($this->dates, 0, $this->cardDateLimit());

        return [
            'visible'   => $visible,
            'remaining' => max(0, count($this->dates) - count($visible)),
        ];
    }

    /**
     * Get dates displayed on an event card.
     *
     * @return array<OccurrenceViewData>
     */
    public function cardDates(): array
    {
        return $this->cardDatePresentation()['visible'];
    }

    /**
     * Get the number of dates omitted from an event card.
     */
    public function remainingCardDateCount(): int
    {
        return $this->cardDatePresentation()['remaining'];
    }

    /**
     * Get the filtered card date limit.
     */
    private function cardDateLimit(): int
    {
        $limit = apply_filters(
            'dizzy_events_card_date_limit',
            self::DEFAULT_CARD_DATE_LIMIT,
            $this->id
        );

        return min(
            max(1, absint($limit)),
            self::MAX_CARD_DATE_LIMIT
        );
    }

    /**
     * Get a safe event permalink.
     */
    private static function permalink(int $eventId): string
    {
        $permalink = get_permalink($eventId);

        return is_string($permalink) ? $permalink : '';
    }

    /**
     * Get a safe featured image URL.
     */
    private static function featuredImage(int $eventId): string
    {
        $image = get_the_post_thumbnail_url($eventId, 'large');

        return is_string($image) ? $image : '';
    }

    /**
     * Build the event card excerpt.
     */
    private static function excerpt(Event $event): string
    {
        $excerpt = get_the_excerpt($event->id);

        if (is_string($excerpt) && trim($excerpt) !== '') {
            return $excerpt;
        }

        return wp_trim_words(
            wp_strip_all_tags(strip_shortcodes($event->content)),
            35
        );
    }
}
