<?php

declare(strict_types=1);

namespace Dizzy\SocialMedia\Poster\Repositories {
    final class PosterRepository
    {
        public static ?object $poster = null;

        public function findByEvent(int $eventId): ?object
        {
            return self::$poster;
        }
    }
}

namespace {
    define('ABSPATH', __DIR__ . '/');

    function wp_get_attachment_url(int $id): string|false
    {
        return $id === 7 ? 'https://example.com/poster.jpg' : false;
    }

    function get_the_post_thumbnail_url(int $id, string $size): string|false
    {
        return $id === 42 ? 'https://example.com/featured.jpg' : false;
    }

    function wp_http_validate_url(string $url): string|false
    {
        return str_starts_with($url, 'https://example.com/') ? $url : false;
    }

    require dirname(__DIR__) . '/modules/emails/includes/EventImage.php';

    $repository = \Dizzy\SocialMedia\Poster\Repositories\PosterRepository::class;
    $repository::$poster = (object) ['attachmentId' => 7, 'imageUrl' => ''];
    if (\Dizzy\Emails\EventImage::url(42) !== 'https://example.com/poster.jpg') {
        throw new \RuntimeException('Generated poster should have priority.');
    }

    $repository::$poster = null;
    if (\Dizzy\Emails\EventImage::url(42) !== 'https://example.com/featured.jpg') {
        throw new \RuntimeException('Featured image should be the fallback.');
    }

    if (\Dizzy\Emails\EventImage::url(0) !== '' || \Dizzy\Emails\EventImage::url(43) !== '') {
        throw new \RuntimeException('Missing event images should resolve to an empty URL.');
    }

    echo "Event image fallback smoke test passed.\n";
}
