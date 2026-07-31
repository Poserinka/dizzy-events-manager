<?php

declare(strict_types=1);

namespace Dizzy\Events\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Central logger for Dizzy Events Manager.
 *
 * @package Dizzy\Events\Core
 */
final class Logger {

	/**
	 * Prevent instantiation.
	 */
	private function __construct() {}

	/**
	 * Log debug message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context.
	 *
	 * @return void
	 */
	public static function debug( string $message, array $context = array() ): void {
		self::log( Config::LOG_DEBUG, $message, $context );
	}

	/**
	 * Log info message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context.
	 *
	 * @return void
	 */
	public static function info( string $message, array $context = array() ): void {
		self::log( Config::LOG_INFO, $message, $context );
	}

	/**
	 * Log warning message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context.
	 *
	 * @return void
	 */
	public static function warning( string $message, array $context = array() ): void {
		self::log( Config::LOG_WARNING, $message, $context );
	}

	/**
	 * Log error message.
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context.
	 *
	 * @return void
	 */
	public static function error( string $message, array $context = array() ): void {
		self::log( Config::LOG_ERROR, $message, $context );
	}

	/**
	 * Write a log entry.
	 *
	 * @param string $level   Log level.
	 * @param string $message Log message.
	 * @param array  $context Additional context.
	 *
	 * @return void
	 */
	public static function log(
		string $level,
		string $message,
		array $context = array()
	): void {

		/**
		 * Allow plugins/themes to intercept log entries.
		 */
		do_action(
			'dizzy_events_log',
			$level,
			$message,
			$context
		);

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {

			$line = sprintf(
				'[Dizzy Events] [%s] %s',
				strtoupper( $level ),
				$message
			);

			if ( ! empty( $context ) ) {
				$line .= ' ' . wp_json_encode( $context );
			}

			error_log( $line ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}