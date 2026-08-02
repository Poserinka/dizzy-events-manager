<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Support;

defined('ABSPATH') || exit;

final class PosterFormats
{
    /** @return array<string, array{label:string,width:int,height:int,dpi:int,ai_size:string}> */
    public static function all(): array
    {
        return [
            'instagram_square' => ['label' => __('Instagram square (1080 × 1080)', 'dizzy-events-manager'), 'width' => 1080, 'height' => 1080, 'dpi' => 72, 'ai_size' => '1024x1024'],
            'instagram_portrait' => ['label' => __('Instagram portrait (1080 × 1350)', 'dizzy-events-manager'), 'width' => 1080, 'height' => 1350, 'dpi' => 72, 'ai_size' => '1024x1536'],
            'instagram_story' => ['label' => __('Instagram Story / Reel (1080 × 1920)', 'dizzy-events-manager'), 'width' => 1080, 'height' => 1920, 'dpi' => 72, 'ai_size' => '1024x1536'],
            'facebook_square' => ['label' => __('Facebook square (1080 × 1080)', 'dizzy-events-manager'), 'width' => 1080, 'height' => 1080, 'dpi' => 72, 'ai_size' => '1024x1024'],
            'facebook_landscape' => ['label' => __('Facebook landscape / link (1200 × 630)', 'dizzy-events-manager'), 'width' => 1200, 'height' => 630, 'dpi' => 72, 'ai_size' => '1536x1024'],
            'facebook_story' => ['label' => __('Facebook Story (1080 × 1920)', 'dizzy-events-manager'), 'width' => 1080, 'height' => 1920, 'dpi' => 72, 'ai_size' => '1024x1536'],
            'print_a4' => ['label' => __('Print A4 portrait (300 DPI)', 'dizzy-events-manager'), 'width' => 2480, 'height' => 3508, 'dpi' => 300, 'ai_size' => '1024x1536'],
        ];
    }

    /** @return array{label:string,width:int,height:int,dpi:int,ai_size:string} */
    public static function get(string $key): array
    {
        $formats = self::all();

        return $formats[$key] ?? $formats['instagram_square'];
    }

    public static function sanitize(string $key): string
    {
        $legacy = [
            'social_square' => 'instagram_square',
            'social_portrait' => 'instagram_portrait',
            'social_story' => 'instagram_story',
        ];
        $key = $legacy[$key] ?? $key;

        return isset(self::all()[$key]) ? $key : 'instagram_square';
    }
}
