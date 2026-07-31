<?php
/**
 * Core Plugin Class
 *
 * @package DizzyEventsManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dizzy_Plugin {

	/**
	 * Boot plugin.
	 *
	 * @return void
	 */
	public static function run() {

		self::load_textdomain();
		self::boot_modules();

	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	private static function load_textdomain() {

		load_plugin_textdomain(
			DIZZY_EVENTS_TEXTDOMAIN,
			false,
			dirname( DIZZY_EVENTS_BASENAME ) . '/languages'
		);

	}

	/**
	 * Initialize all plugin modules.
	 *
	 * @return void
	 */
	private static function boot_modules() {

		$modules = array(

			'Dizzy_Assets',
			'Dizzy_Admin',
			'Dizzy_Events',
			'Dizzy_Reservations',
			'Dizzy_Checkin',
			'Dizzy_Reports',
			'Dizzy_Posters',
			'Dizzy_Social',

		);

		foreach ( $modules as $module ) {

			if (
				class_exists( $module ) &&
				method_exists( $module, 'init' )
			) {

				$module::init();

			}

		}

	}

}