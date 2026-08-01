<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Services\EventService;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * Handles single event display.
 *
 * @package Dizzy\Events\Frontend
 */
final class SingleEvent
{
    /**
     * Create the single event renderer.
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
        add_filter(
            'template_include',
            [
                $this,
                'template',
            ]
        );
    }

    /**
     * Load custom template.
     */
    public function template(string $template): string
    {
        if (! is_singular('event')) {
            return $template;
        }

        global $post;

        if (! $post instanceof WP_Post) {
            return $template;
        }

        $eventId = (int) $post->ID;
        $data    = $this->service->getEvent($eventId);

        if ($data === null) {
            return $template;
        }

        $data['upcomingOccurrences'] = $this->service
            ->getUpcomingOccurrences($eventId);
        $data['pastOccurrences'] = $this->service
            ->getPastOccurrences($eventId);

        set_query_var('dizzy_event_data', $data);

        $customTemplate = DIZZY_EVENTS_PATH
            . 'includes/Frontend/Views/single-event.php';

        if (file_exists($customTemplate)) {
            return $customTemplate;
        }

        return $template;
    }
}
