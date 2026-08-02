<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Repositories;

use Dizzy\Events\Poster\Models\Poster;

defined('ABSPATH') || exit;

final class PosterRepository
{
    public function create(array $data): Poster
    {
        return new Poster(
            (int) ($data['id'] ?? 0),
            isset($data['event_id']) ? (int) $data['event_id'] : null,
            (string) ($data['prompt'] ?? ''),
            (string) ($data['image_url'] ?? ''),
            (string) ($data['status'] ?? 'draft'),
        );
    }
}
