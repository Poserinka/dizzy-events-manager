<?php

declare(strict_types=1);

namespace Dizzy\Events\Services;

use Dizzy\Events\Models\Event;
use Dizzy\Events\Models\Occurrence;
use Dizzy\Events\Repositories\EventRepository;
use Dizzy\Events\Repositories\OccurrenceRepository;

defined('ABSPATH') || exit;

/**
 * Event application service.
 *
 * Handles event-related business operations.
 *
 * @package Dizzy\Events\Services
 */
final class EventService
{
    public function __construct(
        private EventRepository $eventRepository,
        private OccurrenceRepository $occurrenceRepository,
    ) {
    }

    /**
     * Get event with occurrences.
     *
     * @return array{
     *     event: Event,
     *     occurrences: array<Occurrence>
     * }|null
     */
    public function getEvent(
        int $eventId
    ): ?array {

        $event = $this->eventRepository->findById(
            $eventId
        );

        if ($event === null) {
            return null;
        }

        return [
            'event' => $event,

            'occurrences' =>
                $this->occurrenceRepository
                    ->findByEventId($eventId),
        ];
    }

    /**
     * Get upcoming published events.
     *
     * @return array<Event>
     */
    public function getUpcomingEvents(
        int $limit = 20
    ): array {

        return $this->eventRepository
            ->findPublished($limit);
    }

    /**
     * Check whether event has future occurrences.
     */
    public function hasUpcomingOccurrences(
        int $eventId
    ): bool {

        $occurrences =
            $this->occurrenceRepository
                ->findByEventId($eventId);

        foreach ($occurrences as $occurrence) {

            if ($occurrence->isUpcoming()) {
                return true;
            }
        }

        return false;
    }
}