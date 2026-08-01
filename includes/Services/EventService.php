<?php

declare(strict_types=1);

namespace Dizzy\Events\Services;

use Dizzy\Events\Enums\EventStatus;
use Dizzy\Events\Models\Event;
use Dizzy\Events\Models\EventDetails;
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
    /**
     * Create the event service.
     */
    public function __construct(
        private EventRepository $eventRepository,
        private OccurrenceRepository $occurrenceRepository,
    ) {
    }

    /**
     * Get a published event with occurrences and details.
     *
     * @return array{
     *     event: Event,
     *     occurrences: array<Occurrence>,
     *     details: EventDetails
     * }|null
     */
    public function getEvent(int $eventId): ?array
    {
        $event = $this->eventRepository->findById($eventId);

        if (
            $event === null
            || $event->status !== EventStatus::PUBLISHED
        ) {
            return null;
        }

        return [
            'event'       => $event,
            'occurrences' => $this->occurrenceRepository->findByEventId($eventId),
            'details'     => $this->getEventDetails($eventId),
        ];
    }

    /**
     * Get event details.
     */
    public function getEventDetails(int $eventId): EventDetails
    {
        return EventDetails::fromMeta(get_post_meta($eventId));
    }

    /**
     * Get upcoming event presentation data.
     *
     * @return array<int, array{
     *     event: Event,
     *     details: EventDetails,
     *     occurrences: array<Occurrence>
     * }>
     */
    public function getUpcomingEventData(int $limit = 20): array
    {
        $events = $this->getUpcomingEvents($limit);

        if ($events === []) {
            return [];
        }

        $eventIds = array_map(
            static function (Event $event): int {
                return $event->id;
            },
            $events
        );

        $occurrencesByEvent = $this->occurrenceRepository
            ->findUpcomingByEventIds($eventIds);
        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'event'       => $event,
                'details'     => $this->getEventDetails($event->id),
                'occurrences' => $occurrencesByEvent[$event->id] ?? [],
            ];
        }

        return $data;
    }

    /**
     * Get upcoming occurrences in chronological order.
     *
     * @return array<Occurrence>
     */
    public function getUpcomingOccurrences(int $eventId): array
    {
        return $this->filterAndSortOccurrences(
            $this->occurrenceRepository->findByEventId($eventId),
            true
        );
    }

    /**
     * Get past occurrences in chronological order.
     *
     * @return array<Occurrence>
     */
    public function getPastOccurrences(int $eventId): array
    {
        return $this->filterAndSortOccurrences(
            $this->occurrenceRepository->findByEventId($eventId),
            false
        );
    }

    /**
     * Get upcoming published events ordered by next occurrence.
     *
     * @return array<Event>
     */
    public function getUpcomingEvents(int $limit = 20): array
    {
        $eventIds = $this->occurrenceRepository->findUpcomingEventIds($limit);

        return $this->eventRepository->findPublishedByIds($eventIds);
    }

    /**
     * Check whether event has future occurrences.
     */
    public function hasUpcomingOccurrences(int $eventId): bool
    {
        $occurrences = $this->occurrenceRepository->findByEventId($eventId);

        foreach ($occurrences as $occurrence) {
            if ($occurrence->isUpcoming()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter occurrences by temporal state and sort them chronologically.
     *
     * @param array<Occurrence> $occurrences Occurrence records.
     *
     * @return array<Occurrence>
     */
    private function filterAndSortOccurrences(
        array $occurrences,
        bool $upcoming
    ): array {
        $filtered = array_values(
            array_filter(
                $occurrences,
                static function (Occurrence $occurrence) use ($upcoming): bool {
                    return $occurrence->isUpcoming() === $upcoming;
                }
            )
        );

        usort(
            $filtered,
            static function (
                Occurrence $first,
                Occurrence $second
            ): int {
                $comparison = $first->startDateTime <=> $second->startDateTime;

                return $comparison !== 0
                    ? $comparison
                    : $first->id <=> $second->id;
            }
        );

        return $filtered;
    }
}
