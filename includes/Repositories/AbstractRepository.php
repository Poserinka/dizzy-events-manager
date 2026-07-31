<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use Dizzy\Events\Contracts\Hydrates;
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
     * @return class-string<Hydrates>
     */
    abstract protected function modelClass(): string;

    /**
     * Returns physical table name.
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
    ): ?Hydrates {

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
     * @return array<Hydrates>
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
     * Hydrate single model.
     */
    protected function hydrate(
        ?object $source
    ): ?Hydrates {

        if ($source === null) {
            return null;
        }

        $model = $this->modelClass();

        return $model::from($source);
    }

    /**
     * Hydrate multiple models.
     *
     * @param array<object> $sources
     *
     * @return array<Hydrates>
     */
    protected function hydrateMany(
        array $sources
    ): array {

        $items = [];

        foreach ($sources as $source) {
            $items[] = $this->hydrate($source);
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