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
 * Provides common database operations and model hydration.
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
     * Returns the physical table name.
     */
    protected function table(): string
    {
        return Database::table($this->table);
    }

    /**
     * Find one model.
     *
     * @param string $query SQL query.
     * @param array<int,mixed> $args Query arguments.
     */
    protected function findOne(
        string $query,
        array $args = []
    ): ?HydratesFromRow {

        return $this->hydrate(
            DB::getRow(
                $query,
                $args
            )
        );
    }

    /**
     * Find multiple models.
     *
     * @param string $query SQL query.
     * @param array<int,mixed> $args Query arguments.
     *
     * @return array<HydratesFromRow>
     */
    protected function findMany(
        string $query,
        array $args = []
    ): array {

        return $this->hydrateMany(
            DB::getResults(
                $query,
                $args
            )
        );
    }

    /**
     * Hydrate a single model.
     */
    protected function hydrate(
        ?object $row
    ): ?HydratesFromRow {

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
     *
     * @return array<HydratesFromRow>
     */
    protected function hydrateMany(
        array $rows
    ): array {

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