<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;

defined('ABSPATH') || exit;

final class PosterSettings
{
    public function register(): void
    {
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . Config::POST_TYPE_EVENT,
            esc_html__('Poster Settings', 'dizzy-events-manager'),
            esc_html__('Poster Settings', 'dizzy-events-manager'),
            'manage_options',
            'dizzy-poster-settings',
            [$this, 'renderPage']
        );
    }

    public function registerSettings(): void
    {
        add_settings_section(
            'dizzy_poster_settings',
            'AI Poster Settings',
            static function (): void {
                echo '<p>Configure AI poster generation.</p>';
            },
            'dizzy-events'
        );

        register_setting(
            'dizzy-events',
            'dizzy_events_openai_api_key',
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        add_settings_field(
            'dizzy_events_openai_api_key',
            'OpenAI API Key',
            static function (): void {
                printf(
                    '<input type="password" class="regular-text" name="dizzy_events_openai_api_key" value="%s">',
                    esc_attr((string) get_option('dizzy_events_openai_api_key', ''))
                );
            },
            'dizzy-events',
            'dizzy_poster_settings'
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Poster Settings', 'dizzy-events-manager') . '</h1>';
        echo '<form method="post" action="' . esc_url(admin_url('options.php')) . '">';

        settings_fields('dizzy-events');
        do_settings_sections('dizzy-events');
        submit_button();

        echo '</form>';
        echo '</div>';
    }
}
