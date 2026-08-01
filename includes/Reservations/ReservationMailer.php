<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

defined('ABSPATH') || exit;

final class ReservationMailer
{
    public function sendConfirmation(int $reservationId): bool
    {
        return $reservationId > 0;
    }

    public function sendNotification(int $reservationId): bool
    {
        return $reservationId > 0;
    }
}
