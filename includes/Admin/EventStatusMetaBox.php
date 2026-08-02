<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;
use WP_Post;

defined('ABSPATH') || exit;

final class EventStatusMetaBox
{
    private const NONCE_ACTION = 'dizzy_event_status_save';

    private const NONCE_NAME = 'dizzy_event_status_nonce';

    /** @var array<string, string> */
    private array $statuses;

    public function __construct()
    {
        $this->statuses = [
            'publish' => __('Published', 'dizzy-events-manager'),
            'draft' => __('Draft', 'dizzy-events-manager'),
            'pending' => __('Pending Review', 'dizzy-events-manager'),
            'future' => __('Scheduled', 'dizzy-events-manager'),
            'private' => __('Private', 'dizzy-events-manager'),
            'cancelled' => __('Cancelled', 'dizzy-events-manager'),
            'archived' => __('Archived', 'dizzy-events-manager'),
        ];
    }

    public function register(): void
    {
        add_action('add_meta_boxes_' . Config::POST_TYPE_EVENT, [$this, 'addMetaBox']);
        add_action('save_post_' . Config::POST_TYPE_EVENT, [$this, 'save'], 20, 2);
    }

    public function addMetaBox(): void
    {
        add_meta_box(
            'dizzy-event-status',
            __('Event Status', 'dizzy-events-manager'),
            [$this, 'render'],
            Config::POST_TYPE_EVENT,
            'side',
            'high'
        );
    }

    public function render(WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        ?>
        <p>
            <label for="dizzy_event_status"><?php esc_html_e('Status', 'dizzy-events-manager'); ?></label>
        </p>
        <select id="dizzy_event_status" name="dizzy_event_status" class="widefat">
            <?php foreach ($this->statuses as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($post->post_status, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php esc_html_e('Cancelled and archived events remain available in admin but are hidden from public event and reservation flows.', 'dizzy-events-manager'); ?>
        </p>
        <?php
    }

    public function save(int $postId, WP_Post $post): void
    {
        $nonce = isset($_POST[self::NONCE_NAME])
            ? sanitize_text_field(wp_unslash((string) $_POST[self::NONCE_NAME]))
            : '';

        if (
            $nonce === ''
            || ! wp_verify_nonce($nonce, self::NONCE_ACTION)
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || wp_is_post_revision($postId)
            || ! current_user_can('edit_post', $postId)
        ) {
            return;
        }

        $status = isset($_POST['dizzy_event_status'])
            ? sanitize_key(wp_unslash((string) $_POST['dizzy_event_status']))
            : '';

        if (! isset($this->statuses[$status]) || $status === $post->post_status) {
            return;
        }

        remove_action('save_post_' . Config::POST_TYPE_EVENT, [$this, 'save'], 20);
        $result = wp_update_post(['ID' => $postId, 'post_status' => $status], true);
        add_action('save_post_' . Config::POST_TYPE_EVENT, [$this, 'save'], 20, 2);

        if (is_wp_error($result)) {
            error_log(sprintf('Dizzy Events: status update failed for event %d: %s', $postId, $result->get_error_message()));
        }
    }
}

