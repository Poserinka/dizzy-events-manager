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
            ORDER BY sort_order ASC, start_datetime ASC
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

        $database = DB::instance();

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

            $timestamp = current_time('mysql', true);

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
