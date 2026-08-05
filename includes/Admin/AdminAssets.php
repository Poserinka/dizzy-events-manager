<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;

defined('ABSPATH') || exit;

final class AdminAssets
{
    public function register(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(string $hook): void
    {
        if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();

        if (! $screen || $screen->post_type !== Config::POST_TYPE_EVENT) {
            return;
        }

        wp_enqueue_style(
            'dizzy-events-admin',
            DIZZY_EVENTS_URL . 'assets/css/admin.css',
            [],
            DIZZY_EVENTS_VERSION
        );

        wp_enqueue_script(
            'dizzy-events-editor',
            DIZZY_EVENTS_URL . 'assets/js/event-editor.js',
            [],
            DIZZY_EVENTS_VERSION,
            true
        );
        wp_enqueue_media();

        $postId = isset($_GET['post']) ? absint($_GET['post']) : 0;
        wp_localize_script(
            'dizzy-events-editor',
            'dizzyEventEditorData',
            [
                'nonce' => wp_create_nonce('dizzy_event_relations_save'),
                'fields' => $this->eventFields($postId),
            ]
        );
    }

    /** @return array{artists:array<int,array<string,mixed>>,venue:string,tags:string} */
    private function eventFields(int $postId): array
    {
        $artists = $postId > 0 ? get_post_meta($postId, '_dizzy_event_artists', true) : [];
        if (! is_array($artists) || $artists === []) {
            $terms = $postId > 0 ? wp_get_post_terms($postId, Config::TAX_ARTIST) : [];
            $artists = is_wp_error($terms) ? [] : array_map(static function (\WP_Term $term): array {
                $imageId = absint(get_term_meta($term->term_id, '_dizzy_artist_image_id', true));
                return [
                    'name' => $term->name,
                    'role' => $term->description,
                    'contact' => (string) get_term_meta($term->term_id, '_dizzy_artist_contact', true),
                    'imageId' => $imageId,
                    'imageUrl' => $imageId > 0 ? (string) wp_get_attachment_image_url($imageId, 'medium') : '',
                ];
            }, $terms);
        }

        $venue = $postId > 0 ? trim((string) get_post_meta($postId, '_dizzy_event_venue_name', true)) : '';
        if ($venue === '' && $postId > 0) {
            $names = wp_get_post_terms($postId, Config::TAX_VENUE, ['fields' => 'names']);
            if (! is_wp_error($names) && isset($names[0])) $venue = (string) $names[0];
        }

        $tags = $postId > 0 ? trim((string) get_post_meta($postId, '_dizzy_event_tags', true)) : '';
        if ($tags === '' && $postId > 0) {
            $names = wp_get_post_terms($postId, Config::TAX_TAG, ['fields' => 'names']);
            if (! is_wp_error($names)) $tags = implode(', ', array_map('strval', $names));
        }

        return [
            'artists' => array_values($artists),
            'venue' => $venue !== '' ? $venue : 'Jazzcafe Dizzy',
            'tags' => $tags,
        ];
    }
}
