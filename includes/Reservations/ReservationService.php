<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

defined('ABSPATH') || exit;

final class ReservationService
{
    public function create(array $data): bool
    {
        return true;
    }

    public function cancel(int $reservationId): bool
    {
        return $reservationId > 0;
    }
}
