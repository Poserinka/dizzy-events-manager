<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;
use Dizzy\Events\Poster\Repositories\PosterRepository;
use Dizzy\Events\Poster\Services\PosterService;
use Dizzy\Events\Poster\Support\PosterFormats;
use Dizzy\Events\Poster\Support\PosterTemplates;
use Throwable;
use WP_Post;

defined('ABSPATH') || exit;

final class PosterAdmin
{
    public function __construct(
        private readonly PosterService $service,
        private readonly PosterRepository $repository,
    ) {
    }

    public function register(): void
    {
        add_action(
            'add_meta_boxes_' . Config::POST_TYPE_EVENT,
            [$this, 'addMetaBox']
        );

        add_action(
            'admin_post_dizzy_generate_poster',
            [$this, 'generate']
        );
    }

    public function addMetaBox(): void
    {
        add_meta_box(
            'dizzy_event_poster_generator',
            esc_html__('AI Poster Generator', 'dizzy-events-manager'),
            [$this, 'render'],
            Config::POST_TYPE_EVENT,
            'side'
        );
    }

    public function render(WP_Post $post): void
    {
        $poster = $this->repository->findByEvent($post->ID);
        $status = isset($_GET['dizzy_poster_status']) && is_string($_GET['dizzy_poster_status'])
            ? sanitize_key(wp_unslash($_GET['dizzy_poster_status']))
            : '';

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';

        wp_nonce_field(
            'dizzy_generate_poster_' . $post->ID,
            'dizzy_poster_nonce'
        );

        echo '<input type="hidden" name="action" value="dizzy_generate_poster">';
        echo '<input type="hidden" name="post_id" value="' . esc_attr((string) $post->ID) . '">';

        echo '<p>' . esc_html__('Generate an AI poster for this event.', 'dizzy-events-manager') . '</p>';

        echo '<p><label for="dizzy_poster_template"><strong>' . esc_html__('Design', 'dizzy-events-manager') . '</strong></label><br>';
        echo '<select id="dizzy_poster_template" name="template" style="width:100%">';
        foreach (PosterTemplates::all() as $key => $template) {
            echo '<option value="' . esc_attr($key) . '">' . esc_html($template['label']) . '</option>';
        }
        echo '</select></p>';

        echo '<p><label for="dizzy_poster_format"><strong>' . esc_html__('Output format', 'dizzy-events-manager') . '</strong></label><br>';
        echo '<select id="dizzy_poster_format" name="format" style="width:100%">';
        foreach (PosterFormats::all() as $key => $format) {
            echo '<option value="' . esc_attr($key) . '">' . esc_html($format['label']) . '</option>';
        }
        echo '</select></p>';

        echo '<p><label for="dizzy_poster_direction"><strong>' . esc_html__('Extra art direction (optional)', 'dizzy-events-manager') . '</strong></label><br>';
        echo '<textarea id="dizzy_poster_direction" name="direction" rows="3" maxlength="300" style="width:100%" placeholder="' . esc_attr__('For example: feature a saxophone and deep blue lighting', 'dizzy-events-manager') . '"></textarea></p>';

        if ($status === 'success') {
            echo '<div class="notice notice-success inline"><p>' . esc_html__('Poster generated successfully.', 'dizzy-events-manager') . '</p></div>';
        } elseif ($status === 'error') {
            echo '<div class="notice notice-error inline"><p>' . esc_html__('Poster generation failed. Check the API configuration and try again.', 'dizzy-events-manager') . '</p></div>';
        }

        if ($poster && $poster->imageUrl !== '') {
            echo '<img src="' . esc_url($poster->imageUrl) . '" style="width:100%;height:auto;" alt="">';
            echo '<p><a class="button button-secondary" href="' . esc_url($poster->imageUrl) . '" download>' . esc_html__('Download latest poster', 'dizzy-events-manager') . '</a></p>';
        }

        submit_button(
            esc_html__('Generate Poster', 'dizzy-events-manager'),
            'primary',
            'submit',
            false
        );

        echo '</form>';
    }

    public function generate(): void
    {
        $postId = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        if (
            ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
            || ! $postId
            || ! isset($_POST['dizzy_poster_nonce'])
            || ! is_string($_POST['dizzy_poster_nonce'])
        ) {
            wp_safe_redirect(admin_url());
            exit;
        }

        if (! current_user_can('edit_post', $postId)) {
            wp_die(esc_html__('Permission denied.', 'dizzy-events-manager'));
        }

        check_admin_referer(
            'dizzy_generate_poster_' . $postId,
            'dizzy_poster_nonce'
        );

        $redirectUrl = get_edit_post_link($postId, '')
            ?: admin_url('post.php?post=' . $postId . '&action=edit');

        try {
            $templateKey = PosterTemplates::sanitize(isset($_POST['template']) && is_string($_POST['template']) ? sanitize_key(wp_unslash($_POST['template'])) : 'classic');
            $formatKey = PosterFormats::sanitize(isset($_POST['format']) && is_string($_POST['format']) ? sanitize_key(wp_unslash($_POST['format'])) : 'social_square');
            $direction = isset($_POST['direction']) && is_string($_POST['direction'])
                ? sanitize_textarea_field(wp_unslash($_POST['direction']))
                : '';
            $details = $this->eventDetails($postId);

            $this->service->create([
                'event_id' => $postId,
                'prompt' => $this->buildPrompt($postId, $templateKey, $direction),
                'template' => $templateKey,
                'format' => $formatKey,
                'title' => get_the_title($postId),
                'date' => $details['date'],
                'venue' => $details['venue'],
            ]);
        } catch (Throwable) {
            wp_safe_redirect(add_query_arg('dizzy_poster_status', 'error', $redirectUrl));
            exit;
        }

        wp_safe_redirect(add_query_arg('dizzy_poster_status', 'success', $redirectUrl));
        exit;
    }

    private function buildPrompt(int $postId, string $templateKey, string $direction): string
    {
        $title = get_the_title($postId);
        $content = wp_strip_all_tags((string) get_post_field('post_content', $postId));

        $template = PosterTemplates::get($templateKey);

        return sprintf(
            'Create a professional background image for a Jazzcafe Dizzy Rotterdam event poster. Event: %s. Details: %s. Visual direction: %s. %s. Do not include any words, letters, logos, captions, dates or typography; leave calm negative space in the lower part for text overlay.',
            $title,
            $content,
            $template['style'],
            $direction
        );
    }

    /** @return array{date:string,venue:string} */
    private function eventDetails(int $postId): array
    {
        global $wpdb;

        $start = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT start_datetime FROM {$wpdb->prefix}dizzy_event_occurrences WHERE event_id = %d AND status = %s ORDER BY start_datetime ASC LIMIT 1",
                $postId,
                'publish'
            )
        );
        $date = is_string($start) && $start !== ''
            ? wp_date('d F Y - H:i', strtotime($start), wp_timezone())
            : '';
        $venues = wp_get_post_terms($postId, Config::TAX_VENUE, ['fields' => 'names']);
        $venue = ! is_wp_error($venues) && isset($venues[0])
            ? (string) $venues[0]
            : 'Jazzcafe Dizzy Rotterdam';

        return ['date' => $date, 'venue' => $venue];
    }
}
