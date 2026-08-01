<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

use Dizzy\Events\Mail\Mailer;

defined('ABSPATH') || exit;

final class ReservationMailer
{
    public function __construct(
        private readonly Mailer $mailer
    ) {
    }

    public function sendConfirmation(string $email): bool
    {
        return $this->mailer->send(
            $email,
            'Reservation received',
            'Your reservation request has been received.'
        );
    }

    public function sendNotification(string $email): bool
    {
        return $this->mailer->send(
            $email,
            'New reservation received',
            'A new reservation has been created.'
        );
    }
}
