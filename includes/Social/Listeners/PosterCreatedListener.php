<?php

declare(strict_types=1);

namespace Dizzy\Events\Social\Listeners;

use Dizzy\Events\Poster\Models\Poster;
use Dizzy\Events\Social\Services\SocialService;

defined('ABSPATH') || exit;

final class PosterCreatedListener
{
    public function __construct(
        private readonly SocialService $service,
    ) {
    }

    public function register(): void
    {
        add_action(
            'dizzy_events_poster_created',
            [$this, 'handle']
        );
    }

    public function handle(Poster $poster): void
    {
        $this->service->create([
            'title' => 'Generated Event Poster',
            'content' => $poster->prompt,
            'image_url' => $poster->imageUrl,
        ]);
    }
}
