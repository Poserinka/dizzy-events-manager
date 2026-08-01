<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Repositories\OccurrenceRepository;

defined('ABSPATH') || exit;

/**
 * Occurrence administration meta box.
 *
 * @package Dizzy\Events\Admin
 */
final class OccurrenceMetaBox
{
    public function __construct(
        private OccurrenceRepository $repository
    ) {
    }


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
     * Add occurrence box.
     */
    public function addMetaBox(): void
    {
        add_meta_box(
            'dizzy_occurrences',
            __('Event Dates', 'dizzy-events-manager'),
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
     * Render box.
     */
    public function render(
        \WP_Post $post
    ): void {

        wp_nonce_field(
            'dizzy_occurrence_save',
            'dizzy_occurrence_nonce'
        );

        ?>
        <p>
            <label>
                <?php esc_html_e(
                    'Start date/time',
                    'dizzy-events-manager'
                ); ?>
            </label>
        </p>

        <input
            type="datetime-local"
            name="dizzy_occurrence_start"
        >

        <p>
            <label>
                <?php esc_html_e(
                    'End date/time',
                    'dizzy-events-manager'
                ); ?>
            </label>
        </p>

        <input
            type="datetime-local"
            name="dizzy_occurrence_end"
        >

        <?php
    }


    /**
     * Save occurrence.
     */
    public function save(
        int $postId
    ): void {

        if (
            ! isset(
                $_POST['dizzy_occurrence_nonce']
            )
        ) {
            return;
        }


        if (
            ! wp_verify_nonce(
                $_POST['dizzy_occurrence_nonce'],
                'dizzy_occurrence_save'
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


        if (
            empty(
                $_POST['dizzy_occurrence_start']
            )
        ) {
            return;
        }


        $this->repository->create(
            [
                'event_id' =>
                    $postId,

                'start_datetime' =>
                    sanitize_text_field(
                        $_POST['dizzy_occurrence_start']
                    ),

                'end_datetime' =>
                    sanitize_text_field(
                        $_POST['dizzy_occurrence_end'] ?? ''
                    ),

                'all_day' => 0,

                'timezone' =>
                    'Europe/Amsterdam',

                'status' =>
                    'publish',

                'created_at' =>
                    current_time('mysql'),

                'updated_at' =>
                    current_time('mysql'),
            ]
        );
    }
}