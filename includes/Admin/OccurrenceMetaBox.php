<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;
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

    private const VALIDATION_QUERY_ARG = 'dizzy_occurrence_validation';

    public function __construct(
        private OccurrenceRepository $repository,
        private OccurrenceService $service
    ) {
    }

    public function register(): void
    {
        add_action('add_meta_boxes_' . Config::POST_TYPE_EVENT, [$this, 'add']);
        add_action('save_post_' . Config::POST_TYPE_EVENT, [$this, 'save']);
        add_action('admin_notices', [$this, 'renderAdminNotices']);
    }

    public function add(): void
    {
        add_meta_box(
            'dizzy_event_occurrences',
            esc_html__('Event Dates', 'dizzy-events-manager'),
            [$this, 'render'],
            Config::POST_TYPE_EVENT,
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
        $recurrence = get_post_meta((int) $post->ID, '_dizzy_recurrence_rule', true);
        $recurrence = is_array($recurrence) ? $recurrence : [];
        ?>
        <div class="dizzy-occurrences-wrapper">
            <fieldset style="margin-bottom: 16px; padding: 12px; border: 1px solid #ccd0d4;">
                <legend><strong><?php esc_html_e('Recurring Event', 'dizzy-events-manager'); ?></strong></legend>
                <label>
                    <input type="checkbox" name="dizzy_recurrence[enabled]" value="1" <?php checked(($recurrence['enabled'] ?? '') === '1'); ?>>
                    <?php esc_html_e('Generate dates automatically from the first date below', 'dizzy-events-manager'); ?>
                </label>
                <p>
                    <label>
                        <?php esc_html_e('Frequency', 'dizzy-events-manager'); ?>
                        <select name="dizzy_recurrence[frequency]">
                            <?php foreach (['daily' => __('Daily', 'dizzy-events-manager'), 'weekly' => __('Weekly', 'dizzy-events-manager'), 'monthly' => __('Monthly', 'dizzy-events-manager')] as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected(($recurrence['frequency'] ?? 'weekly'), $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label style="margin-left: 12px;">
                        <?php esc_html_e('Every', 'dizzy-events-manager'); ?>
                        <input type="number" min="1" max="52" name="dizzy_recurrence[interval]" value="<?php echo esc_attr((string) ($recurrence['interval'] ?? 1)); ?>" style="width: 64px;">
                    </label>
                    <label style="margin-left: 12px;">
                        <?php esc_html_e('Number of dates', 'dizzy-events-manager'); ?>
                        <input type="number" min="1" max="100" name="dizzy_recurrence[count]" value="<?php echo esc_attr((string) ($recurrence['count'] ?? 4)); ?>" style="width: 72px;">
                    </label>
                </p>
                <p class="description"><?php esc_html_e('Saving regenerates the date list. The first date, time, and duration are used as the template.', 'dizzy-events-manager'); ?></p>
            </fieldset>
            <table class="widefat dizzy-occurrences-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Start Date', 'dizzy-events-manager'); ?></th>
                        <th><?php esc_html_e('Start Time', 'dizzy-events-manager'); ?></th>
                        <th><?php esc_html_e('End Date', 'dizzy-events-manager'); ?></th>
                        <th><?php esc_html_e('End Time', 'dizzy-events-manager'); ?></th>
                        <th><?php esc_html_e('Capacity', 'dizzy-events-manager'); ?></th>
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
                <input type="number" min="1" name="dizzy_occurrences[capacity][]" value="<?php echo esc_attr($occurrence->capacity !== null ? (string) $occurrence->capacity : ''); ?>" style="width: 80px;" placeholder="<?php esc_attr_e('Unlimited', 'dizzy-events-manager'); ?>">
            </td>
            <td>
                <input type="hidden" name="dizzy_occurrences[id][]" value="<?php echo esc_attr((string) $occurrence->id); ?>">
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
                <input type="hidden" name="dizzy_occurrences[id][]" value="">
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
                <input type="number" min="1" name="dizzy_occurrences[capacity][]" style="width: 80px;" placeholder="<?php esc_attr_e('Unlimited', 'dizzy-events-manager'); ?>">
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

        $recurrence = $this->sanitizeRecurrence(
            isset($_POST['dizzy_recurrence']) && is_array($_POST['dizzy_recurrence'])
                ? wp_unslash($_POST['dizzy_recurrence'])
                : []
        );

        if ($recurrence['enabled'] === '1') {
            $data = $this->service->expandRecurrence(
                $data,
                $recurrence['frequency'],
                (int) $recurrence['interval'],
                (int) $recurrence['count']
            );
        }

        try {
            $errors = $this->service->replaceForEvent($postId, $data);

            if ($errors === []) {
                if ($recurrence['enabled'] === '1') {
                    update_post_meta($postId, '_dizzy_recurrence_rule', $recurrence);
                } else {
                    delete_post_meta($postId, '_dizzy_recurrence_rule');
                }
            }

            if ($errors !== []) {
                set_transient(
                    $this->validationTransientKey($postId),
                    $errors,
                    5 * MINUTE_IN_SECONDS
                );

                add_filter(
                    'redirect_post_location',
                    static function (string $location): string {
                        return add_query_arg(
                            self::VALIDATION_QUERY_ARG,
                            '1',
                            $location
                        );
                    }
                );
            }
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

    public function renderAdminNotices(): void
    {
        if ($this->queryFlagIsSet(self::ERROR_QUERY_ARG)) {
            $this->renderPersistenceErrorNotice();
        }

        if ($this->queryFlagIsSet(self::VALIDATION_QUERY_ARG)) {
            $this->renderValidationNotice();
        }
    }

    private function renderPersistenceErrorNotice(): void
    {
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

    private function renderValidationNotice(): void
    {
        $postId = isset($_GET['post'])
            ? absint($_GET['post'])
            : 0;

        if ($postId <= 0) {
            return;
        }

        $key    = $this->validationTransientKey($postId);
        $errors = get_transient($key);

        delete_transient($key);

        if (! is_array($errors) || $errors === []) {
            return;
        }
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong>
                    <?php
                    esc_html_e(
                        'Event dates were not updated. Please correct the following:',
                        'dizzy-events-manager'
                    );
                    ?>
                </strong>
            </p>
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <?php
                    if (
                        ! is_array($error)
                        || ! isset($error['row'], $error['code'])
                    ) {
                        continue;
                    }
                    ?>
                    <li>
                        <?php
                        echo esc_html(
                            $this->validationMessage(
                                absint($error['row']),
                                sanitize_key((string) $error['code'])
                            )
                        );
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    private function validationMessage(int $row, string $code): string
    {
        $messages = [
            'invalid_event'        => __('The event identifier is invalid.', 'dizzy-events-manager'),
            'too_many_occurrences' => __('An event can contain no more than 100 dates.', 'dizzy-events-manager'),
            'start_date_required'  => __('A start date is required.', 'dizzy-events-manager'),
            'start_time_required'  => __('A start time is required.', 'dizzy-events-manager'),
            'invalid_start'        => __('The start date or time is invalid.', 'dizzy-events-manager'),
            'incomplete_end'       => __('The end date and end time must be entered together.', 'dizzy-events-manager'),
            'invalid_end'          => __('The end date or time is invalid.', 'dizzy-events-manager'),
            'end_before_start'     => __('The end time must not be earlier than the start time.', 'dizzy-events-manager'),
        ];

        $message = $messages[$code]
            ?? __('The occurrence contains invalid data.', 'dizzy-events-manager');

        if ($row <= 0) {
            return $message;
        }

        return sprintf(
            /* translators: 1: occurrence row number, 2: validation message. */
            __('Row %1$d: %2$s', 'dizzy-events-manager'),
            $row,
            $message
        );
    }

    private function validationTransientKey(int $postId): string
    {
        return sprintf(
            'dizzy_occurrence_errors_%d_%d',
            get_current_user_id(),
            $postId
        );
    }

    private function queryFlagIsSet(string $key): bool
    {
        return isset($_GET[$key])
            && sanitize_text_field(wp_unslash($_GET[$key])) === '1';
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

    /**
     * @param array<string, mixed> $data
     *
     * @return array{enabled:string, frequency:string, interval:int, count:int}
     */
    private function sanitizeRecurrence(array $data): array
    {
        $frequency = isset($data['frequency'])
            ? sanitize_key((string) $data['frequency'])
            : 'weekly';

        if (! in_array($frequency, ['daily', 'weekly', 'monthly'], true)) {
            $frequency = 'weekly';
        }

        return [
            'enabled' => isset($data['enabled']) && (string) $data['enabled'] === '1' ? '1' : '0',
            'frequency' => $frequency,
            'interval' => min(max(1, absint($data['interval'] ?? 1)), 52),
            'count' => min(max(1, absint($data['count'] ?? 4)), 100),
        ];
    }
}

