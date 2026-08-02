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

        $poster = $this->repository->create($data);

        do_action(
            'dizzy_events_poster_created',
            $poster
        );

        return $poster;
    }
}
