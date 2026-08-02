<?php

declare(strict_types=1);

namespace Dizzy\Events\Reports\Models;

defined('ABSPATH') || exit;

final readonly class Report
{
    public function __construct(
        public string $type,
        public array $data = []
    ) {
    }
}
