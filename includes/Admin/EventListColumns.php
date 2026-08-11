<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;

defined('ABSPATH') || exit;

final class EventListColumns
{
    private const IMAGE_COLUMN = 'dizzy_featured_image';
    private const DATE_COLUMN = 'dizzy_event_date';

    public function register(): void
    {
        add_filter('manage_' . Config::POST_TYPE_EVENT . '_posts_columns', [$this, 'addColumn']);
        add_action('manage_' . Config::POST_TYPE_EVENT . '_posts_custom_column', [$this, 'renderColumn'], 10, 2);
        add_filter('manage_edit-' . Config::POST_TYPE_EVENT . '_sortable_columns', [$this, 'sortableColumns']);
        add_filter('posts_clauses', [$this, 'sortByEventDate'], 10, 2);
        add_action('admin_head-edit.php', [$this, 'printStyles']);
    }

    /**
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public function addColumn(array $columns): array
    {
        $result = [];

        foreach ($columns as $key => $label) {
            $result[$key] = $label;

            if ($key === 'title') {
                $result[self::IMAGE_COLUMN] = __('Featured Image', 'dizzy-events-manager');
                $result[self::DATE_COLUMN] = __('Event Date', 'dizzy-events-manager');
            }
        }

        return $result;
    }

    public function renderColumn(string $column, int $postId): void
    {
        if ($column === self::DATE_COLUMN) {
            global $wpdb;

            $start = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT start_datetime FROM {$wpdb->prefix}dizzy_event_occurrences WHERE event_id = %d ORDER BY start_datetime ASC LIMIT 1",
                    $postId
                )
            );

            if (! is_string($start) || $start === '') {
                echo '<span aria-hidden="true">&mdash;</span>';
                return;
            }

            $timestamp = strtotime($start);
            echo $timestamp === false
                ? esc_html($start)
                : esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $timestamp, wp_timezone()));
            return;
        }

        if ($column !== self::IMAGE_COLUMN) {
            return;
        }

        if (! has_post_thumbnail($postId)) {
            echo '<span aria-hidden="true">&mdash;</span>';
            echo '<span class="screen-reader-text">' . esc_html__('No featured image', 'dizzy-events-manager') . '</span>';
            return;
        }

        echo get_the_post_thumbnail(
            $postId,
            [100, 100],
            [
                'class' => 'dizzy-event-list-thumbnail',
                'loading' => 'lazy',
                'alt' => '',
            ]
        );
    }

    /**
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public function sortableColumns(array $columns): array
    {
        $columns[self::DATE_COLUMN] = self::DATE_COLUMN;

        return $columns;
    }

    /**
     * @param array<string, string> $clauses
     * @return array<string, string>
     */
    public function sortByEventDate(array $clauses, \WP_Query $query): array
    {
        if (! is_admin() || ! $query->is_main_query() || $query->get('post_type') !== Config::POST_TYPE_EVENT || $query->get('orderby') !== self::DATE_COLUMN) {
            return $clauses;
        }

        global $wpdb;

        $alias = 'dizzy_event_list_dates';
        $table = $wpdb->prefix . 'dizzy_event_occurrences';
        $clauses['join'] .= " LEFT JOIN (SELECT event_id, MIN(start_datetime) AS event_date FROM {$table} GROUP BY event_id) AS {$alias} ON {$wpdb->posts}.ID = {$alias}.event_id";
        $order = strtoupper((string) $query->get('order')) === 'DESC' ? 'DESC' : 'ASC';
        $clauses['orderby'] = "{$alias}.event_date IS NULL ASC, {$alias}.event_date {$order}, {$wpdb->posts}.ID {$order}";

        return $clauses;
    }

    public function printStyles(): void
    {
        $screen = get_current_screen();

        if (! $screen || $screen->post_type !== Config::POST_TYPE_EVENT) {
            return;
        }
        ?>
        <style>
            .wp-list-table .column-dizzy_featured_image { width: 120px; text-align: center; }
            .wp-list-table .column-dizzy_event_date { width: 180px; }
            .wp-list-table .dizzy-event-list-thumbnail { width: 100px; height: 100px; object-fit: cover; border-radius: 3px; }
            @media screen and (max-width: 782px) {
                .wp-list-table .column-dizzy_featured_image { display: none; }
            }
        </style>
        <?php
    }
}

