<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use Dizzy\Events\Enums\OccurrenceStatus;
use Dizzy\Events\Models\Occurrence;

defined('ABSPATH') || exit;

/**
 * Repository for event occurrences.
 *
 * @package Dizzy\Events\Repositories
 */
final class OccurrenceRepository extends AbstractRepository
{
    protected string $table = 'dizzy_event_occurrences';


    /**
     * Model handled by repository.
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
     * Find occurrences by event.
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
     * Create occurrence.
     *
     * @param array<string,mixed> $data
     */
    public function create(
        array $data
    ): int {

        return $this->insert(
            $data
        );
    }


    /**
     * Find upcoming occurrences.
     *
     * @return array<Occurrence>
     */
    public function findUpcoming(
        int $limit = 20
    ): array {

        return $this->findMany(
            "
            SELECT *
            FROM {$this->table()}
            WHERE start_datetime >= %s
            AND status = %s
            ORDER BY start_datetime ASC
            LIMIT %d
            ",
            [
                current_time('mysql'),

                OccurrenceStatus::PUBLISHED->value,

                $limit,
            ]
        );
    }
}