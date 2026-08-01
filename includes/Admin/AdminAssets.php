<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

defined('ABSPATH') || exit;

/**
 * Loads admin assets.
 *
 * @package Dizzy\Events\Admin
 */
final class AdminAssets
{
    /**
     * Register hooks.
     */
    public function register(): void
    {
        add_action(
            'admin_enqueue_scripts',
            [
                $this,
                'enqueue',
            ]
        );
    }

    /**
     * Enqueue admin assets.
     */
    public function enqueue(string $hook): void
    {
        if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();

        if (! $screen || $screen->post_type !== 'event') {
            return;
        }

        wp_enqueue_script(
            'dizzy-events-admin',
            DIZZY_EVENTS_URL . 'assets/js/occurrence-admin.js',
            [
                'jquery',
            ],
            DIZZY_EVENTS_VERSION,
            true
        );

        wp_localize_script(
            'dizzy-events-admin',
            'DizzyEventsAdmin',
            [
                'removeLabel' => esc_html__(
                    'Remove',
                    'dizzy-events-manager'
                ),
            ]
        );

        wp_enqueue_style(
            'dizzy-events-admin',
            DIZZY_EVENTS_URL . 'assets/css/admin.css',
            [],
            DIZZY_EVENTS_VERSION
        );
    }
}
