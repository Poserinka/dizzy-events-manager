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
     * @param array<string, mixed> $atts Shortcode attributes.
     */
    public function render(array $atts = []): string
    {
        $events = $this->service->getUpcomingEvents(10);

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
