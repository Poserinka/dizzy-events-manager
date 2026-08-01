<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

defined('ABSPATH') || exit;

/**
 * Loads frontend event assets.
 *
 * @package Dizzy\Events\Frontend
 */
final class FrontendAssets
{
    /**
     * Register frontend asset hooks.
     */
    public function register(): void
    {
        add_action(
            'wp_enqueue_scripts',
            [
                $this,
                'enqueue',
            ]
        );
    }

    /**
     * Enqueue event styles when they can be used.
     */
    public function enqueue(): void
    {
        if (! is_singular('event') && ! $this->currentPostHasShortcode()) {
            return;
        }

        wp_enqueue_style(
            'dizzy-events-frontend',
            DIZZY_EVENTS_URL . 'assets/css/frontend.css',
            [],
            DIZZY_EVENTS_VERSION
        );
    }

    /**
     * Determine whether the current post contains the event shortcode.
     */
    private function currentPostHasShortcode(): bool
    {
        global $post;

        return $post instanceof \WP_Post
            && has_shortcode($post->post_content, 'dizzy_events');
    }
}
