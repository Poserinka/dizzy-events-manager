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
    private const OCCURRENCE_STATUS = 'publish';

    private const EVENT_POST_TYPE = 'event';

    private const EVENT_POST_STATUS = 'publish';

    private const MAX_EVENT_LIMIT = 100;

    private const EVENT_ID_BATCH_SIZE = 100;

    /**
     * Occurrence repository constructor.
     */
    public function __construct(
        private string $table,
        private string $postsTable
    ) {
    }

    /**
     * Find occurrences belonging to an event in their manual admin order.
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
            ORDER BY sort_order ASC, start_datetime ASC
            ",
            [$eventId]
        );

        return $this->hydrateRows($rows);
    }

    /**
     * Find current and upcoming published occurrences grouped by event ID.
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

        $grouped = array_fill_keys($eventIds, []);
        $now     = current_time('mysql');

        foreach (
            array_chunk($eventIds, self::EVENT_ID_BATCH_SIZE)
            as $eventIdBatch
        ) {
            $this->appendUpcomingOccurrenceBatch(
                $grouped,
                $eventIdBatch,
                $now
            );
        }

        return $grouped;
    }

    /**
     * Find published event IDs with current or upcoming occurrences.
     *
     * IDs are ordered by their next relevant occurrence time. Ongoing
     * occurrences are ranked at the current time instead of by an old start.
     *
     * @return array<int>
     */
    public function findUpcomingEventIds(int $limit = 20): array
    {
        $limit = min(max(1, $limit), self::MAX_EVENT_LIMIT);
        $now   = current_time('mysql');

        $eventIds = DB::getColumn(
            "
            SELECT occurrences.event_id
            FROM {$this->table} AS occurrences
            INNER JOIN {$this->postsTable} AS events
                ON events.ID = occurrences.event_id
            WHERE {$this->publishedUpcomingConditions()}
            GROUP BY occurrences.event_id
            ORDER BY
                MIN(
                    CASE
                        WHEN occurrences.start_datetime < %s THEN %s
                        ELSE occurrences.start_datetime
                    END
                ) ASC,
                MIN(occurrences.start_datetime) ASC,
                occurrences.event_id ASC
            LIMIT %d
            ",
            [
                ...$this->publishedUpcomingArguments($now),
                $now,
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
     * Check whether an event has a current or upcoming published occurrence.
     */
    public function hasUpcomingForEvent(int $eventId): bool
    {
        if ($eventId <= 0) {
            return false;
        }

        $now = current_time('mysql');

        return DB::exists(
            "
            SELECT 1
            FROM {$this->table} AS occurrences
            INNER JOIN {$this->postsTable} AS events
                ON events.ID = occurrences.event_id
            WHERE occurrences.event_id = %d
                AND {$this->publishedUpcomingConditions()}
            LIMIT 1
            ",
            [
                $eventId,
                ...$this->publishedUpcomingArguments($now),
            ]
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
    public function replaceForEvent(int $eventId, array $occurrences): void
    {
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
                ['event_id' => $eventId],
                ['%d']
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
     * Delete all occurrences belonging to an event.
     */
    public function deleteForEvent(int $eventId): void
    {
        if ($eventId <= 0) {
            return;
        }

        $deleted = DB::instance()->delete(
            $this->table,
            ['event_id' => $eventId],
            ['%d']
        );

        if ($deleted === false) {
            throw new RuntimeException(
                $this->getDatabaseError(
                    'Could not delete event occurrences.'
                )
            );
        }
    }

    /**
     * Append one batch of upcoming occurrence results to grouped data.
     *
     * @param array<int, array<Occurrence>> $grouped Grouped occurrence data.
     * @param array<int>                    $eventIds Event IDs in this batch.
     */
    private function appendUpcomingOccurrenceBatch(
        array &$grouped,
        array $eventIds,
        string $now
    ): void {
        $placeholders = implode(
            ', ',
            array_fill(0, count($eventIds), '%d')
        );

        $rows = DB::getResults(
            "
            SELECT occurrences.*
            FROM {$this->table} AS occurrences
            INNER JOIN {$this->postsTable} AS events
                ON events.ID = occurrences.event_id
            WHERE occurrences.event_id IN ({$placeholders})
                AND {$this->publishedUpcomingConditions()}
            ORDER BY
                occurrences.event_id ASC,
                occurrences.start_datetime ASC,
                occurrences.sort_order ASC
            ",
            [
                ...$eventIds,
                ...$this->publishedUpcomingArguments($now),
            ]
        );

        foreach ($rows as $row) {
            $occurrence = $this->hydrateRow($row);

            if ($occurrence === null) {
                continue;
            }

            $eventId = (int) ($row->event_id ?? 0);

            if (isset($grouped[$eventId])) {
                $grouped[$eventId][] = $occurrence;
            }
        }
    }

    /**
     * Get the common SQL conditions for public upcoming occurrences.
     */
    private function publishedUpcomingConditions(): string
    {
        return "
            occurrences.status = %s
            AND events.post_type = %s
            AND events.post_status = %s
            AND (
                occurrences.end_datetime >= %s
                OR (
                    occurrences.end_datetime IS NULL
                    AND occurrences.start_datetime >= %s
                )
            )
        ";
    }

    /**
     * Get arguments for the common public upcoming occurrence conditions.
     *
     * @return array<int, string>
     */
    private function publishedUpcomingArguments(string $now): array
    {
        return [
            self::OCCURRENCE_STATUS,
            self::EVENT_POST_TYPE,
            self::EVENT_POST_STATUS,
            $now,
            $now,
        ];
    }

    /**
     * Hydrate occurrence rows while isolating malformed records.
     *
     * @param array<object> $rows Database rows.
     *
     * @return array<Occurrence>
     */
    private function hydrateRows(array $rows): array
    {
        $occurrences = [];

        foreach ($rows as $row) {
            $occurrence = $this->hydrateRow($row);

            if ($occurrence !== null) {
                $occurrences[] = $occurrence;
            }
        }

        return $occurrences;
    }

    /**
     * Hydrate a single occurrence row safely.
     */
    private function hydrateRow(object $row): ?Occurrence
    {
        try {
            return Occurrence::hydrateFromRow($row);
        } catch (Throwable $exception) {
            error_log(
                sprintf(
                    'Dizzy Events skipped malformed occurrence %d: %s',
                    (int) ($row->id ?? 0),
                    $exception->getMessage()
                )
            );

            return null;
        }
    }

    /**
     * Get a database error message.
     */
    private function getDatabaseError(string $fallback): string
    {
        $error = DB::lastError();

        return $error === '' ? $fallback : $fallback . ' ' . $error;
    }
}
