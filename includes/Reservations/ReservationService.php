<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

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
        private readonly ReservationRepository $repository
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

        return $this->repository->save($data);
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
