<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Contracts;

defined('ABSPATH') || exit;

interface PosterGenerator
{
    public function generate(string $prompt): string;
}
