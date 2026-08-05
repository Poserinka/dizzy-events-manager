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

    /** @return array{taxonomy:string,label:string,terms:array<int,array{id:int,name:string,selected:bool}>} */
    private function taxonomyData(string $taxonomy, string $label, int $postId): array
    {
        $selected = $postId > 0 ? wp_get_object_terms($postId, $taxonomy, ['fields' => 'ids']) : [];
        $selectedIds = is_wp_error($selected) ? [] : array_map('intval', $selected);
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);

        return [
            'taxonomy' => $taxonomy,
            'label' => $label,
            'terms' => is_wp_error($terms) ? [] : array_map(
                static fn (\WP_Term $term): array => [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'selected' => in_array($term->term_id, $selectedIds, true),
                ],
                $terms
            ),
        ];
    }
}
