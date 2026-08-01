<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Models\Occurrence;
use Dizzy\Events\Repositories\OccurrenceRepository;
use Dizzy\Events\Services\OccurrenceService;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * Handles event occurrence meta box.
 *
 * @package Dizzy\Events\Admin
 */
final class OccurrenceMetaBox
{
    /**
     * Occurrence meta box constructor.
     */
    public function __construct(
        private OccurrenceRepository $repository,
        private OccurrenceService $service
    ) {
    }

    /**
     * Register meta box hooks.
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
     * Add occurrence meta box.
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
     * Render occurrence fields.
     */
    public function render(WP_Post $post): void
    {
        wp_nonce_field(
            'dizzy_event_occurrences_save',
            'dizzy_event_occurrences_nonce'
        );

        $occurrences = $this->repository->findByEventId(
            (int) $post->ID
        );
        ?>
        <div class="dizzy-occurrences-wrapper">
            <table class="widefat dizzy-occurrences-table">
                <thead>
                    <tr>
                        <th>
                            <?php
                            esc_html_e(
                                'Start Date',
                                'dizzy-events-manager'
                            );
                            ?>
                        </th>

                        <th>
                            <?php
                            esc_html_e(
                                'Start Time',
                                'dizzy-events-manager'
                            );
                            ?>
                        </th>

                        <th>
                            <?php
                            esc_html_e(
                                'End Date',
                                'dizzy-events-manager'
                            );
                            ?>
                        </th>

                        <th>
                            <?php
                            esc_html_e(
                                'End Time',
                                'dizzy-events-manager'
                            );
                            ?>
                        </th>

                        <th>
                            <span class="screen-reader-text">
                                <?php
                                esc_html_e(
                                    'Actions',
                                    'dizzy-events-manager'
                                );
                                ?>
                            </span>
                        </th>
                    </tr>
                </thead>

                <tbody id="dizzy-occurrence-rows">
                    <?php if ($occurrences !== []) : ?>
                        <?php foreach ($occurrences as $index => $occurrence) : ?>
                            <?php
                            $this->renderRow(
                                $occurrence,
                                $index
                            );
                            ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <?php $this->renderEmptyRow(0); ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <p>
                <button
                    type="button"
                    class="button button-secondary dizzy-add-occurrence"
                >
                    <?php
                    esc_html_e(
                        'Add Date',
                        'dizzy-events-manager'
                    );
                    ?>
                </button>
            </p>
        </div>
        <?php
    }

    /**
     * Render an existing occurrence row.
     */
    private function renderRow(
        Occurrence $occurrence,
        int $index
    ): void {
        ?>
        <tr class="dizzy-occurrence-row">
            <td>
                <input
                    type="date"
                    name="dizzy_occurrences[start_date][]"
                    value="<?php echo esc_attr(
                        $occurrence->startDateTime->format('Y-m-d')
                    ); ?>"
                >
            </td>

            <td>
                <input
                    type="time"
                    name="dizzy_occurrences[start_time][]"
                    value="<?php echo esc_attr(
                        $occurrence->startDateTime->format('H:i')
                    ); ?>"
                >
            </td>

            <td>
                <input
                    type="date"
                    name="dizzy_occurrences[end_date][]"
                    value="<?php echo esc_attr(
                        $occurrence->endDateTime?->format('Y-m-d') ?? ''
                    ); ?>"
                >
            </td>

            <td>
                <input
                    type="time"
                    name="dizzy_occurrences[end_time][]"
                    value="<?php echo esc_attr(
                        $occurrence->endDateTime?->format('H:i') ?? ''
                    ); ?>"
                >
            </td>

            <td>
                <input
                    type="hidden"
                    name="dizzy_occurrences[sort_order][]"
                    value="<?php echo esc_attr((string) $index); ?>"
                >

                <button
                    type="button"
                    class="button dizzy-remove-occurrence"
                >
                    <?php
                    esc_html_e(
                        'Remove',
                        'dizzy-events-manager'
                    );
                    ?>
                </button>
            </td>
        </tr>
        <?php
    }

    /**
     * Render an empty occurrence row.
     */
    private function renderEmptyRow(int $index): void
    {
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
                    value="<?php echo esc_attr((string) $index); ?>"
                >

                <button
                    type="button"
                    class="button dizzy-remove-occurrence"
                >
                    <?php
                    esc_html_e(
                        'Remove',
                        'dizzy-events-manager'
                    );
                    ?>
                </button>
            </td>
        </tr>
        <?php
    }

    /**
     * Save event occurrences.
     */
    public function save(int $postId): void
    {
        if (! $this->canSave($postId)) {
            return;
        }

        $data = [];

        if (
            isset($_POST['dizzy_occurrences'])
            && is_array($_POST['dizzy_occurrences'])
        ) {
            $data = wp_unslash(
                $_POST['dizzy_occurrences']
            );
        }

        $this->service->replaceForEvent(
            $postId,
            $data
        );
    }

    /**
     * Determine whether occurrence data can be saved.
     */
    private function canSave(int $postId): bool
    {
        if (
            ! isset($_POST['dizzy_event_occurrences_nonce'])
            || ! is_string($_POST['dizzy_event_occurrences_nonce'])
        ) {
            return false;
        }

        $nonce = sanitize_text_field(
            wp_unslash(
                $_POST['dizzy_event_occurrences_nonce']
            )
        );

        if (
            ! wp_verify_nonce(
                $nonce,
                'dizzy_event_occurrences_save'
            )
        ) {
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

        return current_user_can(
            'edit_post',
            $postId
        );
    }
}