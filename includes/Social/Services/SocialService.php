<?php

declare(strict_types=1);

namespace Dizzy\Events\Social\Services;

use Dizzy\Events\Enums\SocialStatus;
use Dizzy\Events\Social\Repositories\SocialRepository;
use Dizzy\Events\Social\Models\SocialPost;

defined('ABSPATH') || exit;

final class SocialService
{
    public function __construct(
        private readonly SocialRepository $repository,
    ) {
    }

    public function create(array $data): SocialPost
    {
        $data['status'] ??= SocialStatus::Draft->value;

        return $this->repository->create($data);
    }
}
