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
     * Add meta box.
     */
    public function addMetaBox(): void
    {
        add_meta_box(
            'dizzy_event_occurrences',
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
     * Render fields.
     */
    public function render(
        \WP_Post $post
    ): void {


        wp_nonce_field(
            'dizzy_occurrence_save',
            'dizzy_occurrence_nonce'
        );


        $occurrences =
            $this->repository
                ->findByEventId(
                    $post->ID
                );


        ?>

        <div class="dizzy-occurrences-list">


        <?php foreach ($occurrences as $occurrence): ?>


            <?php $this->renderRow($occurrence); ?>


        <?php endforeach; ?>


        </div>



        <button
            type="button"
            class="button dizzy-add-occurrence"
        >

            <?php esc_html_e(
                'Add Date',
                'dizzy-events-manager'
            ); ?>

        </button>



        <script type="text/html" id="dizzy-occurrence-template">


            <?php

            $this->renderEmptyRow();

            ?>


        </script>


        <?php
    }



    /**
     * Render existing row.
     */
    private function renderRow(
        object $occurrence
    ): void {

        ?>

        <div class="dizzy-occurrence-row">


            <p>

                <label>
                    <?php esc_html_e(
                        'Start',
                        'dizzy-events-manager'
                    ); ?>
                </label>


                <input
                    type="datetime-local"
                    name="dizzy_occurrences[start][]"
                    value="<?php echo esc_attr(
                        $occurrence
                            ->startDateTime
                            ->format(
                                'Y-m-d\TH:i'
                            )
                    ); ?>"
                >

            </p>



            <p>

                <label>
                    <?php esc_html_e(
                        'End',
                        'dizzy-events-manager'
                    ); ?>
                </label>


                <input
                    type="datetime-local"
                    name="dizzy_occurrences[end][]"
                    value="<?php echo esc_attr(
                        $occurrence
                            ->endDateTime
                            ->format(
                                'Y-m-d\TH:i'
                            )
                    ); ?>"
                >

            </p>



            <button
                type="button"
                class="button dizzy-remove-occurrence"
            >

                <?php esc_html_e(
                    'Remove',
                    'dizzy-events-manager'
                ); ?>

            </button>


        </div>

        <?php
    }




    /**
     * Empty template row.
     */
    private function renderEmptyRow(): void
    {
        ?>

        <div class="dizzy-occurrence-row">


            <p>

                <label>
                    <?php esc_html_e(
                        'Start',
                        'dizzy-events-manager'
                    ); ?>
                </label>


                <input
                    type="datetime-local"
                    name="dizzy_occurrences[start][]"
                >

            </p>



            <p>

                <label>
                    <?php esc_html_e(
                        'End',
                        'dizzy-events-manager'
                    ); ?>
                </label>


                <input
                    type="datetime-local"
                    name="dizzy_occurrences[end][]"
                >

            </p>



            <button
                type="button"
                class="button dizzy-remove-occurrence"
            >

                <?php esc_html_e(
                    'Remove',
                    'dizzy-events-manager'
                ); ?>

            </button>


        </div>

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


        // Saving logic will be moved
        // to repository handler next.

    }
}