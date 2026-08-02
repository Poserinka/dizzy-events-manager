<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

use Dizzy\Events\Enums\ReservationStatus;
use Dizzy\Events\Mail\Services\MailService;
use Dizzy\Events\Repositories\OccurrenceRepository;
use RuntimeException;

defined('ABSPATH') || exit;

final class ReservationService
{
    private const MAX_GUESTS = 100;

    public function __construct(
        private readonly ReservationRepository $repository,
        private readonly MailService $mailer,
        private readonly OccurrenceRepository $occurrences,
    ) {
    }

    public function create(array $data): int
    {
        if (empty($data['event_id'])) {
            throw new RuntimeException('Event ID is required.');
        }

        $eventId = (int) $data['event_id'];
        $occurrenceId = (int) ($data['occurrence_id'] ?? 0);

        if ($occurrenceId < 0) {
            throw new RuntimeException('Invalid occurrence ID.');
        }

        if ($occurrenceId > 0 && ! $this->isBookableOccurrence($eventId, $occurrenceId)) {
            throw new RuntimeException('The selected event date is not available.');
        }

        if (trim((string) ($data['name'] ?? '')) === '') {
            throw new RuntimeException('Name is required.');
        }

        $email = (string) ($data['email'] ?? '');

        if ($email === '' || ! is_email($email)) {
            throw new RuntimeException('A valid email address is required.');
        }

        $guests = (int) ($data['guests'] ?? 0);

        if ($guests < 1 || $guests > self::MAX_GUESTS) {
            throw new RuntimeException(
                sprintf('Guest count must be between 1 and %d.', self::MAX_GUESTS)
            );
        }

        $data['event_id'] = $eventId;
        $data['occurrence_id'] = $occurrenceId;
        $data['guests'] = $guests;
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

    private function isBookableOccurrence(int $eventId, int $occurrenceId): bool
    {
        $grouped = $this->occurrences->findUpcomingByEventIds([$eventId]);

        foreach ($grouped[$eventId] ?? [] as $occurrence) {
            if ($occurrence->id === $occurrenceId) {
                return true;
            }
        }

        return false;
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
