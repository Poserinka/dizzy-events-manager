<?php
/**
 * Plugin Activator.
 *
 * Runs once when the plugin is activated.
 *
 * @package DizzyEventsManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dizzy_Activator {

	/**
	 * Activate plugin.
	 *
	 * @return void
	 */
	public static function activate() {

		// Install database and plugin defaults.
		if ( class_exists( 'Dizzy_Installer' ) ) {
			Dizzy_Installer::install();
		}

		// Store current plugin version.
		update_option(
			'dizzy_events_version',
			DIZZY_EVENTS_VERSION
		);

		// Store current database version.
		update_option(
			'dizzy_events_db_version',
			DIZZY_EVENTS_DB_VERSION
		);

		// Refresh rewrite rules.
		flush_rewrite_rules();

	}
}