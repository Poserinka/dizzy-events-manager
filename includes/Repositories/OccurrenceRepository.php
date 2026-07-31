<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use DateTimeInterface;
use Dizzy\Events\Core\Config;
use Dizzy\Events\Core\DB;

defined('ABSPATH') || exit;

/**
 * Occurrence repository.
 *
 * Handles recurring event dates.
 */
final class OccurrenceRepository extends AbstractRepository
{
    protected string $table = Config::TABLE_OCCURRENCES;

    /**
     * Returns all upcoming occurrences.
     *
     * @return array<object>
     */
    public function findUpcoming(
        int $limit = 50
    ): array {

        $sql = sprintf(
            "
            SELECT *
            FROM %s
            WHERE start_datetime >= UTC_TIMESTAMP()
            ORDER BY start_datetime ASC
            LIMIT %%d
            ",
            $this->table()
        );

        return DB::getResults(
            $sql,
            [$limit]
        );
    }

    /**
     * Today's occurrences.
     *
     * @return array<object>
     */
    public function findToday(): array
    {
        $sql = sprintf(
            "
            SELECT *
            FROM %s
            WHERE DATE(start_datetime)=UTC_DATE()
            ORDER BY start_datetime ASC
            ",
            $this->table()
        );

        return DB::getResults($sql);
    }

    /**
     * Find occurrences between dates.
     *
     * @return array<object>
     */
    public function findBetween(
        DateTimeInterface $from,
        DateTimeInterface $to
    ): array {

        $sql = sprintf(
            "
            SELECT *
            FROM %s
            WHERE start_datetime
            BETWEEN %%s AND %%s
            ORDER BY start_datetime
            ",
            $this->table()
        );

        return DB::getResults(
            $sql,
            [
                $from->format('Y-m-d H:i:s'),
                $to->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Delete expired occurrences.
     */
    public function deleteExpired(): bool
    {
        $sql = sprintf(
            "
            DELETE
            FROM %s
            WHERE end_datetime < UTC_TIMESTAMP()
            ",
            $this->table()
        );

        return false !== DB::query($sql);
    }

    /**
     * Count upcoming.
     */
    public function countUpcoming(): int
    {
        $sql = sprintf(
            "
            SELECT COUNT(*)
            FROM %s
            WHERE start_datetime>=UTC_TIMESTAMP()
            ",
            $this->table()
        );

        return (int) DB::getVar($sql);
    }
}