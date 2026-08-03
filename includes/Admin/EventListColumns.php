<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;

defined('ABSPATH') || exit;

final class EventListColumns
{
    private const COLUMN = 'dizzy_featured_image';

    public function register(): void
    {
        add_filter('manage_' . Config::POST_TYPE_EVENT . '_posts_columns', [$this, 'addColumn']);
        add_action('manage_' . Config::POST_TYPE_EVENT . '_posts_custom_column', [$this, 'renderColumn'], 10, 2);
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
                $result[self::COLUMN] = __('Featured Image', 'dizzy-events-manager');
            }
        }

        return $result;
    }

    public function renderColumn(string $column, int $postId): void
    {
        if ($column !== self::COLUMN) {
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

    public function printStyles(): void
    {
        $screen = get_current_screen();

        if (! $screen || $screen->post_type !== Config::POST_TYPE_EVENT) {
            return;
        }
        ?>
        <style>
            .wp-list-table .column-dizzy_featured_image { width: 120px; text-align: center; }
            .wp-list-table .dizzy-event-list-thumbnail { width: 100px; height: 100px; object-fit: cover; border-radius: 3px; }
            @media screen and (max-width: 782px) {
                .wp-list-table .column-dizzy_featured_image { display: none; }
            }
        </style>
        <?php
    }
}
