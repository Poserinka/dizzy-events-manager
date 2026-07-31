<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use Dizzy\Events\Core\Database;
use Dizzy\Events\Core\DB;

defined('ABSPATH') || exit;

/**
 * Base repository.
 *
 * Provides common write operations for plugin tables.
 *
 * @package Dizzy\Events\Repositories
 */
abstract class AbstractRepository
{
    /**
     * Logical table key.
     *
     * @var string
     */
    protected string $table;

    /**
     * Returns the physical database table name.
     */
    protected function table(): string
    {
        return Database::table($this->table);
    }

    /**
     * Insert a record.
     *
     * @param array<string,mixed> $data   Data.
     * @param array<int,string>   $format Formats.
     */
    protected function insert(
        array $data,
        array $format = []
    ): int {

        DB::insert(
            $this->table(),
            $data,
            $format
        );

        return DB::insertId();
    }

    /**
     * Update records.
     *
     * @param array<string,mixed> $data         Data.
     * @param array<string,mixed> $where        Where.
     * @param array<int,string>   $format       Formats.
     * @param array<int,string>   $whereFormat  Where formats.
     */
    protected function update(
        array $data,
        array $where,
        array $format = [],
        array $whereFormat = []
    ): bool {

        return DB::update(
            $this->table(),
            $data,
            $where,
            $format,
            $whereFormat
        );
    }

    /**
     * Delete records.
     *
     * @param array<string,mixed> $where Where clause.
     * @param array<int,string>   $whereFormat Where formats.
     */
    protected function delete(
        array $where,
        array $whereFormat = []
    ): bool {

        return DB::delete(
            $this->table(),
            $where,
            $whereFormat
        );
    }
}