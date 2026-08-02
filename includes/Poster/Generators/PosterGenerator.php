<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Generators;

use Dizzy\Events\Poster\Services\PosterService;

defined('ABSPATH') || exit;

final class PosterGenerator
{
    public function __construct(
        private readonly PosterService $service,
    ) {
    }

    public function generate(array $data)
    {
        return $this->service->create($data);
    }
}
