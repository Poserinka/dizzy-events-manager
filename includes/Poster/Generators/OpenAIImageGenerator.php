<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Generators;

use Dizzy\Events\Poster\Contracts\PosterGenerator;

defined('ABSPATH') || exit;

final class OpenAIImageGenerator implements PosterGenerator
{
    public function generate(string $prompt): string
    {
        $apiKey = (string) get_option('dizzy_events_openai_api_key', '');

        if ($apiKey === '') {
            return '';
        }

        $response = wp_remote_post(
            'https://api.openai.com/v1/images/generations',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'body' => wp_json_encode([
                    'model'  => 'gpt-image-1',
                    'prompt' => $prompt,
                    'size'   => '1024x1024',
                ]),
                'timeout' => 60,
            ]
        );

        if (is_wp_error($response)) {
            return '';
        }

        $body = json_decode(
            wp_remote_retrieve_body($response),
            true
        );

        return is_array($body) && isset($body['data'][0]['url'])
            ? (string) $body['data'][0]['url']
            : '';
    }
}
