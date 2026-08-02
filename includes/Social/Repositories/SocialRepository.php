<?php

declare(strict_types=1);

namespace Dizzy\Events\Social\Repositories;

use Dizzy\Events\Social\Models\SocialPost;

defined('ABSPATH') || exit;

final class SocialRepository
{
    /**
     * Hydrate a social post model.
     *
     * Persistence can be connected when the social database layer is enabled.
     */
    public function create(array $data): SocialPost
    {
        return new SocialPost(
            (int) ($data['id'] ?? 0),
            isset($data['event_id']) ? (int) $data['event_id'] : null,
            (string) ($data['platform'] ?? ''),
            (string) ($data['content'] ?? ''),
            (string) ($data['status'] ?? 'draft'),
        );
    }
}
