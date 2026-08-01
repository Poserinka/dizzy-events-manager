<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Services\EventService;

defined('ABSPATH') || exit;

/**
 * Handles single event display.
 *
 * @package Dizzy\Events\Frontend
 */
final class SingleEvent
{
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
    public function template(
        string $template
    ): string {

        if (
            ! is_singular('event')
        ) {
            return $template;
        }


        $custom =
            DIZZY_EVENTS_PATH .
            'includes/Frontend/Views/single-event.php';


        if (file_exists($custom)) {

            return $custom;
        }


        return $template;
    }
}