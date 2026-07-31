<?php
/**
 * PSR-4 Autoloader.
 *
 * @package DizzyEventsManager
 */

declare(strict_types=1);

namespace DizzyEvents\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin autoloader.
 */
final class Autoloader {

	/**
	 * Root namespace.
	 */
	private const NAMESPACE = 'DizzyEvents\\';

	/**
	 * Plugin includes directory.
	 */
	private const BASE_DIRECTORY = DIZZY_EVENTS_PATH . 'includes/';

	/**
	 * Register autoloader.
	 *
	 * @return void
	 */
	public static function register(): void {

		spl_autoload_register(
			array(
				self::class,
				'load',
			)
		);

	}

	/**
	 * Load class.
	 *
	 * @param string $class Fully-qualified class name.
	 *
	 * @return void
	 */
	private static function load( string $class ): void {

		// Ignore external namespaces.
		if ( strncmp( $class, self::NAMESPACE, strlen( self::NAMESPACE ) ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( self::NAMESPACE ) );

		$file = self::BASE_DIRECTORY .
			str_replace(
				'\\',
				DIRECTORY_SEPARATOR,
				$relative
			) .
			'.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}

	}
}