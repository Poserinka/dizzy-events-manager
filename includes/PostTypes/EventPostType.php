<?php

declare(strict_types=1);

namespace Dizzy\Events\PostTypes;

defined('ABSPATH') || exit;

/**
 * Registers Event custom post type.
 *
 * @package Dizzy\Events\PostTypes
 */
final class EventPostType
{
    /**
     * Register post type.
     */
    public function register(): void
    {
        register_post_type(
            'event',
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
    }
}