<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use Dizzy\Events\Core\Database;
use Dizzy\Events\Core\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Base repository.
 *
 * Provides common CRUD methods for plugin tables.
 *
 * @package Dizzy\Events\Repositories
 */
abstract class AbstractRepository {

	/**
	 * Logical table key.
	 *
	 * @var string
	 */
	protected string $table;

	/**
	 * Returns physical table name.
	 */
	protected function table(): string {
		return Database::table( $this->table );
	}

	/**
	 * Find by primary key.
	 *
	 * @param int $id Record ID.
	 *
	 * @return object|null
	 */
	public function find( int $id ): ?object {

		$sql = sprintf(
			'SELECT * FROM %s WHERE id = %%d LIMIT 1',
			$this->table()
		);

		return DB::getRow(
			$sql,
			array( $id )
		);
	}

	/**
	 * Insert row.
	 *
	 * @param array $data Data.
	 * @param array $format Formats.
	 *
	 * @return int Insert ID.
	 */
	protected function insert(
		array $data,
		array $format = array()
	): int {

		DB::insert(
			$this->table(),
			$data,
			$format
		);

		return DB::insertId();
	}

	/**
	 * Update rows.
	 *
	 * @param array $data Data.
	 * @param array $where Where clause.
	 * @param array $format Formats.
	 * @param array $where_format Where formats.
	 *
	 * @return bool
	 */
	protected function update(
		array $data,
		array $where,
		array $format = array(),
		array $where_format = array()
	): bool {

		return DB::update(
			$this->table(),
			$data,
			$where,
			$format,
			$where_format
		);
	}

	/**
	 * Delete rows.
	 *
	 * @param array $where Where clause.
	 * @param array $where_format Where formats.
	 *
	 * @return bool
	 */
	protected function delete(
		array $where,
		array $where_format = array()
	): bool {

		return DB::delete(
			$this->table(),
			$where,
			$where_format
		);
	}

	/**
	 * Returns all rows.
	 *
	 * @return array
	 */
	public function all(): array {

		$sql = sprintf(
			'SELECT * FROM %s',
			$this->table()
		);

		return DB::getResults( $sql );
	}

	/**
	 * Returns total row count.
	 *
	 * @return int
	 */
	public function count(): int {

		$sql = sprintf(
			'SELECT COUNT(*) FROM %s',
			$this->table()
		);

		return (int) DB::getVar( $sql );
	}

}