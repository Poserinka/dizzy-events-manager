<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Services;

use Dizzy\Events\Poster\Contracts\PosterGenerator;
use Dizzy\Events\Poster\Models\Poster;
use Dizzy\Events\Poster\Repositories\PosterRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class PosterService
{
    public function __construct(
        private readonly PosterRepository $repository,
        private readonly PosterGenerator $generator,
    ) {
    }

    public function create(array $data): Poster
    {
        $imageUrl = isset($data['image_url']) && is_string($data['image_url'])
            ? trim($data['image_url'])
            : '';

        if ($imageUrl === '') {
            $data['image_url'] = $this->generator->generate(
                (string) ($data['prompt'] ?? '')
            );

            $imageUrl = trim((string) $data['image_url']);
        }

        if ($imageUrl === '') {
            throw new RuntimeException('Poster generation returned no image.');
        }

        $data['image_url'] = $imageUrl;
        $data['attachment_id'] = $this->importMedia(
            $imageUrl,
            (int) ($data['event_id'] ?? 0)
        );

        $poster = $this->repository->create($data);

        do_action(
            'dizzy_events_poster_created',
            $poster
        );

        return $poster;
    }

    private function importMedia(string $url, int $postId): int
    {
        if (! function_exists('media_sideload_image')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $attachmentId = media_sideload_image(
            $url,
            $postId,
            null,
            'id'
        );

        return is_wp_error($attachmentId) ? 0 : (int) $attachmentId;
    }
}
