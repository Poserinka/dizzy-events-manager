<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Models\Occurrence;
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

        $data = $this->service->getEvent((int) $post->ID);

        if ($data === null) {
            return $template;
        }

        $data['upcomingOccurrences'] = array_values(
            array_filter(
                $data['occurrences'],
                static function (Occurrence $occurrence): bool {
                    return $occurrence->isUpcoming();
                }
            )
        );

        $data['pastOccurrences'] = array_values(
            array_filter(
                $data['occurrences'],
                static function (Occurrence $occurrence): bool {
                    return ! $occurrence->isUpcoming();
                }
            )
        );

        set_query_var('dizzy_event_data', $data);

        $customTemplate = DIZZY_EVENTS_PATH
            . 'includes/Frontend/Views/single-event.php';

        if (file_exists($customTemplate)) {
            return $customTemplate;
        }

        return $template;
    }
}
