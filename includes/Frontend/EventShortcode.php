<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Services\EventService;

defined('ABSPATH') || exit;

/**
 * Frontend event shortcode.
 *
 * @package Dizzy\Events\Frontend
 */
final class EventShortcode
{
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
     */
    public function render(
        array $atts = []
    ): string {

        $events =
            $this->service
                ->getUpcomingEvents(
                    10
                );


        if (empty($events)) {

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

            <?php foreach ($events as $event): ?>

                <article class="dizzy-event">

                    <h3>
                        <?php echo esc_html(
                            $event->title
                        ); ?>
                    </h3>


                    <?php if ($event->excerpt): ?>

                        <div>
                            <?php echo wp_kses_post(
                                $event->excerpt
                            ); ?>
                        </div>

                    <?php endif; ?>

                </article>

            <?php endforeach; ?>

        </div>

        <?php

        return (string) ob_get_clean();
    }
}