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

        add_action(
            'admin_post_dizzy_export_poster',
            [$this, 'export']
        );
    }

    public function addMetaBox(): void
    {
        add_meta_box(
            'dizzy_event_poster_generator',
            esc_html__('Poster Generator', 'dizzy-events-manager'),
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

        $backgroundId = (int) get_post_meta($post->ID, '_dizzy_poster_background_id', true);
        $backgroundUrl = $backgroundId > 0 ? wp_get_attachment_image_url($backgroundId, 'medium') : '';
        $featuredId = (int) get_post_thumbnail_id($post->ID);
        $featuredUrl = $featuredId > 0 ? wp_get_attachment_image_url($featuredId, 'medium') : '';
        $storedFormat = $poster?->attachmentId ? (string) get_post_meta($poster->attachmentId, '_dizzy_poster_format', true) : 'social_square';
        $selectedFormat = PosterFormats::sanitize($storedFormat);

        echo '<div class="dizzy-poster-generator-shell" data-action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<div class="dizzy-poster-generator-pane">';
        echo '<input type="hidden" id="dizzy_poster_nonce" value="' . esc_attr(wp_create_nonce('dizzy_generate_poster_' . $post->ID)) . '">';
        echo '<input type="hidden" id="dizzy_poster_post_id" value="' . esc_attr((string) $post->ID) . '">';
        echo '<input type="hidden" id="dizzy_poster_background_id" name="background_id" value="' . esc_attr((string) $backgroundId) . '">';
        echo '<input type="hidden" id="dizzy_poster_featured_id" value="' . esc_attr((string) $featuredId) . '" data-url="' . esc_url((string) $featuredUrl) . '">';

        echo '<div class="dizzy-poster-background-card">';
        echo '<strong>' . esc_html__('Background image', 'dizzy-events-manager') . '</strong>';
        echo '<div class="dizzy-poster-background-preview">';
        echo '<img src="' . esc_url((string) $backgroundUrl) . '" alt=""' . ($backgroundUrl === '' ? ' hidden' : '') . '>';
        echo '<span class="dizzy-poster-background-empty"' . ($backgroundUrl !== '' ? ' hidden' : '') . '>' . esc_html__('No image selected', 'dizzy-events-manager') . '</span>';
        echo '</div></div>';
        echo '<div class="dizzy-poster-actions">';
        echo '<button type="button" class="button dizzy-poster-select-image">' . esc_html__('Select image', 'dizzy-events-manager') . '</button>';
        echo '<button type="button" class="button dizzy-poster-use-featured"' . ($featuredId <= 0 ? ' disabled' : '') . '>' . esc_html__('Use featured image', 'dizzy-events-manager') . '</button>';
        echo '</div></div>';

        echo '<div class="dizzy-poster-output-pane">';
        echo '<div class="dizzy-poster-output-controls"><label for="dizzy_poster_format"><strong>' . esc_html__('Output format', 'dizzy-events-manager') . '</strong></label>';
        echo '<select id="dizzy_poster_format" name="format">';
        foreach (PosterFormats::all() as $key => $format) {
            echo '<option value="' . esc_attr($key) . '"' . selected($selectedFormat, $key, false) . '>' . esc_html($format['label']) . '</option>';
        }
        echo '</select></div>';
        echo '<div class="dizzy-poster-output-card">';

        if ($status === 'success') {
            echo '<div class="notice notice-success inline dizzy-poster-status"><p>' . esc_html__('Poster generated successfully.', 'dizzy-events-manager') . '</p></div>';
        } elseif ($status === 'error') {
            echo '<div class="notice notice-error inline dizzy-poster-status"><p>' . esc_html__('Poster generation failed. Select a background image and try again.', 'dizzy-events-manager') . '</p></div>';
        }

        echo '<div class="dizzy-poster-output-preview">';
        if ($poster && $poster->imageUrl !== '') {
            echo '<img src="' . esc_url($poster->imageUrl) . '" alt="">';
        }
        echo '</div></div>';
        echo '<div class="dizzy-poster-actions">';
        if ($poster && $poster->imageUrl !== '') {
            echo '<a class="button button-secondary" href="' . esc_url($poster->imageUrl) . '" download>' . esc_html__('Download latest poster', 'dizzy-events-manager') . '</a>';

            $formatKey = $this->socialFormatKey($storedFormat);

            if (str_starts_with($formatKey, 'social_')) {
                foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook'] as $platform => $platformLabel) {
                    $exportUrl = wp_nonce_url(
                        admin_url('admin-post.php?action=dizzy_export_poster&post_id=' . $post->ID . '&platform=' . $platform),
                        'dizzy_export_poster_' . $post->ID
                    );
                    echo '<a class="button" href="' . esc_url($exportUrl) . '">' . esc_html(sprintf(__('Export for %s', 'dizzy-events-manager'), $platformLabel)) . '</a>';
                }
            }
        }

        echo '<button type="button" class="button button-primary dizzy-poster-generate">' . esc_html__('Generate Poster', 'dizzy-events-manager') . '</button>';
        echo '</div></div></div>';
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
            $templateKey = 'classic';
            $formatKey = PosterFormats::sanitize(isset($_POST['format']) && is_string($_POST['format']) ? sanitize_key(wp_unslash($_POST['format'])) : 'social_square');
            $backgroundId = isset($_POST['background_id']) ? absint($_POST['background_id']) : 0;
            if ($backgroundId <= 0) {
                $backgroundId = (int) get_post_thumbnail_id($postId);
            }
            if ($backgroundId <= 0 || ! wp_attachment_is_image($backgroundId)) {
                throw new \RuntimeException('A poster background image is required.');
            }
            $backgroundUrl = wp_get_attachment_url($backgroundId);
            if (! is_string($backgroundUrl) || $backgroundUrl === '') {
                throw new \RuntimeException('The poster background image is unavailable.');
            }
            $details = $this->eventDetails($postId);

            $this->service->create([
                'event_id' => $postId,
                'prompt' => '',
                'image_url' => $backgroundUrl,
                'template' => $templateKey,
                'format' => $formatKey,
                'title' => get_the_title($postId),
                'date' => $details['date'],
                'venue' => $details['venue'],
            ]);
            update_post_meta($postId, '_dizzy_poster_background_id', $backgroundId);
        } catch (Throwable) {
            wp_safe_redirect(add_query_arg('dizzy_poster_status', 'error', $redirectUrl));
            exit;
        }

        wp_safe_redirect(add_query_arg('dizzy_poster_status', 'success', $redirectUrl));
        exit;
    }

    public function export(): void
    {
        $postId = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        $platform = isset($_GET['platform']) && is_string($_GET['platform'])
            ? sanitize_key(wp_unslash($_GET['platform']))
            : '';

        if ($postId <= 0 || ! in_array($platform, ['instagram', 'facebook'], true)) {
            wp_die(esc_html__('Invalid poster export request.', 'dizzy-events-manager'));
        }

        check_admin_referer('dizzy_export_poster_' . $postId);

        if (! current_user_can('edit_post', $postId)) {
            wp_die(esc_html__('Permission denied.', 'dizzy-events-manager'));
        }

        $poster = $this->repository->findByEvent($postId);
        $attachmentId = $poster?->attachmentId ?? 0;
        $storedFormat = $attachmentId > 0 ? (string) get_post_meta($attachmentId, '_dizzy_poster_format', true) : '';
        $formatKey = $this->socialFormatKey($storedFormat);
        $path = $attachmentId > 0 ? get_attached_file($attachmentId) : '';

        if (! str_starts_with($formatKey, 'social_') || ! is_string($path) || ! is_readable($path)) {
            wp_die(esc_html__('No matching social poster is available for export.', 'dizzy-events-manager'));
        }

        $slug = sanitize_title(get_the_title($postId)) ?: 'event';
        $baseName = 'dizzy-' . $slug . '-' . $formatKey;
        $caption = $this->socialCaption($postId, $platform);

        if (class_exists(\ZipArchive::class)) {
            if (! function_exists('wp_tempnam')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            $temporary = wp_tempnam($baseName . '.zip');
            $zip = is_string($temporary) ? new \ZipArchive() : null;

            if ($zip && $zip->open($temporary, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) ?: 'png';
                $zip->addFile($path, $baseName . '.' . $extension);
                $zip->addFromString($baseName . '-caption.txt', $caption);
                $zip->close();
                $this->sendDownload($temporary, $baseName . '.zip', 'application/zip', true);
            }

            if (is_string($temporary) && is_file($temporary)) {
                wp_delete_file($temporary);
            }
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) ?: 'png';
        $this->sendDownload($path, $baseName . '.' . $extension, 'image/png');
    }

    private function socialCaption(int $postId, string $platform): string
    {
        $title = get_the_title($postId);
        $description = wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $postId)), 45, '...');
        $url = get_permalink($postId);
        $tags = $platform === 'instagram'
            ? '#JazzcafeDizzy #Rotterdam #LiveMusic #Jazz'
            : '#JazzcafeDizzy #Rotterdam #LiveMusic';

        return trim($title . "\n\n" . $description . "\n\n" . $url . "\n\n" . $tags) . "\n";
    }

    private function socialFormatKey(string $key): string
    {
        $compatible = [
            'social_square',
            'social_portrait',
            'social_story',
            'instagram_square',
            'instagram_portrait',
            'instagram_story',
            'facebook_square',
            'facebook_portrait',
            'facebook_story',
        ];

        return in_array($key, $compatible, true) ? PosterFormats::sanitize($key) : '';
    }

    private function sendDownload(string $path, string $name, string $mime, bool $deleteAfter = false): never
    {
        nocache_headers();
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($name) . '"');
        header('Content-Length: ' . (string) filesize($path));
        readfile($path);

        if ($deleteAfter) {
            wp_delete_file($path);
        }

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

