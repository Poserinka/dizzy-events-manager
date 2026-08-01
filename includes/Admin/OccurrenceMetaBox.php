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
                'add',
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
                'render',
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


            <table class="widefat dizzy-occurrences-table">


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


                    <th></th>

                </tr>

                </thead>



                <tbody id="dizzy-occurrence-rows">



                <?php if (! empty($occurrences)): ?>


                    <?php foreach ($occurrences as $index => $occurrence): ?>


                        <?php

                        $this->renderRow(
                            $occurrence,
                            $index
                        );

                        ?>


                    <?php endforeach; ?>



                <?php else: ?>


                    <?php

                    $this->renderEmptyRow(
                        0
                    );

                    ?>


                <?php endif; ?>


                </tbody>


            </table>



            <p>

                <button
                    type="button"
                    class="button button-secondary dizzy-add-occurrence"
                >

                    <?php esc_html_e(
                        'Add Date',
                        'dizzy-events-manager'
                    ); ?>

                </button>

            </p>



        </div>


        <?php
    }





    /**
     * Render existing occurrence row.
     */
    private function renderRow(
        object $occurrence,
        int $index
    ): void {

        ?>

        <tr class="dizzy-occurrence-row">


            <td>

                <input
                    type="date"
                    name="dizzy_occurrences[start_date][]"
                    value="<?php echo esc_attr(
                        $occurrence
                            ->startDateTime
                            ->format('Y-m-d')
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
                            ->format('H:i')
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
                                ->format('Y-m-d')
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
                                ->format('H:i')
                            : ''
                    ); ?>"
                >

            </td>



            <td>

                <input
                    type="hidden"
                    name="dizzy_occurrences[sort_order][]"
                    value="<?php echo esc_attr(
                        $index
                    ); ?>"
                >


                <button
                    type="button"
                    class="button dizzy-remove-occurrence"
                >

                    <?php esc_html_e(
                        'Remove',
                        'dizzy-events-manager'
                    ); ?>

                </button>

            </td>


        </tr>

        <?php
    }





    /**
     * Render empty row.
     */
    private function renderEmptyRow(
        int $index
    ): void {

        ?>

        <tr class="dizzy-occurrence-row">


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



            <td>

                <input
                    type="hidden"
                    name="dizzy_occurrences[sort_order][]"
                    value="<?php echo esc_attr(
                        $index
                    ); ?>"
                >


                <button
                    type="button"
                    class="button dizzy-remove-occurrence"
                >

                    <?php esc_html_e(
                        'Remove',
                        'dizzy-events-manager'
                    ); ?>

                </button>

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

                sanitize_text_field(
                    wp_unslash(
                        $_POST['dizzy_occurrences_nonce']
                    )
                ),

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
            wp_is_post_revision(
                $postId
            )
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
                ? wp_unslash(
                    $_POST['dizzy_occurrences']
                )
                : [];



        $this->repository
            ->replaceForEvent(
                $postId,
                $data
            );
    }
}