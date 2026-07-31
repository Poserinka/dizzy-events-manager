<?php
/**
 * Core Plugin Bootstrap.
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
	 * Plugin modules.
	 *
	 * Every module must expose a static init() method.
	 *
	 * @var array<class-string>
	 */
	private const MODULES = array(

		Assets::class,

		// Admin
		// \DizzyEvents\Admin\Admin::class,

		// Events
		// \DizzyEvents\Events\EventManager::class,

		// Artists
		// \DizzyEvents\Artists\ArtistManager::class,

		// Reservations
		// \DizzyEvents\Reservations\ReservationManager::class,

		// Check-in
		// \DizzyEvents\Checkin\CheckinManager::class,

		// Reports
		// \DizzyEvents\Reports\ReportManager::class,

		// Posters
		// \DizzyEvents\Posters\PosterGenerator::class,

		// Social
		// \DizzyEvents\Social\SocialExporter::class,

	);

	/**
	 * Bootstrap plugin.
	 *
	 * @return void
	 */
	public static function init(): void {

		self::load_textdomain();

		self::boot_modules();

	}

	/**
	 * Load translations.
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
	 * Initialize all modules.
	 *
	 * @return void
	 */
	private static function boot_modules(): void {

		foreach ( Modules::all() as $module ) {

	if (
		class_exists( $module ) &&
		method_exists( $module, 'init' )
	) {
		$module::init();
	}
}

	}
}