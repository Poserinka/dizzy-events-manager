<?php

declare(strict_types=1);

namespace Dizzy\Events\PostTypes;

use Dizzy\Events\Core\Config;

defined('ABSPATH') || exit;

/**
 * Registers Event custom post type.
 *
 * @package Dizzy\Events\PostTypes
 */
final class EventPostType
{
    private const GENRE_MIGRATION_OPTION = 'dizzy_events_genre_taxonomy_migrated';

    /**
     * Register post type.
     */
    public function register(): void
    {
        register_post_type(
            Config::POST_TYPE_EVENT,
            [
                'labels' => [
                    'name' =>
                        __('Events', 'dizzy-events-manager'),

                    'singular_name' =>
                        __('Event', 'dizzy-events-manager'),

                    'add_new' =>
                        __('Add New Event', 'dizzy-events-manager'),

                    'edit_item' =>
                        __('Edit Event', 'dizzy-events-manager'),

                    'view_item' =>
                        __('View Event', 'dizzy-events-manager'),
                ],

                'public' => true,

                'show_ui' => true,

                'menu_icon' => 'dashicons-calendar-alt',

                'supports' => [
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                ],

                'has_archive' => true,

                'rewrite' => [
                    'slug' => 'events',
                ],

                'show_in_rest' => true,
            ]
        );

        $this->registerTaxonomies();
        $this->migrateLegacyGenres();
    }

    private function registerTaxonomies(): void
    {
        register_taxonomy(
            Config::TAX_CATEGORY,
            [Config::POST_TYPE_EVENT],
            [
                'labels' => [
                    'name' => __('Event Categories', 'dizzy-events-manager'),
                    'singular_name' => __('Event Category', 'dizzy-events-manager'),
                    'search_items' => __('Search Event Categories', 'dizzy-events-manager'),
                    'all_items' => __('All Event Categories', 'dizzy-events-manager'),
                    'edit_item' => __('Edit Event Category', 'dizzy-events-manager'),
                    'add_new_item' => __('Add Event Category', 'dizzy-events-manager'),
                ],
                'public' => true,
                'hierarchical' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'rewrite' => ['slug' => 'event-category'],
            ]
        );

        register_taxonomy(
            Config::TAX_GENRE,
            [Config::POST_TYPE_EVENT],
            [
                'labels' => [
                    'name' => __('Genres', 'dizzy-events-manager'),
                    'singular_name' => __('Genre', 'dizzy-events-manager'),
                    'search_items' => __('Search Genres', 'dizzy-events-manager'),
                    'all_items' => __('All Genres', 'dizzy-events-manager'),
                    'edit_item' => __('Edit Genre', 'dizzy-events-manager'),
                    'add_new_item' => __('Add Genre', 'dizzy-events-manager'),
                ],
                'public' => true,
                'hierarchical' => false,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'rewrite' => ['slug' => 'event-genre'],
            ]
        );
    }

    private function migrateLegacyGenres(): void
    {
        if (get_option(self::GENRE_MIGRATION_OPTION, '') === '1') {
            return;
        }

        $eventIds = get_posts([
            'post_type' => Config::POST_TYPE_EVENT,
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => '_dizzy_genre',
            'no_found_rows' => true,
        ]);
        $migrationSucceeded = true;

        foreach ($eventIds as $eventId) {
            if (has_term('', Config::TAX_GENRE, (int) $eventId)) {
                continue;
            }

            $legacyGenre = trim((string) get_post_meta((int) $eventId, '_dizzy_genre', true));

            if ($legacyGenre === '') {
                continue;
            }

            $genres = array_values(array_filter(array_map('trim', explode(',', $legacyGenre))));

            if ($genres !== []) {
                $result = wp_set_object_terms((int) $eventId, $genres, Config::TAX_GENRE);

                if (is_wp_error($result)) {
                    $migrationSucceeded = false;
                }
            }
        }

        if ($migrationSucceeded) {
            flush_rewrite_rules(false);
            update_option(self::GENRE_MIGRATION_OPTION, '1', false);
        }
    }
}
