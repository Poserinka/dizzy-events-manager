<?php

declare(strict_types=1);

namespace Dizzy\Events\Services;

use InvalidArgumentException;

defined('ABSPATH') || exit;

/**
 * Event formatting and default value helpers.
 */
final class EventFormattingService
{
    public function defaultVenue(): string
    {
        return 'Jazzcafé Dizzy';
    }

    public function defaultAddress(): string
    {
        return "'s-Gravendijkwal 127, 3021 EK Rotterdam";
    }

    public function defaultMapsUrl(): string
    {
        return 'https://maps.app.goo.gl/t73PkgDRtb6RvKFMA';
    }

    public function formatPrice(float $price): string
    {
        if ($price <= 0) {
            return '';
        }

        return '€' . number_format($price, 2, ',', '.');
    }

    /**
     * @return array<int, string>
     */
    public function timeOptions(): array
    {
        $times = [];

        for ($hour = 14; $hour <= 23; $hour++) {
            $times[] = sprintf('%02d:00', $hour);
            $times[] = sprintf('%02d:30', $hour);
        }

        $times[] = '00:00';

        return $times;
    }
}
