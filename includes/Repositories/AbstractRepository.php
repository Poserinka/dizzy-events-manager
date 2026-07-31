<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use Dizzy\Events\Contracts\HydratesFromRow;
use Dizzy\Events\Core\Database;
use Dizzy\Events\Core\DB;

defined('ABSPATH') || exit;

/**
 * Base repository.
 *
 * Provides common write operations and model hydration.
 *
 * @package Dizzy\Events\Repositories
 */
abstract class AbstractRepository
{
    /**
     * Logical table key.
     */
    protected string $table;

    /**
     * Returns the model class handled by this repository.
     *
     * @return class-string<HydratesFromRow>
     */
    abstract protected function modelClass(): string;

    /**
     * Returns the physical database table name.
     */
    protected function table(): string
    {
        return Database::table($this->table);
    }

    /**
     * Hydrate a model from a database row.
     */
    protected function hydrate(?object $row): ?HydratesFromRow
    {
        if ($row === null) {
            return null;
        }

        $model = $this->modelClass();

        return $model::fromRow($row);
    }

    /**
     * Hydrate multiple models.
     *
     * @param array<object> $rows
     * @return array<HydratesFromRow>
     */
    protected function hydrateMany(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            $items[] = $this->hydrate($row);
        }

        return $items;
    }

    /**
     * Insert a record.
     *
     * @param array<string,mixed> $data
     * @param array<int,string> $format
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
     * @param array<string,mixed> $data
     * @param array<string,mixed> $where
     * @param array<int,string> $format
     * @param array<int,string> $whereFormat
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
     * @param array<string,mixed> $where
     * @param array<int,string> $whereFormat
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