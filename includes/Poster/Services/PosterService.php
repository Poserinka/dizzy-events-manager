<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Services;

use Dizzy\Events\Poster\Models\Poster;
use Dizzy\Events\Poster\Repositories\PosterRepository;

defined('ABSPATH') || exit;

final class PosterService
{
    public function __construct(
        private readonly PosterRepository $repository,
    ) {
    }

    public function create(array $data): Poster
    {
        return $this->repository->create($data);
    }
}
