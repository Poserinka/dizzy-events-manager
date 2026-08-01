<?php

declare(strict_types=1);

namespace Dizzy\Events\Social\Models;

defined('ABSPATH') || exit;

readonly class SocialPost
{
    public function __construct(
        public int $id,
        public ?int $eventId,
        public string $platform,
        public string $content,
        public string $status,
    ) {
    }
}
