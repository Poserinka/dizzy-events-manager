<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;
use WP_Post;

defined('ABSPATH') || exit;

final class EventEditor
{
    public function register(): void
    {
        add_filter('use_block_editor_for_post_type', [$this, 'disableBlockEditor'], 20, 2);
        add_filter('theme_' . Config::POST_TYPE_EVENT . '_templates', [$this, 'eventTemplates'], 20, 4);
        add_action('wp_insert_post', [$this, 'assignDefaultTemplate'], 20, 3);
        add_filter('admin_body_class', [$this, 'adminBodyClass']);
        add_action('save_post_' . Config::POST_TYPE_EVENT, [$this, 'saveRelations'], 20, 3);
    }

    public function disableBlockEditor(bool $useBlockEditor, string $postType): bool
    {
        return $postType === Config::POST_TYPE_EVENT ? false : $useBlockEditor;
    }

    /** @param array<string,string> $templates @return array<string,string> */
    public function eventTemplates(array $templates, mixed $theme = null, mixed $post = null, string $postType = ''): array
    {
        return array_merge(wp_get_theme()->get_page_templates(null, 'page'), $templates);
    }

    public function assignDefaultTemplate(int $postId, WP_Post $post, bool $update): void
    {
        if ($post->post_type !== Config::POST_TYPE_EVENT || wp_is_post_revision($postId) !== false) {
            return;
        }

        $current = (string) get_post_meta($postId, '_wp_page_template', true);
        if ($current !== '' && $current !== 'default') {
            return;
        }

        $template = $this->findEventFullWidthTemplate();
        if ($template !== '') {
            update_post_meta($postId, '_wp_page_template', $template);
        }
    }

    public function adminBodyClass(string $classes): string
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        return $screen && $screen->post_type === Config::POST_TYPE_EVENT
            ? $classes . ' dizzy-event-editor-screen'
            : $classes;
    }

    public function saveRelations(int $postId, WP_Post $post, bool $update): void
    {
        if (
            wp_is_post_revision($postId) !== false
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || ! current_user_can('edit_post', $postId)
        ) {
            return;
        }

        $nonce = isset($_POST['dizzy_event_relations_nonce'])
            ? sanitize_text_field(wp_unslash((string) $_POST['dizzy_event_relations_nonce']))
            : '';
        if ($nonce === '' || ! wp_verify_nonce($nonce, 'dizzy_event_relations_save')) {
            return;
        }

        $artistsInput = isset($_POST['dizzy_event_artists']) && is_array($_POST['dizzy_event_artists'])
            ? wp_unslash($_POST['dizzy_event_artists'])
            : [];
        $artists = [];
        foreach ($artistsInput as $artist) {
            if (! is_array($artist)) continue;
            $name = sanitize_text_field((string) ($artist['name'] ?? ''));
            if ($name === '') continue;
            $imageId = absint($artist['image_id'] ?? 0);
            $artists[] = [
                'name' => $name,
                'role' => sanitize_text_field((string) ($artist['role'] ?? '')),
                'contact' => sanitize_text_field((string) ($artist['contact'] ?? '')),
                'imageId' => $imageId > 0 && wp_attachment_is_image($imageId) ? $imageId : 0,
                'imageUrl' => $imageId > 0 ? (string) wp_get_attachment_image_url($imageId, 'medium') : '',
            ];
        }

        $venue = sanitize_text_field(wp_unslash((string) ($_POST['dizzy_event_venue_name'] ?? '')));
        $tags = sanitize_text_field(wp_unslash((string) ($_POST['dizzy_event_tags'] ?? '')));
        update_post_meta($postId, '_dizzy_event_artists', $artists);
        update_post_meta($postId, '_dizzy_event_venue_name', $venue);
        update_post_meta($postId, '_dizzy_event_tags', $tags);

        // Keep the hidden taxonomies synchronized for backwards compatibility.
        wp_set_object_terms($postId, array_column($artists, 'name'), Config::TAX_ARTIST, false);
        foreach ($artists as $artist) {
            $term = term_exists($artist['name'], Config::TAX_ARTIST);
            $termId = is_array($term) ? absint($term['term_id'] ?? 0) : absint($term);
            if ($termId <= 0) continue;
            wp_update_term($termId, Config::TAX_ARTIST, ['description' => $artist['role']]);
            if ($artist['contact'] === '') delete_term_meta($termId, '_dizzy_artist_contact');
            else update_term_meta($termId, '_dizzy_artist_contact', $artist['contact']);
            if ($artist['imageId'] > 0) update_term_meta($termId, '_dizzy_artist_image_id', $artist['imageId']);
            else delete_term_meta($termId, '_dizzy_artist_image_id');
        }
        wp_set_object_terms($postId, $venue !== '' ? [$venue] : [], Config::TAX_VENUE, false);
        $tagNames = array_values(array_filter(array_map('trim', explode(',', $tags))));
        wp_set_object_terms($postId, $tagNames, Config::TAX_TAG, false);
    }

    private function findEventFullWidthTemplate(): string
    {
        foreach (wp_get_theme()->get_page_templates(null, 'page') as $file => $name) {
            $normalizedName = sanitize_title((string) $name);
            $normalizedFile = sanitize_title((string) pathinfo((string) $file, PATHINFO_FILENAME));
            if ($normalizedName === 'event-full-width' || (str_contains($normalizedFile, 'event') && str_contains($normalizedFile, 'full'))) {
                return (string) $file;
            }
        }

        return '';
    }
}
