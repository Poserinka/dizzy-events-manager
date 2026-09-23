<?php

declare(strict_types=1);

namespace Dizzy\Emails;

defined('ABSPATH') || exit;

final class EventImage
{
    public static function url(int $eventId): string
    {
        if ($eventId <= 0) {
            return '';
        }

        if (class_exists(\Dizzy\SocialMedia\Poster\Repositories\PosterRepository::class)) {
            try {
                $poster = (new \Dizzy\SocialMedia\Poster\Repositories\PosterRepository())->findByEvent($eventId);
                if ($poster !== null) {
                    $url = $poster->attachmentId > 0 ? wp_get_attachment_url($poster->attachmentId) : '';
                    $url = is_string($url) && $url !== '' ? $url : $poster->imageUrl;
                    if (is_string($url) && wp_http_validate_url($url)) {
                        return $url;
                    }
                }
            } catch (\Throwable) {
                // The Social Media module may be unavailable; use the featured image below.
            }
        }

        $url = get_the_post_thumbnail_url($eventId, 'full');
        return is_string($url) && wp_http_validate_url($url) ? $url : '';
    }
}
