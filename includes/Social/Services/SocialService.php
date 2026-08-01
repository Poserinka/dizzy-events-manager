<?php

declare(strict_types=1);

namespace Dizzy\Events\Social\Services;

use Dizzy\Events\Enums\SocialStatus;

defined('ABSPATH') || exit;

final class SocialService
{
    public function create(array $data): array
    {
        $data['status'] ??= SocialStatus::Draft->value;

        return $data;
    }
}
