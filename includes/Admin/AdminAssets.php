<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;

defined('ABSPATH') || exit;

final class AdminAssets
{
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(string $hook): void
    {
        if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();

        if (! $screen || $screen->post_type !== Config::POST_TYPE_EVENT) {
            return;
        }

        wp_enqueue_style(
            'dizzy-events-admin',
            DIZZY_EVENTS_URL . 'assets/css/admin.css',
            [],
            DIZZY_EVENTS_VERSION
        );

        wp_enqueue_script(
            'dizzy-events-editor',
            DIZZY_EVENTS_URL . 'assets/js/event-editor.js',
            [],
            DIZZY_EVENTS_VERSION,
            true
        );
    }
}
