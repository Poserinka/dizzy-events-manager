<?php
/**
 * Plugin Loader
 *
 * Loads all required plugin classes.
 *
 * @package DizzyEventsManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dizzy_Loader {

	/**
	 * Initialize loader.
	 *
	 * @return void
	 */
	public static function init() {

		self::load_core();
		self::load_events();
		self::load_admin();

	}

	/**
	 * Load core classes.
	 *
	 * @return void
	 */
	private static function load_core() {

		self::require_files(
			array(
				'class-activator.php',
				'class-deactivator.php',
				'class-plugin.php',
			)
		);

	}

	/**
	 * Load event related classes.
	 *
	 * @return void
	 */
	private static function load_events() {

		self::require_files(
			array(
				'class-events-post-type.php',
				'class-artists-post-type.php',
				'class-event-meta-boxes.php',
			)
		);

	}

	/**
	 * Load admin classes.
	 *
	 * @return void
	 */
	private static function load_admin() {

		self::require_files(
			array(
				'class-admin-menu.php',
			)
		);

	}

	/**
	 * Require plugin files.
	 *
	 * @param array $files List of files.
	 *
	 * @return void
	 */
	private static function require_files( array $files ) {

		foreach ( $files as $file ) {

			$path = DIZZY_EVENTS_PATH . 'includes/' . $file;

			if ( is_readable( $path ) ) {
				require_once $path;
			}
		}

	}
}