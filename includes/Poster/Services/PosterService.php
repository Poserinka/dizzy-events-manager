<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Services;

use Dizzy\Events\Poster\Contracts\PosterGenerator;
use Dizzy\Events\Poster\Models\Poster;
use Dizzy\Events\Poster\Repositories\PosterRepository;

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
        if (empty($data['image_url'])) {
            $data['image_url'] = $this->generator->generate(
                (string) ($data['prompt'] ?? '')
            );
        }

        if (! empty($data['image_url'])) {
            $data['attachment_id'] = $this->importMedia(
                (string) $data['image_url'],
                (int) ($data['event_id'] ?? 0)
            );
        }

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
