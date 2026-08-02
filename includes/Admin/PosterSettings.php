<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

defined('ABSPATH') || exit;

final class PosterSettings
{
    public function register(): void
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
}
