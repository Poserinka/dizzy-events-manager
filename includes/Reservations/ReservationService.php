<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

use Dizzy\Events\Mail\Mailer;
use RuntimeException;

defined('ABSPATH') || exit;

/**
 * Reservation business logic service.
 *
 * @package Dizzy\Events\Reservations
 */
final class ReservationService
{
    public function __construct(
        private readonly ReservationRepository $repository,
        private readonly Mailer $mailer
    ) {
    }

    /**
     * Create reservation.
     *
     * @param array<string,mixed> $data
     */
    public function create(array $data): int
    {
        if (empty($data['event_id'])) {
            throw new RuntimeException('Event ID is required.');
        }

        if (
            isset($data['occurrence_id'])
            && (int) $data['occurrence_id'] < 0
        ) {
            throw new RuntimeException('Invalid occurrence ID.');
        }

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

    /**
     * Cancel reservation.
     */
    public function cancel(int $reservationId): bool
    {
        if ($reservationId <= 0) {
            return false;
        }

        return $this->repository->delete($reservationId);
    }
}
