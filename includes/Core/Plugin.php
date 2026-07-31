<?php
/**
 * Main Plugin Bootstrap.
 *
 * @package DizzyEventsManager
 */

declare(strict_types=1);

namespace DizzyEvents\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin bootstrap class.
 */
final class Plugin {

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public static function init(): void {

		self::load_textdomain();

		self::boot_modules();

	}

	/**
	 * Load plugin translations.
	 *
	 * @return void
	 */
	private static function load_textdomain(): void {

		load_plugin_textdomain(
			DIZZY_EVENTS_TEXTDOMAIN,
			false,
			dirname( DIZZY_EVENTS_BASENAME ) . '/languages'
		);

	}

	/**
	 * Initialize all registered modules.
	 *
	 * @return void
	 */
	private static function boot_modules(): void {

foreach ( Modules::all() as $module ) {

	if (
		is_string( $module ) &&
		class_exists( $module ) &&
		is_callable( array( $module, 'init' ) )
	) {
		$module::init();
	}

}
}