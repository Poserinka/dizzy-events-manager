<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

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
     *
     * @param \WP_Post $post Current post.
     */
    public function render(
        \WP_Post $post
    ): void {

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
                <?php esc_html_e(
                    'Start date/time',
                    'dizzy-events-manager'
                ); ?>
            </label>
            <br>

            <input
                type="datetime-local"
                name="dizzy_event_start"
                value="<?php echo esc_attr($start); ?>"
            >
        </p>

        <p>
            <label>
                <?php esc_html_e(
                    'End date/time',
                    'dizzy-events-manager'
                ); ?>
            </label>
            <br>

            <input
                type="datetime-local"
                name="dizzy_event_end"
                value="<?php echo esc_attr($end); ?>"
            >
        </p>

        <?php
    }

    /**
     * Save meta values.
     */
    public function save(
        int $postId
    ): void {

        if (
            ! isset($_POST['dizzy_event_nonce'])
            ||
            ! wp_verify_nonce(
                $_POST['dizzy_event_nonce'],
                'dizzy_event_save'
            )
        ) {
            return;
        }

        if (
            defined('DOING_AUTOSAVE')
            &&
            DOING_AUTOSAVE
        ) {
            return;
        }

        if (
            ! current_user_can(
                'edit_post',
                $postId
            )
        ) {
            return;
        }

        if (isset($_POST['dizzy_event_start'])) {

            update_post_meta(
                $postId,
                '_dizzy_event_start',
                sanitize_text_field(
                    $_POST['dizzy_event_start']
                )
            );
        }

        if (isset($_POST['dizzy_event_end'])) {

            update_post_meta(
                $postId,
                '_dizzy_event_end',
                sanitize_text_field(
                    $_POST['dizzy_event_end']
                )
            );
        }
    }
}