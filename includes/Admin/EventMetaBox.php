<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use WP_Post;

defined('ABSPATH') || exit;

/**
 * Event administration meta box.
 *
 * Handles event custom fields in WordPress admin.
 *
 * @package Dizzy\Events\Admin
 */
final class EventMetaBox
{
    /**
     * Register hooks.
     */
    public function register(): void
    {
        add_action(
            'add_meta_boxes',
            [
                $this,
                'addMetaBox',
            ]
        );

        add_action(
            'save_post_event',
            [
                $this,
                'save',
            ]
        );
    }

    /**
     * Add event meta box.
     */
    public function addMetaBox(): void
    {
        add_meta_box(
            'dizzy_event_details',
            __('Event Details', 'dizzy-events-manager'),
            [
                $this,
                'render',
            ],
            'event',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box.
     */
    public function render(WP_Post $post): void
    {
        wp_nonce_field(
            'dizzy_event_save',
            'dizzy_event_nonce'
        );

        $start = get_post_meta(
            $post->ID,
            '_dizzy_event_start',
            true
        );

        $end = get_post_meta(
            $post->ID,
            '_dizzy_event_end',
            true
        );
        ?>
        <p>
            <label>
                <?php
                esc_html_e(
                    'Start date/time',
                    'dizzy-events-manager'
                );
                ?>
            </label>
            <br>

            <input
                type="datetime-local"
                name="dizzy_event_start"
                value="<?php echo esc_attr((string) $start); ?>"
            >
        </p>

        <p>
            <label>
                <?php
                esc_html_e(
                    'End date/time',
                    'dizzy-events-manager'
                );
                ?>
            </label>
            <br>

            <input
                type="datetime-local"
                name="dizzy_event_end"
                value="<?php echo esc_attr((string) $end); ?>"
            >
        </p>
        <?php
    }

    /**
     * Save meta values.
     */
    public function save(int $postId): void
    {
        if (! $this->canSave($postId)) {
            return;
        }

        if (
            isset($_POST['dizzy_event_start'])
            && is_string($_POST['dizzy_event_start'])
        ) {
            update_post_meta(
                $postId,
                '_dizzy_event_start',
                sanitize_text_field(
                    wp_unslash($_POST['dizzy_event_start'])
                )
            );
        }

        if (
            isset($_POST['dizzy_event_end'])
            && is_string($_POST['dizzy_event_end'])
        ) {
            update_post_meta(
                $postId,
                '_dizzy_event_end',
                sanitize_text_field(
                    wp_unslash($_POST['dizzy_event_end'])
                )
            );
        }
    }

    /**
     * Determine whether event data can be saved.
     */
    private function canSave(int $postId): bool
    {
        if (
            ! isset($_POST['dizzy_event_nonce'])
            || ! is_string($_POST['dizzy_event_nonce'])
        ) {
            return false;
        }

        $nonce = sanitize_text_field(
            wp_unslash($_POST['dizzy_event_nonce'])
        );

        if (! wp_verify_nonce($nonce, 'dizzy_event_save')) {
            return false;
        }

        if (
            defined('DOING_AUTOSAVE')
            && DOING_AUTOSAVE
        ) {
            return false;
        }

        if (wp_is_post_revision($postId) !== false) {
            return false;
        }

        if (wp_is_post_autosave($postId) !== false) {
            return false;
        }

        return current_user_can('edit_post', $postId);
    }
}
