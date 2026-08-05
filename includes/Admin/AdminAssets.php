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
                'taxonomies' => [
                    $this->taxonomyData(Config::TAX_ARTIST, __('Artists', 'dizzy-events-manager'), $postId),
                    $this->taxonomyData(Config::TAX_VENUE, __('Venues', 'dizzy-events-manager'), $postId),
                    $this->taxonomyData(Config::TAX_TAG, __('Tags', 'dizzy-events-manager'), $postId),
                ],
            ]
        );
    }

    /** @return array{taxonomy:string,label:string,terms:array<int,array<string,mixed>>} */
    private function taxonomyData(string $taxonomy, string $label, int $postId): array
    {
        $selected = $postId > 0 ? wp_get_object_terms($postId, $taxonomy, ['fields' => 'ids']) : [];
        $selectedIds = is_wp_error($selected) ? [] : array_map('intval', $selected);
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);

        $termData = is_wp_error($terms) ? [] : array_map(
            static function (\WP_Term $term) use ($taxonomy, $selectedIds, $postId): array {
                $selected = in_array($term->term_id, $selectedIds, true);
                if ($postId === 0 && $taxonomy === Config::TAX_VENUE && sanitize_title($term->name) === 'jazzcafe-dizzy') {
                    $selected = true;
                }

                $imageId = $taxonomy === Config::TAX_ARTIST
                    ? absint(get_term_meta($term->term_id, '_dizzy_artist_image_id', true))
                    : 0;

                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'selected' => $selected,
                    'role' => $taxonomy === Config::TAX_ARTIST ? $term->description : '',
                    'contact' => $taxonomy === Config::TAX_ARTIST
                        ? (string) get_term_meta($term->term_id, '_dizzy_artist_contact', true)
                        : '',
                    'imageId' => $imageId,
                    'imageUrl' => $imageId > 0 ? (string) wp_get_attachment_image_url($imageId, 'medium') : '',
                ];
            },
            $terms
        );

        return [
            'taxonomy' => $taxonomy,
            'label' => $label,
            'terms' => $termData,
        ];
    }
}
