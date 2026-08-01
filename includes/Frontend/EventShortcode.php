<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Frontend\ViewModels\EventViewData;
use Dizzy\Events\Services\EventService;

defined('ABSPATH') || exit;

/**
 * Frontend event shortcode.
 *
 * @package Dizzy\Events\Frontend
 */
final class EventShortcode
{
    /**
     * Default number of events to display.
     */
    private const DEFAULT_LIMIT = 10;

    /**
     * Maximum number of events allowed per shortcode.
     */
    private const MAX_LIMIT = 100;

    /**
     * Create the event shortcode.
     */
    public function __construct(
        private EventService $service
    ) {
    }

    /**
     * Register shortcode.
     */
    public function register(): void
    {
        add_shortcode(
            'dizzy_events',
            [
                $this,
                'render',
            ]
        );
    }

    /**
     * Render events.
     *
     * Supported usage: [dizzy_events limit="10"]
     *
     * @param array<string, mixed> $atts Shortcode attributes.
     */
    public function render(array $atts = []): string
    {
        $attributes = shortcode_atts(
            [
                'limit' => (string) self::DEFAULT_LIMIT,
            ],
            $atts,
            'dizzy_events'
        );

        $limit  = absint($attributes['limit']);
        $limit  = $limit > 0 ? $limit : self::DEFAULT_LIMIT;
        $limit  = min($limit, self::MAX_LIMIT);
        $events = $this->service->getUpcomingEvents($limit);

        if ($events === []) {
            return sprintf(
                '<p>%s</p>',
                esc_html__(
                    'No upcoming events.',
                    'dizzy-events-manager'
                )
            );
        }

        ob_start();
        ?>
        <div class="dizzy-events">
            <?php foreach ($events as $event) : ?>
                <?php
                $viewData = EventViewData::from(
                    $event,
                    $this->service->getEventDetails($event->id),
                    $this->service->getUpcomingOccurrences($event->id)
                );

                include DIZZY_EVENTS_PATH . 'includes/Frontend/Views/event-card.php';
                ?>
            <?php endforeach; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
