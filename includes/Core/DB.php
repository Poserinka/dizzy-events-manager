<?php

declare(strict_types=1);

namespace Dizzy\Events\Core;

use wpdb;

defined( 'ABSPATH' ) || exit;

/**
 * Lightweight database wrapper.
 *
 * All direct interaction with wpdb should happen through this class.
 *
 * @package Dizzy\Events\Core
 */
final class DB {

	/**
	 * Prevent instantiation.
	 */
	private function __construct() {}

	/**
	 * Returns the wpdb instance.
	 */
	public static function instance(): wpdb {

		global $wpdb;

		return $wpdb;
	}

	/**
	 * Insert a row.
	 */
	public static function insert(
		string $table,
		array $data,
		array $format = array()
	): bool {

		return false !== self::instance()->insert(
			$table,
			$data,
			$format
		);
	}

	/**
	 * Update rows.
	 */
	public static function update(
		string $table,
		array $data,
		array $where,
		array $format = array(),
		array $where_format = array()
	): bool {

		return false !== self::instance()->update(
			$table,
			$data,
			$where,
			$format,
			$where_format
		);
	}

	/**
	 * Delete rows.
	 */
	public static function delete(
		string $table,
		array $where,
		array $where_format = array()
	): bool {

		return false !== self::instance()->delete(
			$table,
			$where,
			$where_format
		);
	}

	/**
	 * Returns one row.
	 */
	public static function getRow(
		string $query,
		array $args = array()
	): ?object {

		if ( ! empty( $args ) ) {
			$query = self::instance()->prepare(
				$query,
				$args
			);
		}

		return self::instance()->get_row( $query );
	}

	/**
	 * Returns multiple rows.
	 */
	public static function getResults(
		string $query,
		array $args = array()
	): array {

		if ( ! empty( $args ) ) {
			$query = self::instance()->prepare(
				$query,
				$args
			);
		}

		return self::instance()->get_results( $query );
	}

	/**
	 * Returns a single value.
	 */
	public static function getVar(
		string $query,
		array $args = array()
	): mixed {

		if ( ! empty( $args ) ) {
			$query = self::instance()->prepare(
				$query,
				$args
			);
		}

		return self::instance()->get_var( $query );
	}

	/**
	 * Execute raw SQL.
	 */
	public static function query(
		string $query,
		array $args = array()
	): int|false {

		if ( ! empty( $args ) ) {
			$query = self::instance()->prepare(
				$query,
				$args
			);
		}

		return self::instance()->query( $query );
	}

	/**
	 * Last insert id.
	 */
	public static function insertId(): int {

		return (int) self::instance()->insert_id;
	}

	/**
	 * Rows affected.
	 */
	public static function rowsAffected(): int {

		return (int) self::instance()->rows_affected;
	}

	/**
	 * Last database error.
	 */
	public static function lastError(): string {

		return self::instance()->last_error;
	}
}