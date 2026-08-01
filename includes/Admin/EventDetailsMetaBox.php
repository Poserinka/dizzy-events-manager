<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

defined('ABSPATH') || exit;

/**
 * Event details administration box.
 *
 * @package Dizzy\Events\Admin
 */
final class EventDetailsMetaBox
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
     * Add meta box.
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
            'side',
            'default'
        );
    }


    /**
     * Render fields.
     */
    public function render(
        \WP_Post $post
    ): void {

        wp_nonce_field(
            'dizzy_event_details_save',
            'dizzy_event_details_nonce'
        );


        $fields = [
            'artist',
            'genre',
            'venue',
            'ticket_url',
            'ticket_price',
        ];


        foreach ($fields as $field) {

            $value =
                get_post_meta(
                    $post->ID,
                    '_dizzy_' . $field,
                    true
                );


            ?>

            <p>

                <label>
                    <?php echo esc_html(
                        ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $field
                            )
                        )
                    ); ?>
                </label>

                <input
                    type="text"
                    class="widefat"
                    name="dizzy_<?php echo esc_attr($field); ?>"
                    value="<?php echo esc_attr($value); ?>"
                >

            </p>

            <?php
        }


        $featured =
            get_post_meta(
                $post->ID,
                '_dizzy_featured',
                true
            );

        ?>

        <p>

            <label>

                <input
                    type="checkbox"
                    name="dizzy_featured"
                    value="1"
                    <?php checked(
                        $featured,
                        '1'
                    ); ?>
                >

                <?php esc_html_e(
                    'Featured Event',
                    'dizzy-events-manager'
                ); ?>

            </label>

        </p>

        <?php
    }


    /**
     * Save fields.
     */
    public function save(
        int $postId
    ): void {

        if (
            ! isset(
                $_POST['dizzy_event_details_nonce']
            )
        ) {
            return;
        }


        if (
            ! wp_verify_nonce(
                $_POST['dizzy_event_details_nonce'],
                'dizzy_event_details_save'
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


        $fields = [
            'artist',
            'genre',
            'venue',
            'ticket_url',
            'ticket_price',
        ];


        foreach ($fields as $field) {

            $key =
                'dizzy_' . $field;


            if (
                isset(
                    $_POST[$key]
                )
            ) {

                update_post_meta(
                    $postId,
                    '_dizzy_' . $field,
                    sanitize_text_field(
                        $_POST[$key]
                    )
                );
            }
        }


        update_post_meta(
            $postId,
            '_dizzy_featured',
            isset(
                $_POST['dizzy_featured']
            )
                ? '1'
                : '0'
        );
    }
}