<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Renderers;

defined('ABSPATH') || exit;

final class PosterRenderer
{
    public function render(string $template, array $data = []): string
    {
        return $template;
    }
}
