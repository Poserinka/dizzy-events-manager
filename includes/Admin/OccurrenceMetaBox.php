<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Models\Occurrence;
use Dizzy\Events\Repositories\OccurrenceRepository;
use Dizzy\Events\Services\OccurrenceService;
use Throwable;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * Handles event occurrence meta box.
 *
 * @package Dizzy\Events\Admin
 */
final class OccurrenceMetaBox
{
    private const ERROR_QUERY_ARG = 'dizzy_occurrence_error';

    public function __construct(
        private OccurrenceRepository $repository,
        private OccurrenceService $service
    ) {
    }

    public function register(): void
    {
        add_action('add_meta_boxes_event', [$this, 'add']);
        add_action('save_post_event', [$this, 'save']);
        add_action(
            'admin_notices',
            [$this, 'renderPersistenceErrorNotice']
        );
    }

    public function add(): void
    {
        add_meta_box(
            'dizzy_event_occurrences',
            esc_html__('Event Dates', 'dizzy-events-manager'),
            [$this, 'render'],
            'event',
            'normal',
            'high'
        );
    }

    public function render(WP_Post $post): void
    {
        wp_nonce_field(
            'dizzy_event_occurrences_save',
            'dizzy_event_occurrences_nonce'
        );

        $occurrences = $this->repository->findByEventId((int) $post->ID);
        ?>
        <div class="dizzy-occurrences-wrapper">
            <table class="widefat dizzy-occurrences-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Start Date', 'dizzy-events-manager'); ?></th>
                        <th><?php esc_html_e('Start Time', 'dizzy-events-manager'); ?></th>
                        <th><?php esc_html_e('End Date', 'dizzy-events-manager'); ?></th>
                        <th><?php esc_html_e('End Time', 'dizzy-events-manager'); ?></th>
                        <th>
                            <span class="screen-reader-text">
                                <?php esc_html_e('Actions', 'dizzy-events-manager'); ?>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody id="dizzy-occurrence-rows">
                    <?php if ($occurrences !== []) : ?>
                        <?php foreach ($occurrences as $index => $occurrence) : ?>
                            <?php $this->renderRow($occurrence, $index); ?>
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
                    <?php esc_html_e('Add Date', 'dizzy-events-manager'); ?>
                </button>
            </p>
        </div>
        <?php
    }

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
                <?php
                $this->renderTimeSelect(
                    'dizzy_occurrences[start_time][]',
                    $occurrence->startDateTime->format('H:i')
                );
                ?>
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
                <?php
                $this->renderTimeSelect(
                    'dizzy_occurrences[end_time][]',
                    $occurrence->endDateTime?->format('H:i') ?? ''
                );
                ?>
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
                    <?php esc_html_e('Remove', 'dizzy-events-manager'); ?>
                </button>
            </td>
        </tr>
        <?php
    }

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
                <?php
                $this->renderTimeSelect(
                    'dizzy_occurrences[start_time][]',
                    ''
                );
                ?>
            </td>
            <td>
                <input
                    type="date"
                    name="dizzy_occurrences[end_date][]"
                >
            </td>
            <td>
                <?php
                $this->renderTimeSelect(
                    'dizzy_occurrences[end_time][]',
                    ''
                );
                ?>
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
                    <?php esc_html_e('Remove', 'dizzy-events-manager'); ?>
                </button>
            </td>
        </tr>
        <?php
    }

    private function renderTimeSelect(string $name, string $selected): void
    {
        $options = self::timeOptions();

        if ($selected !== '' && ! in_array($selected, $options, true)) {
            $options[] = $selected;
        }
        ?>
        <select name="<?php echo esc_attr($name); ?>">
            <option value="">
                <?php esc_html_e('Select time', 'dizzy-events-manager'); ?>
            </option>
            <?php foreach ($options as $time) : ?>
                <option
                    value="<?php echo esc_attr($time); ?>"
                    <?php selected($selected, $time); ?>
                >
                    <?php echo esc_html(str_replace(':', '.', $time)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Return allowed time values from 14:00 until midnight.
     *
     * @return array<int, string>
     */
    public static function timeOptions(): array
    {
        $options = [];

        for ($minutes = 14 * 60; $minutes < 24 * 60; $minutes += 30) {
            $options[] = sprintf(
                '%02d:%02d',
                intdiv($minutes, 60),
                $minutes % 60
            );
        }

        $options[] = '00:00';

        return $options;
    }

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
            $data = wp_unslash($_POST['dizzy_occurrences']);
        }

        try {
            $this->service->replaceForEvent($postId, $data);
        } catch (Throwable $exception) {
            error_log(
                sprintf(
                    'Dizzy Events occurrence save failed for event %d: %s',
                    $postId,
                    $exception->getMessage()
                )
            );

            add_filter(
                'redirect_post_location',
                static function (string $location): string {
                    return add_query_arg(
                        self::ERROR_QUERY_ARG,
                        '1',
                        $location
                    );
                }
            );
        }
    }

    public function renderPersistenceErrorNotice(): void
    {
        if (
            ! isset($_GET[self::ERROR_QUERY_ARG])
            || sanitize_text_field(
                wp_unslash($_GET[self::ERROR_QUERY_ARG])
            ) !== '1'
        ) {
            return;
        }
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                esc_html_e(
                    'The event was saved, but its dates could not be updated. Please try again and check the error log if the problem continues.',
                    'dizzy-events-manager'
                );
                ?>
            </p>
        </div>
        <?php
    }

    private function canSave(int $postId): bool
    {
        if (
            ! isset($_POST['dizzy_event_occurrences_nonce'])
            || ! is_string($_POST['dizzy_event_occurrences_nonce'])
        ) {
            return false;
        }

        $nonce = sanitize_text_field(
            wp_unslash($_POST['dizzy_event_occurrences_nonce'])
        );

        if (! wp_verify_nonce($nonce, 'dizzy_event_occurrences_save')) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
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
