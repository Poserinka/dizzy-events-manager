<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Repositories\OccurrenceRepository;

defined('ABSPATH') || exit;

/**
 * Handles event occurrence meta box.
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
            'add_meta_boxes_event',
            [
                $this,
                'add'
            ]
        );


        add_action(
            'save_post_event',
            [
                $this,
                'save'
            ]
        );
    }



    /**
     * Add meta box.
     */
    public function add(): void
    {
        add_meta_box(

            'dizzy_event_occurrences',

            esc_html__(
                'Event Dates',
                'dizzy-events-manager'
            ),

            [
                $this,
                'render'
            ],

            'event',

            'normal',

            'high'

        );
    }



    /**
     * Render fields.
     */
    public function render(
        \WP_Post $post
    ): void {


        wp_nonce_field(

            'dizzy_event_occurrences_save',

            'dizzy_event_occurrences_nonce'

        );



        $occurrences =
            $this->repository
                ->findByEventId(
                    $post->ID
                );

        ?>

        <div class="dizzy-occurrences-wrapper">


            <table class="widefat">

                <thead>

                <tr>

                    <th>
                        <?php esc_html_e(
                            'Start Date',
                            'dizzy-events-manager'
                        ); ?>
                    </th>

                    <th>
                        <?php esc_html_e(
                            'Start Time',
                            'dizzy-events-manager'
                        ); ?>
                    </th>

                    <th>
                        <?php esc_html_e(
                            'End Date',
                            'dizzy-events-manager'
                        ); ?>
                    </th>

                    <th>
                        <?php esc_html_e(
                            'End Time',
                            'dizzy-events-manager'
                        ); ?>
                    </th>

                </tr>

                </thead>


                <tbody>


                <?php if (! empty($occurrences)): ?>


                    <?php foreach ($occurrences as $index => $occurrence): ?>


                        <tr>


                            <td>

                                <input
                                    type="date"
                                    name="dizzy_occurrences[start_date][]"
                                    value="<?php echo esc_attr(
                                        $occurrence
                                            ->startDateTime
                                            ->format(
                                                'Y-m-d'
                                            )
                                    ); ?>"
                                >

                            </td>



                            <td>

                                <input
                                    type="time"
                                    name="dizzy_occurrences[start_time][]"
                                    value="<?php echo esc_attr(
                                        $occurrence
                                            ->startDateTime
                                            ->format(
                                                'H:i'
                                            )
                                    ); ?>"
                                >

                            </td>



                            <td>

                                <input
                                    type="date"
                                    name="dizzy_occurrences[end_date][]"
                                    value="<?php echo esc_attr(
                                        $occurrence->endDateTime
                                            ? $occurrence
                                                ->endDateTime
                                                ->format(
                                                    'Y-m-d'
                                                )
                                            : ''
                                    ); ?>"
                                >

                            </td>



                            <td>

                                <input
                                    type="time"
                                    name="dizzy_occurrences[end_time][]"
                                    value="<?php echo esc_attr(
                                        $occurrence->endDateTime
                                            ? $occurrence
                                                ->endDateTime
                                                ->format(
                                                    'H:i'
                                                )
                                            : ''
                                    ); ?>"
                                >

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <?php $this->emptyRow(); ?>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


        <?php
    }



    /**
     * Empty default row.
     */
    private function emptyRow(): void
    {
        ?>

        <tr>

            <td>

                <input
                    type="date"
                    name="dizzy_occurrences[start_date][]"
                >

            </td>


            <td>

                <input
                    type="time"
                    name="dizzy_occurrences[start_time][]"
                >

            </td>


            <td>

                <input
                    type="date"
                    name="dizzy_occurrences[end_date][]"
                >

            </td>


            <td>

                <input
                    type="time"
                    name="dizzy_occurrences[end_time][]"
                >

            </td>


        </tr>

        <?php
    }



    /**
     * Save occurrences.
     */
    public function save(
        int $postId
    ): void {


        if (
            ! isset(
                $_POST['dizzy_occurrences_nonce']
            )
        ) {
            return;
        }



        if (
            ! wp_verify_nonce(

                $_POST['dizzy_occurrences_nonce'],

                'dizzy_event_occurrences_save'

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



        $data =
            isset(
                $_POST['dizzy_occurrences']
            )
            &&
            is_array(
                $_POST['dizzy_occurrences']
            )
                ? $_POST['dizzy_occurrences']
                : [];



        $this->repository
            ->replaceForEvent(
                $postId,
                $data
            );
    }
}