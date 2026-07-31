<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use Dizzy\Events\Enums\OccurrenceStatus;
use Dizzy\Events\Models\Occurrence;

defined('ABSPATH') || exit;

/**
 * Repository for event occurrences.
 *
 * Handles database operations related to event occurrences.
 *
 * @package Dizzy\Events\Repositories
 */
final class OccurrenceRepository extends AbstractRepository
{
    /**
     * Database table key.
     */
    protected string $table = 'occurrences';

    /**
     * Model handled by this repository.
     *
     * @return class-string<Occurrence>
     */
    protected function modelClass(): string
    {
        return Occurrence::class;
    }

    /**
     * Find occurrence by ID.
     */
    public function findById(
        int $id
    ): ?Occurrence {

        return $this->findOne(
            "
            SELECT *
            FROM {$this->table()}
            WHERE id = %d
            LIMIT 1
            ",
            [
                $id,
            ]
        );
    }

    /**
     * Find upcoming occurrences.
     *
     * @return array<Occurrence>
     */
    public function findUpcoming(
        int $limit = 20,
        int $offset = 0
    ): array {

        return $this->findMany(
            "
            SELECT *
            FROM {$this->table()}
            WHERE start_datetime >= %s
            AND status = %s
            ORDER BY start_datetime ASC
            LIMIT %d OFFSET %d
            ",
            [
                current_time('mysql'),
                OccurrenceStatus::PUBLISHED->value,
                $limit,
                $offset,
            ]
        );
    }

    /**
     * Find occurrences for an event.
     *
     * @return array<Occurrence>
     */
    public function findByEventId(
        int $eventId
    ): array {

        return $this->findMany(
            "
            SELECT *
            FROM {$this->table()}
            WHERE event_id = %d
            ORDER BY start_datetime ASC
            ",
            [
                $eventId,
            ]
        );
    }

    /**
     * Find occurrences between dates.
     *
     * @return array<Occurrence>
     */
    public function findBetween(
        string $start,
        string $end
    ): array {

        return $this->findMany(
            "
            SELECT *
            FROM {$this->table()}
            WHERE start_datetime BETWEEN %s AND %s
            ORDER BY start_datetime ASC
            ",
            [
                $start,
                $end,
            ]
        );
    }

    /**
     * Mark old occurrences as expired.
     *
     * @param string $before Date limit.
     */
    public function expireBefore(
        string $before
    ): bool {

        return $this->update(
            [
                'status' => OccurrenceStatus::EXPIRED->value,
            ],
            [
                'status' => OccurrenceStatus::PUBLISHED->value,
            ]
        );
    }
}