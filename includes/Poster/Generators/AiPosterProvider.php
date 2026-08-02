<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Generators;

defined('ABSPATH') || exit;

final class AiPosterProvider
{
    public function generate(array $prompt): array
    {
        return $prompt;
    }
}
