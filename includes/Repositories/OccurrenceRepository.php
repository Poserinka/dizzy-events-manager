<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use Dizzy\Events\Core\DB;
use Dizzy\Events\Models\Occurrence;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

defined('ABSPATH') || exit;

/**
 * Handles event occurrence persistence.
 *
 * @package Dizzy\Events\Repositories
 */
final class OccurrenceRepository
{
    /**
     * Occurrence repository constructor.
     */
    public function __construct(
        private string $table
    ) {
    }

    /**
     * Find occurrences belonging to an event.
     *
     * @return array<Occurrence>
     */
    public function findByEventId(int $eventId): array
    {
        if ($eventId <= 0) {
            return [];
        }

        $rows = DB::getResults(
            "
            SELECT *
            FROM {$this->table}
            WHERE event_id = %d
            ORDER BY start_datetime ASC, sort_order ASC
            ",
            [
                $eventId,
            ]
        );

        return array_map(
            static function (object $row): Occurrence {
                return Occurrence::hydrateFromRow($row);
            },
            $rows
        );
    }

    /**
     * Find upcoming published occurrences grouped by event ID.
     *
     * @param array<int> $eventIds Event IDs.
     *
     * @return array<int, array<Occurrence>>
     */
    public function findUpcomingByEventIds(array $eventIds): array
    {
        $eventIds = array_values(
            array_unique(
                array_filter(
                    array_map('absint', $eventIds)
                )
            )
        );

        if ($eventIds === []) {
            return [];
        }

        $placeholders = implode(
            ', ',
            array_fill(0, count($eventIds), '%d')
        );
        $now = current_time('mysql', true);

        $rows = DB::getResults(
            "
            SELECT *
            FROM {$this->table}
            WHERE event_id IN ({$placeholders})
                AND status = %s
                AND start_datetime >= %s
            ORDER BY event_id ASC, start_datetime ASC, sort_order ASC
            ",
            [
                ...$eventIds,
                'publish',
                $now,
            ]
        );

        $grouped = array_fill_keys($eventIds, []);

        foreach ($rows as $row) {
            $occurrence = Occurrence::hydrateFromRow($row);
            $eventId    = (int) $row->event_id;

            if (! isset($grouped[$eventId])) {
                continue;
            }

            $grouped[$eventId][] = $occurrence;
        }

        return $grouped;
    }

    /**
     * Find event IDs with upcoming published occurrences.
     *
     * IDs are ordered by their next occurrence date.
     *
     * @return array<int>
     */
    public function findUpcomingEventIds(int $limit = 20): array
    {
        $limit = max(1, $limit);
        $now   = current_time('mysql', true);

        $eventIds = DB::getColumn(
            "
            SELECT event_id
            FROM {$this->table}
            WHERE status = %s
                AND start_datetime >= %s
            GROUP BY event_id
            ORDER BY MIN(start_datetime) ASC
            LIMIT %d
            ",
            [
                'publish',
                $now,
                $limit,
            ]
        );

        return array_values(
            array_filter(
                array_map('absint', $eventIds)
            )
        );
    }

    /**
     * Replace all occurrences belonging to an event.
     *
     * The occurrence data must already be validated and normalized.
     *
     * @param array<int, array{
     *     start_datetime: string,
     *     end_datetime: string|null,
     *     all_day: int,
     *     timezone: string,
     *     sort_order: int,
     *     status: string
     * }> $occurrences Normalized occurrence records.
     */
    public function replaceForEvent(
        int $eventId,
        array $occurrences
    ): void {
        if ($eventId <= 0) {
            throw new InvalidArgumentException(
                'A valid event ID is required to replace occurrences.'
            );
        }

        $database  = DB::instance();
        $timestamp = current_time('mysql', true);

        if ($database->query('START TRANSACTION') === false) {
            throw new RuntimeException(
                'Could not start occurrence database transaction.'
            );
        }

        try {
            $deleted = $database->delete(
                $this->table,
                [
                    'event_id' => $eventId,
                ],
                [
                    '%d',
                ]
            );

            if ($deleted === false) {
                throw new RuntimeException(
                    $this->getDatabaseError(
                        'Could not delete existing event occurrences.'
                    )
                );
            }

            foreach ($occurrences as $occurrence) {
                $inserted = $database->insert(
                    $this->table,
                    [
                        'event_id'       => $eventId,
                        'start_datetime' => $occurrence['start_datetime'],
                        'end_datetime'   => $occurrence['end_datetime'],
                        'all_day'        => $occurrence['all_day'],
                        'timezone'       => $occurrence['timezone'],
                        'sort_order'     => $occurrence['sort_order'],
                        'status'         => $occurrence['status'],
                        'created_at'     => $timestamp,
                        'updated_at'     => $timestamp,
                    ],
                    [
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                    ]
                );

                if ($inserted === false) {
                    throw new RuntimeException(
                        $this->getDatabaseError(
                            'Could not insert event occurrence.'
                        )
                    );
                }
            }

            if ($database->query('COMMIT') === false) {
                throw new RuntimeException(
                    $this->getDatabaseError(
                        'Could not commit occurrence database transaction.'
                    )
                );
            }
        } catch (Throwable $exception) {
            $database->query('ROLLBACK');

            throw $exception;
        }
    }

    /**
     * Get a database error message.
     */
    private function getDatabaseError(string $fallback): string
    {
        $error = DB::lastError();

        if ($error === '') {
            return $fallback;
        }

        return $fallback . ' ' . $error;
    }
}
