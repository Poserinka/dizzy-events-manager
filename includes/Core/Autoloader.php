<?php
/**
 * PSR-4 Autoloader.
 *
 * @package DizzyEventsManager
 */

declare(strict_types=1);

namespace DizzyEvents\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Autoloader {

	/**
	 * Base namespace.
	 */
	private const BASE_NAMESPACE = 'DizzyEvents\\';

	/**
	 * Register autoloader.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'autoload' ) );
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class Fully qualified class name.
	 *
	 * @return void
	 */
	private static function autoload( string $class ): void {

		// Ignore other namespaces.
		if ( strpos( $class, self::BASE_NAMESPACE ) !== 0 ) {
			return;
		}

		// Remove namespace prefix.
		$relative_class = substr( $class, strlen( self::BASE_NAMESPACE ) );

		// Namespace -> directory.
		$file = DIZZY_EVENTS_PATH
			. 'includes/'
			. str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class )
			. '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}