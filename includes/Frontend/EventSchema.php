<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Models\Occurrence;
use Dizzy\Events\Services\EventService;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * Generates Schema.org Event data.
 *
 * @package Dizzy\Events\Frontend
 */
final class EventSchema
{
    /**
     * Create the event schema renderer.
     */
    public function __construct(
        private EventService $service
    ) {
    }

    /**
     * Register hooks.
     */
    public function register(): void
    {
        add_action(
            'wp_head',
            [
                $this,
                'render',
            ]
        );
    }

    /**
     * Output JSON-LD.
     */
    public function render(): void
    {
        if (! is_singular('event')) {
            return;
        }

        global $post;

        if (! $post instanceof WP_Post) {
            return;
        }

        $data = $this->service->getEvent((int) $post->ID);

        if ($data === null) {
            return;
        }

        $event       = $data['event'];
        $details     = $data['details'];
        $occurrences = array_values(
            array_filter(
                $data['occurrences'],
                static function (Occurrence $occurrence): bool {
                    return $occurrence->isUpcoming();
                }
            )
        );

        if ($occurrences === []) {
            return;
        }

        $firstOccurrence = $occurrences[0];

        $schema = [
            '@context'            => 'https://schema.org',
            '@type'               => 'MusicEvent',
            'name'                => $event->title,
            'description'         => wp_strip_all_tags($event->content),
            'eventStatus'         => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'image'               => get_the_post_thumbnail_url($event->id, 'large'),
            'startDate'           => $firstOccurrence->startDateTime->format(DATE_ATOM),
            'location'            => [
                '@type' => 'Place',
                'name'  => $details->venue ?? get_bloginfo('name'),
            ],
        ];

        if ($firstOccurrence->endDateTime !== null) {
            $schema['endDate'] = $firstOccurrence->endDateTime->format(DATE_ATOM);
        }

        if ($details->artist !== null) {
            $schema['performer'] = [
                '@type' => 'PerformingGroup',
                'name'  => $details->artist,
            ];
        }

        if (count($occurrences) > 1) {
            $schema['subEvent'] = [];

            foreach ($occurrences as $occurrence) {
                $subEvent = [
                    '@type'     => 'MusicEvent',
                    'name'      => $event->title,
                    'startDate' => $occurrence->startDateTime->format(DATE_ATOM),
                ];

                if ($occurrence->endDateTime !== null) {
                    $subEvent['endDate'] = $occurrence->endDateTime->format(DATE_ATOM);
                }

                $schema['subEvent'][] = $subEvent;
            }
        }

        if ($details->ticketUrl !== null) {
            $schema['offers'] = [
                '@type'         => 'Offer',
                'url'           => $details->ticketUrl,
                'price'         => $details->ticketPrice ?? 0,
                'priceCurrency' => 'EUR',
                'availability'  => 'https://schema.org/InStock',
            ];
        }

        echo '<script type="application/ld+json">';
        echo wp_json_encode(
            $schema,
            JSON_UNESCAPED_SLASHES
        );
        echo '</script>';
    }
}
