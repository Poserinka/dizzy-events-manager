<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

use Dizzy\Events\Enums\ReservationStatus;
use Dizzy\Events\Mail\Services\MailService;
use RuntimeException;

defined('ABSPATH') || exit;

final class ReservationService
{
    public function __construct(
        private readonly ReservationRepository $repository,
        private readonly MailService $mailer
    ) {
    }

    public function create(array $data): int
    {
        if (empty($data['event_id'])) {
            throw new RuntimeException('Event ID is required.');
        }

        if (isset($data['occurrence_id']) && (int) $data['occurrence_id'] < 0) {
            throw new RuntimeException('Invalid occurrence ID.');
        }

        $data['status'] ??= ReservationStatus::Pending->value;

        $reservationId = $this->repository->save($data);

        if (! empty($data['email']) && is_string($data['email'])) {
            $this->mailer->send(
                $data['email'],
                'Reservation received',
                'Your reservation request has been received.'
            );
        }

        return $reservationId;
    }

    public function confirm(int $reservationId): bool
    {
        return $this->repository->update(
            $reservationId,
            ['status' => ReservationStatus::Confirmed->value]
        );
    }

    public function cancel(int $reservationId): bool
    {
        return $this->repository->update(
            $reservationId,
            ['status' => ReservationStatus::Cancelled->value]
        );
    }
}
