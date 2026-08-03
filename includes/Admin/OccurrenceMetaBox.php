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
        add_action('save_post_' . Config::POST_TYPE_EVENT, [$this, 'save'], 20);
        add_action('admin_notices', [$this, 'renderAdminNotices']);
    }

    public function add(): void
    {
        add_meta_box(
            'dizzy_event_occurrences',
            esc_html__('Event Date', 'dizzy-events-manager'),
            [$this, 'render'],
            Config::POST_TYPE_EVENT,
            'normal',
            'high',
            ['__block_editor_compatible_meta_box' => true]
        );
    }

    public function render(WP_Post $post): void
    {
        wp_nonce_field('dizzy_event_date_save', 'dizzy_event_date_nonce');

        $occurrences = $this->repository->findByEventId((int) $post->ID);
        $occurrence = $occurrences[0] ?? null;
        ?>
        <table class="widefat dizzy-occurrences-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Start Date', 'dizzy-events-manager'); ?></th>
                    <th><?php esc_html_e('Start Time', 'dizzy-events-manager'); ?></th>
                    <th><?php esc_html_e('End Date', 'dizzy-events-manager'); ?></th>
                    <th><?php esc_html_e('End Time', 'dizzy-events-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $this->renderFields($occurrence); ?>
            </tbody>
        </table>
        <?php
    }

    private function renderFields(?Occurrence $occurrence): void
    {
        $startDate = $occurrence?->startDateTime->format('Y-m-d') ?? '';
        $startTime = $occurrence?->startDateTime->format('H:i') ?? '';
        $endDate = $occurrence?->endDateTime?->format('Y-m-d') ?? '';
        $endTime = $occurrence?->endDateTime?->format('H:i') ?? '';
        ?>
        <tr>
            <td>
                <input type="hidden" name="dizzy_event_date[id]" value="<?php echo esc_attr((string) ($occurrence?->id ?? 0)); ?>">
                <input type="date" name="dizzy_event_date[start_date]" value="<?php echo esc_attr($startDate); ?>">
            </td>
            <td><?php $this->renderTimeSelect('dizzy_event_date[start_time]', $startTime); ?></td>
            <td><input type="date" name="dizzy_event_date[end_date]" value="<?php echo esc_attr($endDate); ?>"></td>
            <td><?php $this->renderTimeSelect('dizzy_event_date[end_time]', $endTime); ?></td>
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
            <option value=""><?php esc_html_e('Select time', 'dizzy-events-manager'); ?></option>
            <?php foreach ($options as $time) : ?>
                <option value="<?php echo esc_attr($time); ?>" <?php selected($selected, $time); ?>>
                    <?php echo esc_html(str_replace(':', '.', $time)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * @return array<int, string>
     */
    public static function timeOptions(): array
    {
        $options = [];

        for ($minutes = 0; $minutes < 24 * 60; $minutes += 30) {
            $options[] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
        }

        return $options;
    }

    public function save(int $postId): void
    {
        if (! $this->canSave($postId)) {
            return;
        }

        $submitted = isset($_POST['dizzy_event_date']) && is_array($_POST['dizzy_event_date'])
            ? wp_unslash($_POST['dizzy_event_date'])
            : [];

        $data = [
            'id' => [$submitted['id'] ?? 0],
            'start_date' => [$submitted['start_date'] ?? ''],
            'start_time' => [$submitted['start_time'] ?? ''],
            'end_date' => [$submitted['end_date'] ?? ''],
            'end_time' => [$submitted['end_time'] ?? ''],
            'sort_order' => [0],
        ];

        try {
            $errors = $this->service->replaceForEvent($postId, $data);

            if ($errors !== []) {
                set_transient($this->validationTransientKey($postId), $errors, 5 * MINUTE_IN_SECONDS);
                $this->addRedirectFlag(self::VALIDATION_QUERY_ARG);
                return;
            }

            delete_post_meta($postId, '_dizzy_recurrence_rule');
        } catch (Throwable $exception) {
            set_transient($this->persistenceTransientKey($postId), $exception->getMessage(), 5 * MINUTE_IN_SECONDS);
            error_log(sprintf('Dizzy Events date save failed for event %d: %s', $postId, $exception->getMessage()));
            $this->addRedirectFlag(self::ERROR_QUERY_ARG);
        }
    }

    public function renderAdminNotices(): void
    {
        $postId = isset($_GET['post']) ? absint($_GET['post']) : 0;

        if ($postId <= 0) {
            return;
        }

        $persistenceKey = $this->persistenceTransientKey($postId);
        $persistenceError = get_transient($persistenceKey);

        if ($this->queryFlagIsSet(self::ERROR_QUERY_ARG) || is_string($persistenceError)) {
            delete_transient($persistenceKey);
            $this->renderPersistenceErrorNotice(is_string($persistenceError) ? $persistenceError : '');
        }

        $validationKey = $this->validationTransientKey($postId);
        $validationErrors = get_transient($validationKey);

        if ($this->queryFlagIsSet(self::VALIDATION_QUERY_ARG) || is_array($validationErrors)) {
            delete_transient($validationKey);
            $this->renderValidationNotice(is_array($validationErrors) ? $validationErrors : []);
        }
    }

    private function renderPersistenceErrorNotice(string $details): void
    {
        ?>
        <div class="notice notice-error">
            <p><strong><?php esc_html_e('The event was saved, but its date could not be updated.', 'dizzy-events-manager'); ?></strong></p>
            <?php if ($details !== '') : ?>
                <p><?php esc_html_e('Technical details:', 'dizzy-events-manager'); ?> <code><?php echo esc_html($details); ?></code></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * @param array<int, array{row:int, code:string}> $errors
     */
    private function renderValidationNotice(array $errors): void
    {
        if ($errors === []) {
            return;
        }
        ?>
        <div class="notice notice-error">
            <p><strong><?php esc_html_e('Event date was not updated. Please check the date and time fields.', 'dizzy-events-manager'); ?></strong></p>
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?php echo esc_html($this->validationMessage((string) ($error['code'] ?? ''))); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    private function validationMessage(string $code): string
    {
        $messages = [
            'start_date_required' => __('A start date is required.', 'dizzy-events-manager'),
            'start_time_required' => __('A start time is required.', 'dizzy-events-manager'),
            'invalid_start' => __('The start date or time is invalid.', 'dizzy-events-manager'),
            'incomplete_end' => __('The end date and end time must be entered together.', 'dizzy-events-manager'),
            'invalid_end' => __('The end date or time is invalid.', 'dizzy-events-manager'),
            'end_before_start' => __('The end time must not be earlier than the start time.', 'dizzy-events-manager'),
        ];

        return $messages[$code] ?? __('The event date contains invalid data.', 'dizzy-events-manager');
    }

    private function canSave(int $postId): bool
    {
        if (
            ! isset($_POST['dizzy_event_date_nonce'])
            || ! is_string($_POST['dizzy_event_date_nonce'])
            || ! wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['dizzy_event_date_nonce'])),
                'dizzy_event_date_save'
            )
        ) {
            return false;
        }

        if (wp_is_post_revision($postId) !== false) {
            return false;
        }

        return current_user_can('edit_post', $postId);
    }

    private function addRedirectFlag(string $flag): void
    {
        add_filter(
            'redirect_post_location',
            static fn (string $location): string => add_query_arg($flag, '1', $location)
        );
    }

    private function persistenceTransientKey(int $postId): string
    {
        return sprintf('dizzy_occurrence_persistence_%d_%d', get_current_user_id(), $postId);
    }

    private function validationTransientKey(int $postId): string
    {
        return sprintf('dizzy_occurrence_errors_%d_%d', get_current_user_id(), $postId);
    }

    private function queryFlagIsSet(string $key): bool
    {
        return isset($_GET[$key]) && sanitize_text_field(wp_unslash($_GET[$key])) === '1';
    }
}
